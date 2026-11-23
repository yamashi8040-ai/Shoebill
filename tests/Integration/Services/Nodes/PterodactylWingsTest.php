<?php

namespace App\Tests\Integration\Services\Nodes;

use App\Console\Commands\Server\RecordServerStatsCommand;
use App\Enums\WingsType;
use App\Jobs\Server\CheckServerCrash;
use App\Repositories\Daemon\DaemonFileRepository;
use App\Repositories\Daemon\DaemonSystemRepository;
use App\Services\Servers\ServerConfigurationStructureService;
use App\Tests\Integration\IntegrationTestCase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

class PterodactylWingsTest extends IntegrationTestCase
{
    private const SYSTEM = ['architecture' => 'amd64', 'cpu_count' => 4, 'kernel_version' => '6.1', 'os' => 'linux', 'version' => '1.11.13'];

    public function test_responses_without_a_user_agent_are_accepted_only_from_pterodactyl_wings(): void
    {
        Http::fake(['*/api/system' => Http::response(self::SYSTEM)]);
        $server = $this->createServerModel();

        $server->node->update(['daemon_type' => WingsType::Pterodactyl]);
        $this->assertSame('1.11.13', (new DaemonSystemRepository())->setNode($server->node->refresh())->getSystemInformation()['version']);

        $server->node->update(['daemon_type' => WingsType::Pelican]);
        $this->expectException(ConnectionException::class);
        (new DaemonSystemRepository())->setNode($server->node->refresh())->getSystemInformation();
    }

    public function test_file_search_filters_the_folder_listing_on_pterodactyl_wings(): void
    {
        Http::fake(['*/files/list-directory*' => Http::response([['name' => 'server.properties'], ['name' => 'world'], ['name' => 'Server.jar']])]);
        $server = $this->createServerModel();
        $server->node->update(['daemon_type' => WingsType::Pterodactyl]);

        $names = array_column((new DaemonFileRepository())->setServer($server->refresh())->search('server', '/'), 'name');

        $this->assertSame(['server.properties', 'Server.jar'], $names);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/files/search'));
    }

    public function test_the_server_configuration_carries_the_flag_pterodactyl_wings_reads(): void
    {
        $server = $this->createServerModel(['oom_killer' => false]);

        $build = app(ServerConfigurationStructureService::class)->handle($server)['build'];

        $this->assertTrue($build['oom_disabled']);
        $this->assertFalse($build['oom_killer']);
    }

    public function test_a_running_server_found_offline_on_pterodactyl_wings_queues_a_crash_check(): void
    {
        Queue::fake();
        $server = $this->createServerModel();
        $server->node->update(['daemon_type' => WingsType::Pterodactyl]);
        $server->refresh();
        $command = new RecordServerStatsCommand();

        $command->watchForCrash($server, 'running');
        $command->watchForCrash($server, 'offline');
        $command->watchForCrash($server, 'offline');

        Queue::assertPushed(CheckServerCrash::class, 1);
    }

    public function test_the_archive_status_callback_is_accepted(): void
    {
        $server = $this->createServerModel();
        $node = $server->node;

        $this->withHeader('Authorization', "Bearer $node->daemon_token_id." . $node->daemon_token)
            ->postJson("/api/remote/servers/$server->uuid/archive", ['successful' => true])
            ->assertNoContent();
    }
}
