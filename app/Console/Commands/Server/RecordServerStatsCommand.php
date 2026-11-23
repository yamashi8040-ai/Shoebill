<?php

namespace App\Console\Commands\Server;

use App\Enums\ShoebillFeature;
use App\Jobs\Server\CheckServerCrash;
use App\Models\Server;
use App\Models\ServerPlayer;
use App\Models\ServerStatSample;
use App\Services\Features\FeatureService;
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
                $this->watchForCrash($server, $resources['state'] ?? null);
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
        if (!app(FeatureService::class)->enabled(ShoebillFeature::PlayerHistory, $server)) {
            return;
        }
        $names = array_slice(array_unique(array_map(fn ($n) => mb_substr($n, 0, 255), $names)), 0, 200);
        if (!$names) {
            return;
        }

        ServerPlayer::query()->insertOrIgnore(array_map(fn ($name) => ['server_id' => $server->id, 'name' => $name, 'first_seen_at' => $now, 'last_seen_at' => $now, 'minutes_played' => 0], $names));
        ServerPlayer::query()->where('server_id', $server->id)->whereIn('name', $names)
            ->update(['last_seen_at' => $now, 'minutes_played' => DB::raw('minutes_played + 1')]);
    }

    /**
     * Pterodactyl Wings does not report state changes to the panel, so crash alerts compare this minute's state with the last one.
     * shortcut: a server that crashes and is restarted by Wings within the same minute is missed; Pelican Wings reports those.
     */
    public function watchForCrash(Server $server, ?string $state): void
    {
        if (!$server->node->isPterodactylWings() || $state === null) {
            return;
        }

        $key = "servers.$server->uuid.last_state";
        $previous = Cache::get($key);
        Cache::put($key, $state, now()->addMinutes(10));

        if ($previous === 'running' && $state === 'offline') {
            CheckServerCrash::dispatch($server, now()->getTimestamp())->delay(CheckServerCrash::DELAY_SECONDS);
        }
    }
}
