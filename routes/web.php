<?php

use App\Http\Middleware\RequireFilamentLogin;
use App\Models\Product;
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
    $productos = Product::query()->inRandomOrder()->take(8)->get();
    $catalogo = Product::query()->orderBy('name')->get();
    $ventas = Sale::query()->latest()->with('items.product')->take(25)->get();

    $kpis = [
        'total_productos' => (int) Product::query()->count(),
        'stock_total' => (int) Product::query()->sum('stock'),
        'total_facturas' => (int) Sale::query()->count(),
        'ingresos_totales' => (int) Sale::query()->sum('total'),
    ];

    $ultimosVendidos = SaleItem::query()
        ->latest()
        ->with(['product', 'sale'])
        ->take(18)
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

    return view('dashboard', [
        'productos' => $productos,
        'catalogo' => $catalogo,
        'ventas' => $ventas,
        'kpis' => $kpis,
        'ultimosVendidos' => $ultimosVendidos,
        'ultimasFacturas' => $ultimasFacturas,
        'bajoStock' => $bajoStock,
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

    return redirect('/#ventas');
});
