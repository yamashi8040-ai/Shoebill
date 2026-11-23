<?php

namespace App\Services\Servers;

use App\Models\Server;
use App\Models\ServerStatDaily;
use App\Models\ServerStatSample;
use Carbon\CarbonInterface;

class ServerStatsService
{
    /** Periods the Status page can show, in days; null is all time. */
    public const PERIODS = ['day' => 1, 'week' => 7, 'month' => 30, 'all' => null];

    /**
     * Averages for the chosen period, the same-length period before it, and a chart series covering it.
     *
     * @return array{current: array, previous: ?array, series: array{labels: string[], cpu: float[], memory: float[], players: (float|null)[]}}
     */
    public function forPeriod(Server $server, string $period): array
    {
        $days = array_key_exists($period, self::PERIODS) ? self::PERIODS[$period] : 1;

        if ($days === null) {
            $all = $this->summary($server)['all'];
            $first = $this->firstRecordedAt($server);

            return ['current' => $all + ['uptime' => $first ? $this->uptimeShare($all['samples'], $first, now()) : null], 'previous' => null, 'series' => $this->dailySeries($server)];
        }

        $since = now()->subDays($days);
        $current = $this->totals($server, $since);
        $previous = $this->totals($server, $since->copy()->subDays($days), $since);

        return [
            'current' => $this->averages($current) + ['uptime' => $this->uptime($server, $current['samples'], $since, now())],
            'previous' => $this->averages($previous) + ['uptime' => $this->uptime($server, $previous['samples'], $since->copy()->subDays($days), $since)],
            'series' => $this->sampleSeries($server, $since, $days === 1 ? 'H:00' : ($days <= 7 ? 'n/j H:00' : 'n/j')),
        ];
    }

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
    private function totals(Server $server, CarbonInterface $since, ?CarbonInterface $until = null): array
    {
        $row = ServerStatSample::query()->where('server_id', $server->id)->where('created_at', '>=', $since)
            ->when($until, fn ($query) => $query->where('created_at', '<', $until))
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

    /** Per-minute samples since $since, averaged into buckets labelled with $format (hours or days). */
    private function sampleSeries(Server $server, CarbonInterface $since, string $format): array
    {
        $buckets = ServerStatSample::query()->where('server_id', $server->id)->where('created_at', '>=', $since)
            ->orderBy('created_at')->get(['cpu', 'memory_bytes', 'players', 'created_at'])
            ->groupBy(fn (ServerStatSample $sample) => $sample->created_at->format($format));

        return [
            'labels' => $buckets->keys()->all(),
            'cpu' => $buckets->map(fn ($s) => round($s->avg('cpu'), 1))->values()->all(),
            'memory' => $buckets->map(fn ($s) => round($s->avg('memory_bytes') / 1048576))->values()->all(),
            'players' => $buckets->map(fn ($s) => $s->whereNotNull('players')->isEmpty() ? null : round($s->avg('players'), 1))->values()->all(),
        ];
    }

    /** All time: one point per day from the daily rollups, plus today from the samples. */
    private function dailySeries(Server $server): array
    {
        $days = ServerStatDaily::query()->where('server_id', $server->id)->orderBy('date')->get()
            ->mapWithKeys(fn (ServerStatDaily $day) => [$day->date->format('Y/n/j') => [
                'cpu' => $day->samples ? round($day->cpu_sum / $day->samples, 1) : 0,
                'memory' => $day->samples ? round($day->memory_sum / $day->samples / 1048576) : 0,
                'players' => $day->player_samples ? round($day->players_sum / $day->player_samples, 1) : null,
            ]]);

        $today = $this->averages($this->totals($server, now()->startOfDay()));
        if ($today['samples']) {
            $days[now()->format('Y/n/j')] = ['cpu' => round($today['cpu'], 1), 'memory' => round($today['memory'] / 1048576), 'players' => $today['players'] === null ? null : round($today['players'], 1)];
        }

        return [
            'labels' => $days->keys()->all(),
            'cpu' => $days->pluck('cpu')->all(),
            'memory' => $days->pluck('memory')->all(),
            'players' => $days->pluck('players')->all(),
        ];
    }

    /**
     * Share of the window the server was running: samples are only taken while it runs, one per minute.
     * The window starts no earlier than the first recording, so servers older than the stats don't look down.
     */
    public function uptime(Server $server, int $samples, CarbonInterface $from, CarbonInterface $to): ?float
    {
        $first = $this->firstRecordedAt($server);
        if (!$first || $first->greaterThanOrEqualTo($to)) {
            return null;
        }

        return $this->uptimeShare($samples, $first->greaterThan($from) ? $first : $from, $to);
    }

    private function uptimeShare(int $samples, CarbonInterface $from, CarbonInterface $to): ?float
    {
        $minutes = (int) $from->diffInMinutes($to);

        return $minutes < 1 ? null : min(1.0, $samples / $minutes);
    }

    private function firstRecordedAt(Server $server): ?CarbonInterface
    {
        return once(function () use ($server) {
            $daily = ServerStatDaily::query()->where('server_id', $server->id)->min('date');
            $sample = ServerStatSample::query()->where('server_id', $server->id)->min('created_at');

            return collect([$daily, $sample])->filter()->map(fn ($at) => \Illuminate\Support\Carbon::parse($at))->min();
        });
    }
}
