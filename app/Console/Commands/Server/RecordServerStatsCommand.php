<?php

namespace App\Console\Commands\Server;

use App\Models\Server;
use App\Models\ServerPlayer;
use App\Models\ServerStatSample;
use App\Services\GameQuery\GameQueryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecordServerStatsCommand extends Command
{
    protected $signature = 'shoebill:stats:record';

    protected $description = 'Record one CPU, memory and player sample for every running server.';

    public function handle(GameQueryService $query): void
    {
        $now = now()->startOfMinute();

        // shortcut: servers are polled one after another; move to queued jobs if a minute is not enough for the fleet.
        Server::query()->whereNull('status')->with(['egg', 'allocation', 'node', 'variables'])->chunkById(100, function ($servers) use ($query, $now) {
            foreach ($servers as $server) {
                $resources = $server->retrieveResources();
                if (($resources['state'] ?? null) !== 'running') {
                    continue;
                }

                $players = $query->query($server);
                if ($players) {
                    // The live player list on the Players page reads this instead of querying again.
                    Cache::put("servers.$server->uuid.players", $players, now()->addMinutes(2));
                    $this->recordPlayers($server, $players['players'], $now);
                }

                ServerStatSample::create([
                    'server_id' => $server->id,
                    'cpu' => (float) ($resources['cpu_absolute'] ?? 0),
                    'memory_bytes' => (int) ($resources['memory_bytes'] ?? 0),
                    'players' => $players['online'] ?? null,
                    'max_players' => $players['max'] ?? null,
                    'created_at' => $now,
                ]);
            }
        });
    }

    /** @param string[] $names */
    public function recordPlayers(Server $server, array $names, \DateTimeInterface $now): void
    {
        // shortcut: Minecraft only reports a sample of up to 12 names, so on busy servers history misses some players; use RCON/query if that matters.
        $names = array_slice(array_unique(array_map(fn ($n) => mb_substr($n, 0, 255), $names)), 0, 200);
        if (!$names) {
            return;
        }

        ServerPlayer::query()->insertOrIgnore(array_map(fn ($name) => ['server_id' => $server->id, 'name' => $name, 'first_seen_at' => $now, 'last_seen_at' => $now, 'minutes_played' => 0], $names));
        ServerPlayer::query()->where('server_id', $server->id)->whereIn('name', $names)
            ->update(['last_seen_at' => $now, 'minutes_played' => DB::raw('minutes_played + 1')]);
    }
}
