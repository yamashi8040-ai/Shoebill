<?php

namespace App\Console\Commands\Server;

use App\Models\ServerStatDaily;
use App\Models\ServerStatSample;
use Illuminate\Console\Command;

class RollupServerStatsCommand extends Command
{
    public const KEEP_DAYS = 31;

    protected $signature = 'shoebill:stats:rollup';

    protected $description = 'Fold finished days of server samples into daily totals and prune old samples.';

    public function handle(): void
    {
        for ($day = now()->subDays(self::KEEP_DAYS)->startOfDay(); $day->lt(now()->startOfDay()); $day = $day->copy()->addDay()) {
            if (ServerStatDaily::query()->whereDate('date', $day)->exists()) {
                continue;
            }

            ServerStatSample::query()
                ->whereBetween('created_at', [$day, $day->copy()->endOfDay()])
                ->groupBy('server_id')
                ->selectRaw('server_id, count(*) as samples, sum(cpu) as cpu_sum, sum(memory_bytes) as memory_sum, count(players) as player_samples, coalesce(sum(players), 0) as players_sum, max(players) as players_peak')
                ->get()
                ->each(fn ($row) => ServerStatDaily::create([...$row->only(['server_id', 'samples', 'cpu_sum', 'memory_sum', 'player_samples', 'players_sum', 'players_peak']), 'date' => $day->toDateString()]));
        }

        ServerStatSample::query()->where('created_at', '<', now()->subDays(self::KEEP_DAYS))->delete();
    }
}
