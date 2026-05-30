<?php

namespace App\Console\Commands;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoSeedLast3DaysSalesCommand extends Command
{
    protected $signature = 'demo:seed-last-3-days-sales {--force : Re-seed even if marker exists}';
    protected $description = 'Creates 30 sales across the last 3 days (timezone America/Bogota) with controlled stock (40 per product).';

    public function handle(): int
    {
        $tz = 'America/Bogota';
        $now = Carbon::now($tz);

        $today = $now->copy()->startOfDay();
        $days = [
            (clone $today)->subDays(2), // anteayer
            (clone $today)->subDay(),  // ayer
            (clone $today),            // hoy
        ];

        $totalSales = 30;
        $salesPerDay = 10;

        $force = (bool) $this->option('force');

        $markerKey = sprintf(
            'demo_last3_sales_control_%s_%s_%s',
            $days[0]->toDateString(),
            $days[1]->toDateString(),
            $days[2]->toDateString()
        );

        $markerPath = storage_path('app/' . $markerKey . '.txt');

        if (!$force && file_exists($markerPath)) {
            $this->info('Demo last-3-days sales already seeded for these dates. Use --force to reseed.');
            return self::SUCCESS;
        }

        $seededAt = Carbon::now($tz);

        DB::transaction(function () use ($days, $salesPerDay, $totalSales, $force, $seededAt, $tz) {
            // Range boundaries for delete
            $rangeStart = (clone $days[0])->startOfDay();
            $rangeEnd = (clone $days[2])->endOfDay();

            // 1) Reset demo data inside range (so "30 sales" is exact)
            $salesToDelete = Sale::query()
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->pluck('id')
                ->all();

            if (!empty($salesToDelete)) {
                Sale::query()->whereIn('id', $salesToDelete)->delete();
            }

            InventoryMovement::query()
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->delete();

            // 2) Ensure exactly 40 units stock per each of the 8 products
            $products = Product::query()
                ->orderBy('id')
                ->take(8)
                ->get(['id', 'price', 'stock']);

            if ($products->count() < 8) {
                throw new \RuntimeException('Expected at least 8 products in DB.');
            }

            $productIds = $products->pluck('id')->map(fn ($v) => (int) $v)->values()->all();
            $productById = $products->keyBy('id');

            foreach ($products as $p) {
                $p->update(['stock' => 40]);
            }

            // Add “ingreso” movements to make the inventory history show stock baseline.
            // (InventoryMovement is unsigned quantity in this app, so we only record the increases.)
            $movementTimeBase = (clone $days[0])->addMinutes(5);
            foreach ($products as $p) {
                InventoryMovement::create([
                    'product_id' => (int) $p->id,
                    'quantity' => 40,
                    'created_at' => (clone $movementTimeBase)->addMinutes((int) $p->id),
                    'updated_at' => (clone $movementTimeBase)->addMinutes((int) $p->id),
                ]);
            }

            // 3) Create 30 SALES with controlled units (quantity = 1 per ticket)
            // Each day uses a different set of products (no overlap between days).
            $dayAllowedProductIds = [
                array_slice($productIds, 0, 3), // anteayer: 3 productos
                array_slice($productIds, 3, 3), // ayer: 3 productos
                array_slice($productIds, 6, 2), // hoy: 2 productos
            ];

            $createdSales = 0;

            for ($dayIdx = 0; $dayIdx < 3; $dayIdx++) {
                $dayStart = $days[$dayIdx];
                $allowed = $dayAllowedProductIds[$dayIdx] ?? [];

                if (count($allowed) === 0) {
                    throw new \RuntimeException("No allowed products for dayIdx={$dayIdx}.");
                }

                for ($i = 0; $i < $salesPerDay; $i++) {
                    $saleTime = (clone $dayStart)->addMinutes(10)
                        ->addSeconds(random_int(0, 40))
                        ->addMinutes(random_int(0, 540));

                    // Payment method varied
                    $paymentMethod = (($dayIdx + $i) % 2 === 0) ? 'efectivo' : 'nequi';

                    // Exactly 1 item per sale to keep stock controlled
                    $productId = $allowed[$i % count($allowed)];
                    $p = $productById->get($productId);

                    if (!$p) {
                        continue;
                    }

                    $unitPrice = (int) $p->price;
                    $qty = 1;
                    $lineTotal = $unitPrice * $qty;

                    $sale = Sale::create([
                        'payment_method' => $paymentMethod,
                        'total' => 0,
                    ]);

                    $sale->forceFill([
                        'created_at' => $saleTime,
                        'updated_at' => $saleTime,
                    ])->save();

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

                    $sale->update(['total' => (int) $lineTotal]);

                    // Decrement product stock
                    $p->decrement('stock', $qty);

                    $createdSales++;
                    if ($createdSales >= $totalSales) {
                        break 2;
                    }
                }
            }

            if ($createdSales !== $totalSales) {
                throw new \RuntimeException("Expected to create {$totalSales} sales, but created {$createdSales}.");
            }
        });

        file_put_contents($markerPath, sprintf("seeded_at=%s\n", $seededAt->toIso8601String()));

        $this->info('30 sales seeded for the last 3 days successfully (stock controlled).');
        return self::SUCCESS;
    }
}
