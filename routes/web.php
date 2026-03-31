<?php

use App\Http\Middleware\RequireFilamentLogin;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(RequireFilamentLogin::class)->get('/', function () {
    $productos = Product::query()
        ->select(['id', 'name', 'price', 'stock', 'image'])
        ->orderBy('name')
        ->take(8)
        ->get();

    $catalogo = Product::query()
        ->select(['id', 'name', 'price', 'stock', 'image'])
        ->orderBy('name')
        ->get();
    $ventas = Sale::query()
        ->orderByDesc('created_at')
        ->orderByDesc('id')
        ->with('items.product')
        ->take(25)
        ->get();

    $productAgg = Product::query()
        ->selectRaw('COUNT(*) as total_productos, COALESCE(SUM(stock), 0) as stock_total')
        ->first();

    $salesAgg = Sale::query()
        ->selectRaw('COUNT(*) as total_facturas, COALESCE(SUM(total), 0) as ingresos_totales')
        ->first();

    $kpis = [
        'total_productos' => (int) ($productAgg->total_productos ?? 0),
        'stock_total' => (int) ($productAgg->stock_total ?? 0),
        'total_facturas' => (int) ($salesAgg->total_facturas ?? 0),
        'ingresos_totales' => (int) ($salesAgg->ingresos_totales ?? 0),
    ];

    $ultimosVendidos = SaleItem::query()
        ->latest()
        ->with(['product', 'sale'])
        ->take(20)
        ->get();

    $ultimasFacturas = Sale::query()
        ->orderByDesc('created_at')
        ->orderByDesc('id')
        ->take(6)
        ->get();

    $bajoStock = Product::query()
        ->where('stock', '<=', 1)
        ->orderBy('stock')
        ->orderBy('name')
        ->take(10)
        ->get();

    $movimientosInventario = InventoryMovement::query()
        ->latest()
        ->with('product')
        ->take(40)
        ->get();

    $topVendidos = SaleItem::query()
        ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(line_total) as total_amount')
        ->with('product')
        ->groupBy('product_id')
        ->orderByDesc('total_qty')
        ->take(5)
        ->get();

    $ventasDiarias = Sale::query()
        ->selectRaw('DATE(created_at) as dia, SUM(total) as total')
        ->groupBy('dia')
        ->orderByDesc('dia')
        ->take(60)
        ->get();

    $ventasDiariasJson = $ventasDiarias
        ->map(fn ($row) => [
            'date' => (string) $row->dia,
            'total' => (int) $row->total,
        ])
        ->values();

    return view('dashboard', [
        'productos' => $productos,
        'catalogo' => $catalogo,
        'ventas' => $ventas,
        'kpis' => $kpis,
        'ultimosVendidos' => $ultimosVendidos,
        'ultimasFacturas' => $ultimasFacturas,
        'bajoStock' => $bajoStock,
        'movimientosInventario' => $movimientosInventario,
        'topVendidos' => $topVendidos,
        'ventasDiarias' => $ventasDiarias,
        'ventasDiariasJson' => $ventasDiariasJson,
    ]);
});

Route::middleware(RequireFilamentLogin::class)->post('/sales', function (Request $request) {
    $data = $request->validate([
        'payment_method' => ['required', 'in:efectivo,nequi'],
        'items' => ['required', 'array', 'min:1'],
        'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
        'items.*.quantity' => ['required', 'integer', 'min:1'],
    ]);

    try {
        DB::transaction(function () use ($data) {
            $items = collect($data['items'])
                ->filter(fn ($row) => !empty($row['product_id']) && !empty($row['quantity']))
                ->values();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Debes agregar al menos un producto.',
                ]);
            }

            $total = 0;
            $sale = Sale::create([
                'payment_method' => $data['payment_method'],
                'total' => 0,
            ]);

            foreach ($items as $row) {
                $qty = (int) $row['quantity'];
                $product = Product::query()->findOrFail((int) $row['product_id']);

                if ($product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'stock' => "No hay stock suficiente para: {$product->name}.",
                    ]);
                }

                $unitPrice = (int) $product->price;
                $lineTotal = $unitPrice * $qty;
                $total += $lineTotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                $product->decrement('stock', $qty);
            }

            $sale->update(['total' => $total]);
        });
    } catch (ValidationException $e) {
        return redirect('/#ventas')->withErrors($e->errors())->withInput();
    }

    return redirect('/#ventas')->with('sale_success', 'Venta creada satisfactoriamente.');
});

Route::middleware(RequireFilamentLogin::class)->post('/inventory/add-stock', function (Request $request) {
    $data = $request->validate([
        'product_id' => ['required', 'integer', 'exists:products,id'],
        'quantity' => ['required', 'integer', 'min:1'],
    ]);

    $product = Product::query()->findOrFail((int) $data['product_id']);
    $qty = (int) $data['quantity'];
    $product->increment('stock', $qty);

    $movement = InventoryMovement::create([
        'product_id' => $product->id,
        'quantity' => $qty,
    ]);

    if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
        return response()->json([
            'product_id' => $product->id,
            'stock' => (int) $product->stock,
            'movement' => [
                'id' => $movement->id,
                'quantity' => $qty,
                'created_at' => $movement->created_at?->format('Y-m-d H:i:s'),
                'product' => [
                    'name' => $product->name,
                    'stock' => (int) $product->stock,
                    'image_url' => $product->image ? asset('img/' . $product->image) : '',
                ],
            ],
        ]);
    }

    return redirect('/#inventario');
});

Route::middleware(RequireFilamentLogin::class)->get('/cierre-data', function (Request $request) {
    $date = $request->query('date');

    if (!$date) {
        $date = now()->toDateString();
    }

    try {
        $parsed = \Carbon\Carbon::parse($date)->startOfDay();
        $dateString = $parsed->toDateString();
    } catch (\Exception $e) {
        $dateString = now()->toDateString();
    }

    $salesQuery = Sale::query()->whereDate('created_at', $dateString);

    $dineroVendido = (int) $salesQuery->sum('total');

    $itemsAgg = SaleItem::query()
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->whereDate('sales.created_at', $dateString)
        ->select([
            'sale_items.product_id',
            DB::raw('SUM(sale_items.quantity) as total_qty'),
            DB::raw('SUM(sale_items.line_total) as total_amount'),
        ])
        ->groupBy('sale_items.product_id')
        ->get();

    $totalProductosVendidos = (int) $itemsAgg->sum('total_qty');

    $products = Product::query()
        ->whereIn('id', $itemsAgg->pluck('product_id')->unique())
        ->get()
        ->keyBy('id');

    $detalle = $itemsAgg->map(function ($row) use ($products) {
        $product = $products->get($row->product_id);

        return [
            'product_id' => (int) $row->product_id,
            'name' => $product?->name ?? 'Producto',
            'image_url' => ($product && $product->image) ? asset('img/' . $product->image) : '',
            'quantity' => (int) $row->total_qty,
            'amount' => (int) $row->total_amount,
        ];

    });

    return response()->json([
        'date' => $dateString,
        'total_money' => $dineroVendido,
        'total_products' => $totalProductosVendidos,
        'items' => $detalle->values(),
    ]);
});
