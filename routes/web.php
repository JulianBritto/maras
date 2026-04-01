<?php

use App\Http\Middleware\RequireFilamentLogin;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesWeekdayGoal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
    $ventasDateParam = request('ventas_date');
    $ventasDate = null;

    if ($ventasDateParam) {
        try {
            $parsed = Carbon::parse($ventasDateParam)->startOfDay();
            $ventasDate = $parsed->toDateString();
        } catch (\Exception $e) {
            $ventasDate = null;
        }
    }

    $productos = Product::query()
        ->select(['id', 'name', 'price', 'stock', 'image'])
        ->orderBy('name')
        ->take(8)
        ->get();

    $catalogo = Product::query()
        ->select(['id', 'name', 'price', 'stock', 'image'])
        ->where('stock', '>', 0)
        ->orderBy('name')
        ->get();

    $ventasQuery = Sale::query()
        ->orderByDesc('created_at')
        ->orderByDesc('id')
        ->with('items.product');

    if ($ventasDate) {
        $ventasQuery->whereDate('created_at', $ventasDate);
    }

    $ventas = $ventasQuery
        ->take(100)
        ->get();

    $productAgg = Cache::remember('dashboard.productAgg', 30, function () {
        return Product::query()
            ->selectRaw('COUNT(*) as total_productos, COALESCE(SUM(stock), 0) as stock_total')
            ->first();
    });

    $salesAgg = Cache::remember('dashboard.salesAgg', 30, function () {
        return Sale::query()
            ->selectRaw('COUNT(*) as total_facturas, COALESCE(SUM(total), 0) as ingresos_totales')
            ->first();
    });

    $ventasPorMetodo = Cache::remember('dashboard.ventasPorMetodo', 30, function () {
        return Sale::query()
            ->selectRaw('payment_method, COUNT(*) as total_facturas, COALESCE(SUM(total), 0) as ingresos_totales')
            ->groupBy('payment_method')
            ->get();
    });

    $weekdayGoals = SalesWeekdayGoal::query()->get()->keyBy('weekday');

    $kpis = [
        'total_productos' => (int) ($productAgg->total_productos ?? 0),
        'stock_total' => (int) ($productAgg->stock_total ?? 0),
        'total_facturas' => (int) ($salesAgg->total_facturas ?? 0),
        'ingresos_totales' => (int) ($salesAgg->ingresos_totales ?? 0),
    ];

    $ventasGoalDia = (int) env('SALES_DAILY_GOAL', 500000);

    $ventasDiaAgg = null;

    if ($ventasDate) {
        $ventasDiaRows = Sale::query()
            ->selectRaw('payment_method, COUNT(*) as total_facturas, COALESCE(SUM(total), 0) as ingresos_totales')
            ->whereDate('created_at', $ventasDate)
            ->groupBy('payment_method')
            ->get();

        $totalDia = (int) $ventasDiaRows->sum('ingresos_totales');
        $totalDiaFacturas = (int) $ventasDiaRows->sum('total_facturas');

        $rowNequi = $ventasDiaRows->firstWhere('payment_method', 'nequi');
        $rowEfectivo = $ventasDiaRows->firstWhere('payment_method', 'efectivo');

        $ventasDiaAgg = [
            'date' => $ventasDate,
            'total_facturas' => $totalDiaFacturas,
            'total' => $totalDia,
            'nequi' => (int) ($rowNequi->ingresos_totales ?? 0),
            'efectivo' => (int) ($rowEfectivo->ingresos_totales ?? 0),
        ];
    }

    $dateForGoal = $ventasDate ? Carbon::parse($ventasDate) : now();
    $weekdayIso = (int) $dateForGoal->dayOfWeekIso; // 1 = lunes ... 7 = domingo
    $weekdayGoalRow = $weekdayGoals->get($weekdayIso);
    $ventasGoalDia = (int) ($weekdayGoalRow->amount ?? env('SALES_DAILY_GOAL', 0));

    $ultimosVendidos = SaleItem::query()
        ->latest()
        ->with(['product', 'sale'])
        ->take(20)
        ->get();

    $ultimasFacturas = Sale::query()
        ->latest()
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

    $topVendidos = Cache::remember('dashboard.topVendidos', 30, function () {
        return SaleItem::query()
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(line_total) as total_amount')
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(20)
            ->get();
    });

    $ventasDiarias = Cache::remember('dashboard.ventasDiarias', 30, function () {
        return Sale::query()
            ->selectRaw('DATE(created_at) as dia, SUM(total) as total')
            ->groupBy('dia')
            ->orderByDesc('dia')
            ->take(60)
            ->get();
    });

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
        'ventasDate' => $ventasDate,
        'ventasDiaAgg' => $ventasDiaAgg,
        'ventasGoalDia' => $ventasGoalDia,
        'weekdayGoals' => $weekdayGoals,
        'ventasPorMetodo' => $ventasPorMetodo,
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

Route::middleware(RequireFilamentLogin::class)->post('/admin/sales-goals', function (Request $request) {
    $data = $request->validate([
        'goals' => ['required', 'array'],
        'goals.*' => ['nullable', 'integer', 'min:0'],
    ]);

    $goals = $data['goals'] ?? [];

    for ($weekday = 1; $weekday <= 7; $weekday++) {
        $amount = isset($goals[$weekday]) ? (int) $goals[$weekday] : 0;

        SalesWeekdayGoal::updateOrCreate(
            ['weekday' => $weekday],
            ['amount' => $amount]
        );
    }

    return redirect('/#admin')->with('sale_success', 'Metas de ventas actualizadas.');
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
