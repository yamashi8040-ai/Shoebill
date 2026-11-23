<?php

namespace App\Jobs\Server;

use App\Enums\ShoebillFeature;
use App\Events\Server\Crashed;
use App\Models\Server;
use App\Services\Features\FeatureService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs a little after the container went straight from running to offline (a stop from the panel or websocket passes through "stopping").
 * Waiting lets the daemon's batched activity log arrive, so a `stop` typed into the console is not reported as a crash.
 * shortcut: any console command in the window suppresses the alert, and an in-game stop by a player still alerts; parse the command per game if that gets noisy.
 */
class CheckServerCrash implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const DELAY_SECONDS = 90;

    public bool $deleteWhenMissingModels = true;

    public function __construct(public Server $server, public int $exitedAt) {}

    public function handle(): void
    {
        if (!app(FeatureService::class)->enabled(ShoebillFeature::CrashAlerts, $this->server)) {
            return;
        }

        $intended = $this->server->activity()
            ->whereIn('event', ['server:console.command', 'server:power.stop', 'server:power.kill', 'server:power.restart'])
            ->where('timestamp', '>=', now()->setTimestamp($this->exitedAt)->subMinutes(3))
            ->exists();

        if (!$intended) {
            event(new Crashed($this->server));
        }
    }
}
