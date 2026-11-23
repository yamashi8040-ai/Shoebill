<?php

namespace App\Services\Servers;

use App\Models\Server;
use App\Models\ServerStatDaily;
use App\Models\ServerStatSample;
use Carbon\CarbonInterface;

class ServerStatsService
{
    /**
     * Averages for today, the last 7 days, the last 30 days and all time.
     *
     * @return array<string, array{samples: int, cpu: ?float, memory: ?float, players: ?float, peak: ?int}>
     */
    public function summary(Server $server): array
    {
        $today = $this->totals($server, now()->startOfDay());
        $daily = ServerStatDaily::query()->where('server_id', $server->id)
            ->selectRaw('coalesce(sum(samples), 0) as samples, coalesce(sum(cpu_sum), 0) as cpu_sum, coalesce(sum(memory_sum), 0) as memory_sum, coalesce(sum(player_samples), 0) as player_samples, coalesce(sum(players_sum), 0) as players_sum, max(players_peak) as peak')
            ->first();

        $all = [
            'samples' => $today['samples'] + (int) $daily->samples,
            'cpu_sum' => $today['cpu_sum'] + (float) $daily->cpu_sum,
            'memory_sum' => $today['memory_sum'] + (float) $daily->memory_sum,
            'player_samples' => $today['player_samples'] + (int) $daily->player_samples,
            'players_sum' => $today['players_sum'] + (int) $daily->players_sum,
            'peak' => collect([$today['peak'], $daily->peak])->filter(fn ($v) => $v !== null)->max(),
        ];

        return [
            'today' => $this->averages($today),
            'week' => $this->averages($this->totals($server, now()->subDays(7))),
            'month' => $this->averages($this->totals($server, now()->subDays(30))),
            'all' => $this->averages($all),
        ];
    }

    /** @return array{labels: string[], cpu: float[], memory: float[], players: (float|null)[]} hourly averages for the last 24 hours */
    public function lastDay(Server $server): array
    {
        $hours = ServerStatSample::query()->where('server_id', $server->id)->where('created_at', '>=', now()->subDay())
            ->get(['cpu', 'memory_bytes', 'players', 'created_at'])
            ->groupBy(fn (ServerStatSample $sample) => $sample->created_at->format('H:00'));

        return [
            'labels' => $hours->keys()->all(),
            'cpu' => $hours->map(fn ($s) => round($s->avg('cpu'), 1))->values()->all(),
            'memory' => $hours->map(fn ($s) => round($s->avg('memory_bytes') / 1048576))->values()->all(),
            'players' => $hours->map(fn ($s) => $s->whereNotNull('players')->isEmpty() ? null : round($s->avg('players'), 1))->values()->all(),
        ];
    }

    /** @return array{samples: int, cpu_sum: float, memory_sum: float, player_samples: int, players_sum: int, peak: ?int} */
    private function totals(Server $server, CarbonInterface $since): array
    {
        $row = ServerStatSample::query()->where('server_id', $server->id)->where('created_at', '>=', $since)
            ->selectRaw('count(*) as samples, coalesce(sum(cpu), 0) as cpu_sum, coalesce(sum(memory_bytes), 0) as memory_sum, count(players) as player_samples, coalesce(sum(players), 0) as players_sum, max(players) as peak')
            ->first();

        return [
            'samples' => (int) $row->samples,
            'cpu_sum' => (float) $row->cpu_sum,
            'memory_sum' => (float) $row->memory_sum,
            'player_samples' => (int) $row->player_samples,
            'players_sum' => (int) $row->players_sum,
            'peak' => $row->peak === null ? null : (int) $row->peak,
        ];
    }

    /** @param array{samples: int, cpu_sum: float, memory_sum: float, player_samples: int, players_sum: int, peak: ?int} $t */
    private function averages(array $t): array
    {
        return [
            'samples' => $t['samples'],
            'cpu' => $t['samples'] ? $t['cpu_sum'] / $t['samples'] : null,
            'memory' => $t['samples'] ? $t['memory_sum'] / $t['samples'] : null,
            'players' => $t['player_samples'] ? $t['players_sum'] / $t['player_samples'] : null,
            'peak' => $t['peak'],
        ];
    }
}
