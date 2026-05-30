<?php

namespace App\Console\Commands;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoFixSeedSalesDatesCommand extends Command
{
    protected $signature = 'demo:fix-seed-sales-dates {--force : Fix even if it seems already fixed}';
    protected $description = 'Fixes created_at/updated_at of demo-seeded sales (yesterday/today) based on the marker file (without changing stock).';

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
        if (!file_exists($markerPath)) {
            $this->error("Marker not found: {$markerPath}");
            return self::FAILURE;
        }

        $content = trim((string) file_get_contents($markerPath));
        $seededAtIso = null;

        foreach (preg_split("/\r\n|\n|\r/", $content) as $line) {
            if (str_starts_with($line, 'seeded_at=')) {
                $seededAtIso = trim(substr($line, strlen('seeded_at=')));
                break;
            }
        }

        if (!$seededAtIso) {
            $this->error('Invalid marker file format.');
            return self::FAILURE;
        }

        $seededAt = Carbon::parse($seededAtIso)->setTimezone($tz);

        $todayDate = $today->toDateString();
        $yesterdayDate = $yesterday->toDateString();

        // Expectation: previous seed created 4 sales back-to-back (2 for yesterday, 2 for today).
        // We'll grab the sales created very close to seededAt and with created_at currently on todayDate.
        $windowStart = (clone $seededAt)->subMinutes(20);
        $windowEnd = (clone $seededAt)->addMinutes(20);

        $salesCandidates = Sale::query()
            ->whereBetween('created_at', [$windowStart, $windowEnd])
            ->whereIn('payment_method', ['efectivo', 'nequi'])
            ->orderBy('id')
            ->get();

        if ($salesCandidates->count() < 4) {
            $this->error('Could not find 4 candidate sales near marker time. Found: ' . $salesCandidates->count());
            return self::FAILURE;
        }

        $exact4 = $salesCandidates->take(4)->values();

        $this->info('Candidate sales (id, payment_method, current_date):');
        foreach ($exact4 as $s) {
            $this->line(sprintf('  id=%d pm=%s date=%s', $s->id, $s->payment_method, $s->created_at?->format('Y-m-d')));
        }

        // If already fixed, allow user to skip unless --force
        $alreadyFixed = $exact4->filter(fn ($s) => $s->created_at?->toDateString() === $yesterdayDate)->count() === 2;
        if ($alreadyFixed && !$this->option('force')) {
            $this->info('Looks already fixed. Use --force to re-apply.');
            return self::SUCCESS;
        }

        // Create deterministic times (so dashboard groups by DATE(created_at))
        $tYesterdayA = Carbon::parse($yesterdayDate . ' 12:10:00', $tz)->addMinutes(1);
        $tYesterdayB = Carbon::parse($yesterdayDate . ' 12:10:00', $tz)->addMinutes(2);
        $tTodayA = Carbon::parse($todayDate . ' 12:10:00', $tz)->addMinutes(3);
        $tTodayB = Carbon::parse($todayDate . ' 12:10:00', $tz)->addMinutes(4);

        $timesByIndex = [
            0 => $tYesterdayA,
            1 => $tYesterdayB,
            2 => $tTodayA,
            3 => $tTodayB,
        ];

        DB::transaction(function () use ($exact4, $timesByIndex) {
            foreach ($exact4 as $idx => $sale) {
                /** @var Sale $sale */
                $t = $timesByIndex[$idx] ?? null;
                if (!$t) {
                    continue;
                }

                Sale::query()
                    ->whereKey($sale->id)
                    ->update([
                        'created_at' => $t,
                        'updated_at' => $t,
                    ]);

                DB::table('sale_items')
                    ->where('sale_id', $sale->id)
                    ->update([
                        'created_at' => $t,
                        'updated_at' => $t,
                    ]);
            }
        });

        $this->info('Seeded sales dates fixed successfully.');
        return self::SUCCESS;
    }
}
