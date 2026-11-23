<?php

namespace App\Services\Servers;

use App\Enums\ServerState;
use App\Models\ActivityLog;
use App\Models\Node;
use App\Models\Server;
use App\Models\ServerStatSample;
use Illuminate\Support\Collection;

/**
 * The admin dashboard's numbers: is the whole fleet OK right now, and what needs attention.
 * Reads the per-minute samples and caches only, so the page never waits on a daemon.
 */
class FleetStatsService
{
    /** A server counts as running when it was sampled (only running servers are) within this many minutes. */
    public const LIVE_MINUTES = 3;

    /** A node is flagged once this share of its memory is handed out to servers. */
    public const NODE_FULL = 0.9;

    /** @return array{servers: int, running: int, nodes: int, nodes_down: int, cpu: ?float, cpu_before: ?float, players: ?int, players_before: ?int, memory_allocated: int, memory_total: int} */
    public function summary(): array
    {
        $now = now();
        $live = ServerStatSample::query()->where('created_at', '>=', $now->copy()->subMinutes(self::LIVE_MINUTES));
        $dayAgo = ServerStatSample::query()->whereBetween('created_at', [$now->copy()->subDay()->subMinutes(self::LIVE_MINUTES), $now->copy()->subDay()]);

        $nodes = Node::query()->get(['id', 'memory']);

        return [
            'servers' => Server::query()->count(),
            'running' => (clone $live)->distinct()->count('server_id'),
            'nodes' => $nodes->count(),
            'nodes_down' => $nodes->filter(fn (Node $node) => $this->isDown($node))->count(),
            'cpu' => $this->averageCpu($now->copy()->subHour(), $now),
            'cpu_before' => $this->averageCpu($now->copy()->subDay()->subHour(), $now->copy()->subDay()),
            'players' => $this->playersAt($live),
            'players_before' => $this->playersAt($dayAgo),
            'uptime' => $this->fleetUptime(),
            'memory_allocated' => (int) Server::query()->sum('memory'),
            'memory_total' => (int) $nodes->sum('memory'),
        ];
    }

    /** @return Collection<int, array{kind: string, title: string, detail: string, url: ?string}> problems only; empty means all is well */
    public function attention(): Collection
    {
        $items = collect();

        ActivityLog::query()->where('event', 'server:crashed')->where('timestamp', '>=', now()->subDay())
            ->with('subjects.subject')->latest('timestamp')->limit(5)->get()
            ->each(function (ActivityLog $log) use ($items) {
                $server = $log->subjects->first()?->subject;
                if ($server instanceof Server) {
                    $items->push(['kind' => 'crash', 'title' => $server->name, 'detail' => $log->timestamp->diffForHumans(), 'url' => "/server/$server->uuid_short/console"]);
                }
            });

        foreach ($this->nodeCapacity() as $node) {
            if ($node['down']) {
                $items->push(['kind' => 'node_down', 'title' => $node['name'], 'detail' => $node['location'] ?? '', 'url' => "/admin/nodes/{$node['id']}/edit"]);
            } elseif ($node['share'] !== null && $node['share'] >= self::NODE_FULL) {
                $items->push(['kind' => 'node_full', 'title' => $node['name'], 'detail' => round($node['share'] * 100) . '%', 'url' => "/admin/nodes/{$node['id']}/edit"]);
            }
        }

        Server::query()->whereIn('status', [ServerState::InstallFailed, ServerState::ReinstallFailed])->limit(5)->get()
            ->each(fn (Server $server) => $items->push(['kind' => 'install_failed', 'title' => $server->name, 'detail' => '', 'url' => "/admin/servers/$server->id/edit"]));

        return $items;
    }

    /** @return Collection<int, array{id: int, name: string, location: ?string, down: bool, allocated: int, total: int, share: ?float}> fullest first */
    public function nodeCapacity(): Collection
    {
        return Node::query()->withSum('servers', 'memory')->get()
            ->map(fn (Node $node) => [
                'id' => $node->id,
                'name' => $node->name,
                'location' => $node->location,
                'down' => $this->isDown($node),
                'allocated' => (int) $node->servers_sum_memory,
                'total' => $node->memory,
                'share' => $node->memory > 0 ? $node->servers_sum_memory / $node->memory : null,
            ])
            ->sortByDesc(fn (array $node) => $node['share'] ?? -1)
            ->values();
    }

    /** @return Collection<int, array{name: string, url: string, cpu: float, players: ?float}> busiest servers today by average CPU */
    public function busiestServers(int $limit = 5): Collection
    {
        $rows = ServerStatSample::query()->where('created_at', '>=', now()->startOfDay())
            ->selectRaw('server_id, avg(cpu) as cpu, avg(players) as players')
            ->groupBy('server_id')->orderByDesc('cpu')->limit($limit)->get();
        $servers = Server::query()->whereIn('id', $rows->pluck('server_id'))->get(['id', 'name', 'uuid_short'])->keyBy('id');

        return $rows->filter(fn ($row) => $servers->has($row->server_id))->map(fn ($row) => [
            'name' => $servers[$row->server_id]->name,
            'url' => '/server/' . $servers[$row->server_id]->uuid_short . '/status',
            'cpu' => (float) $row->cpu,
            'players' => $row->players === null ? null : (float) $row->players,
        ])->values();
    }

    private function isDown(Node $node): bool
    {
        if ($node->isSimulated()) {
            return false;
        }

        // Only what is already known: the backoff flag or a cached failed lookup. Never a live request here.
        return $node->isUnreachable() || isset(cache()->get("nodes.$node->id.system_information")['exception']);
    }

    private function averageCpu($from, $to): ?float
    {
        $avg = ServerStatSample::query()->whereBetween('created_at', [$from, $to])->avg('cpu');

        return $avg === null ? null : (float) $avg;
    }

    /** Players across the fleet in the newest sample of each server inside the window; null when no game reported any. */
    private function playersAt($window): ?int
    {
        $latest = (clone $window)->whereNotNull('players')->selectRaw('server_id, max(created_at) as at')->groupBy('server_id');
        $rows = ServerStatSample::query()->joinSub($latest, 'l', fn ($join) => $join->on('server_stat_samples.server_id', '=', 'l.server_id')->on('server_stat_samples.created_at', '=', 'l.at'))->get(['players']);

        return $rows->isEmpty() ? null : (int) $rows->sum('players');
    }

    /**
     * Running minutes over expected minutes across all servers in the last 24 hours.
     * shortcut: one uptime lookup per server; aggregate in SQL if fleets grow past a few hundred servers.
     */
    private function fleetUptime(): ?float
    {
        $stats = app(ServerStatsService::class);
        $since = now()->subDay();
        $samples = ServerStatSample::query()->where('created_at', '>=', $since)->selectRaw('server_id, count(*) as n')->groupBy('server_id')->pluck('n', 'server_id');

        $shares = Server::query()->get()->map(fn (Server $server) => $stats->uptime($server, (int) ($samples[$server->id] ?? 0), $since, now()))->filter(fn ($share) => $share !== null);

        return $shares->isEmpty() ? null : $shares->avg();
    }
}
