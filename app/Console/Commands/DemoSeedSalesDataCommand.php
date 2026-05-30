<?php

namespace App\Console\Commands;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoSeedSalesDataCommand extends Command
{
    protected $signature = 'demo:seed-sales-data {--force : Re-seed even if marker exists}';
    protected $description = 'Adds +30 stock to each of the 8 products and simulates yesterday/today sales for dashboard statistics.';

    public function handle(): int
    {
        $tz = 'America/Bogota';
        $today = Carbon::now($tz)->startOfDay();
        $yesterday = (clone $today)->subDay();

        $markerKey = sprintf(
            'demo_seed_sales_%s_%s',
            $yesterday->toDateString(),
            $today->toDateString()
        );

        $markerPath = storage_path('app/' . $markerKey . '.txt');

        $force = (bool) $this->option('force');
        if (!$force && file_exists($markerPath)) {
            $this->info('Demo sales data already seeded for this date range. Use --force to reseed.');
            return self::SUCCESS;
        }

        $seededAt = Carbon::now($tz);

        DB::transaction(function () use ($today, $yesterday, $seededAt, $markerPath) {
            $products = Product::query()
                ->select(['id', 'name', 'price', 'stock'])
                ->orderBy('id')
                ->take(8)
                ->lockForUpdate()
                ->get();

            if ($products->count() < 8) {
                throw new \RuntimeException('Expected at least 8 products in DB.');
            }

            $day29DesiredQty = 12;
            $day30DesiredQty = 8;
            $stockAddQty = 30;

            // 1) Add +30 stock and create InventoryMovement records
            $movementIndex = 0;
            foreach ($products as $product) {
                $product->increment('stock', $stockAddQty);

                $movementTime = (clone $yesterday)->addMinutes(1 + $movementIndex);
                $movementIndex++;

                InventoryMovement::create([
                    'product_id' => (int) $product->id,
                    'quantity' => $stockAddQty,
                    'created_at' => $movementTime,
                    'updated_at' => $movementTime,
                ]);
            }

            // Refresh stock after increments
            $products = Product::query()
                ->select(['id', 'name', 'price', 'stock'])
                ->whereIn('id', $products->pluck('id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            // 2) Compute quantities sold per product for day29/day30 based on availability after restock
            $qtyDay29ByProductId = [];
            $qtyDay30ByProductId = [];

            foreach ($products as $product) {
                $available = (int) $product->stock;

                $qty29 = min($day29DesiredQty, $available);
                $available -= $qty29;

                $qty30 = min($day30DesiredQty, $available);

                $qtyDay29ByProductId[(int) $product->id] = $qty29;
                $qtyDay30ByProductId[(int) $product->id] = $qty30;
            }

            // Payment method rule: high-price products -> efectivo, low-price products -> nequi
            $paymentMethodFor = function (Product $product): string {
                return ((int) $product->price) >= 15000 ? 'efectivo' : 'nequi';
            };

            $createSaleForDay = function (
                Carbon $saleDayStart,
                string $paymentMethod,
                array $qtyByProductId,
                array $productIndexById
            ) use ($products, $paymentMethodFor) {
                $items = [];

                foreach ($qtyByProductId as $productId => $qty) {
                    if ((int) $qty <= 0) {
                        continue;
                    }

                    /** @var Product $p */
                    $p = $productIndexById[(int) $productId];

                    if ($paymentMethodFor($p) !== $paymentMethod) {
                        continue;
                    }

                    $items[] = [
                        'product' => $p,
                        'quantity' => (int) $qty,
                    ];
                }

                if (empty($items)) {
                    return null;
                }

                $saleTime = (clone $saleDayStart)->addMinutes(10 + random_int(0, 30));

                $sale = Sale::create([
                    'payment_method' => $paymentMethod,
                    'total' => 0,
                ]);

                $sale->forceFill([
                    'created_at' => $saleTime,
                    'updated_at' => $saleTime,
                ])->save();

                $total = 0;

                foreach ($items as $row) {
                    /** @var Product $p */
                    $p = $row['product'];
                    $qty = (int) $row['quantity'];
                    $unitPrice = (int) $p->price;
                    $lineTotal = $unitPrice * $qty;

                    $saleItem = SaleItem::create([
                        'sale_id' => (int) $sale->id,
                        'product_id' => (int) $p->id,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'line_total' => (int) $lineTotal,
                    ]);

                    $saleItem->forceFill([
                        'created_at' => $saleTime,
                        'updated_at' => $saleTime,
                    ])->save();

                    // Decrement stock (Sales don't create InventoryMovement in this app)
                    $p->decrement('stock', $qty);

                    $total += $lineTotal;
                }

                $sale->update(['total' => (int) $total]);

                return $sale;
            };

            $productIndexById = $products->keyBy('id')->all();

            // 29
            $createSaleForDay($yesterday, 'nequi', $qtyDay29ByProductId, $productIndexById);
            $createSaleForDay($yesterday, 'efectivo', $qtyDay29ByProductId, $productIndexById);

            // 30
            $createSaleForDay($today, 'nequi', $qtyDay30ByProductId, $productIndexById);
            $createSaleForDay($today, 'efectivo', $qtyDay30ByProductId, $productIndexById);

            // Marker
            file_put_contents($markerPath, sprintf("seeded_at=%s\n", $seededAt->toIso8601String()));
        });

        $this->info('Demo sales seeded successfully.');
        return self::SUCCESS;
    }
}
