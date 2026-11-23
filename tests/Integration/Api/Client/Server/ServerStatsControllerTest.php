<?php

namespace App\Tests\Integration\Api\Client\Server;

use App\Models\ServerStatSample;
use App\Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class ServerStatsControllerTest extends ClientApiIntegrationTestCase
{
    public function test_owner_gets_stats_and_strangers_do_not(): void
    {
        [$user, $server] = $this->generateTestAccount();
        ServerStatSample::create(['server_id' => $server->id, 'cpu' => 12, 'memory_bytes' => 1048576, 'created_at' => now()]);

        $this->actingAs($user)->getJson("/api/client/servers/$server->uuid_short/stats")
            ->assertOk()
            ->assertJsonPath('object', 'server_stats')
            ->assertJsonPath('attributes.summary.today.samples', 1)
            ->assertJsonPath('attributes.summary.today.cpu', 12);

        [$stranger] = $this->generateTestAccount();
        $this->actingAs($stranger)->getJson("/api/client/servers/$server->uuid_short/stats")->assertNotFound();
    }
}
