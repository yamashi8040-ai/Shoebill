<?php

namespace App\Tests\Integration\Api\Remote;

use App\Events\Server\Crashed;
use App\Facades\Activity;
use App\Jobs\Server\CheckServerCrash;
use App\Models\Server;
use App\Notifications\ServerCrashed;
use App\Tests\Integration\IntegrationTestCase;
use Filament\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

class ServerCrashAlertTest extends IntegrationTestCase
{
    private function sendState(Server $server, string $state): void
    {
        $node = $server->node;
        $this->withHeader('Authorization', "Bearer $node->daemon_token_id." . $node->daemon_token)
            ->postJson("/api/remote/servers/$server->uuid/container/status", ['data' => ['new_state' => $state]])
            ->assertOk();
    }

    public function test_only_a_direct_running_to_offline_jump_queues_a_crash_check(): void
    {
        Queue::fake();
        $server = $this->createServerModel();

        $this->sendState($server, 'running');
        $this->sendState($server, 'stopping');
        $this->sendState($server, 'offline');
        Queue::assertNothingPushed();

        $this->sendState($server, 'running');
        $this->sendState($server, 'offline');
        Queue::assertPushed(CheckServerCrash::class, 1);
    }

    public function test_the_check_fires_crashed_unless_someone_acted_on_the_server(): void
    {
        Event::fake([Crashed::class]);
        $server = $this->createServerModel();

        (new CheckServerCrash($server, now()->getTimestamp()))->handle();
        Event::assertDispatchedTimes(Crashed::class, 1);

        Activity::event('server:console.command')->subject($server)->property('command', 'stop')->log();
        (new CheckServerCrash($server, now()->getTimestamp()))->handle();
        Event::assertDispatchedTimes(Crashed::class, 1);
    }

    public function test_a_crash_notifies_the_owner_in_the_panel_and_by_mail(): void
    {
        Notification::fake();
        $server = $this->createServerModel();

        event(new Crashed($server));

        Notification::assertSentTo($server->user, ServerCrashed::class);
        Notification::assertSentTo($server->user, DatabaseNotification::class);
    }
}
