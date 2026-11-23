<?php

namespace App\Tests\Integration\Services\Nodes;

use App\Models\Allocation;
use App\Models\Egg;
use App\Models\Node;
use App\Models\User;
use App\Services\Servers\ServerCreationService;
use App\Tests\Integration\IntegrationTestCase;
use Illuminate\Support\Facades\Http;

class SimulatedWingsTest extends IntegrationTestCase
{
    public function test_a_test_mode_node_answers_without_a_daemon(): void
    {
        Http::preventStrayRequests();
        $node = Node::factory()->create(['simulated' => true, 'memory' => 102400, 'fqdn' => 'no-such-host.invalid']);

        $this->assertSame('simulated', $node->systemInformation()['kernel_version']);
        $this->assertSame(102400 * 1048576, $node->statistics()['memory_total']);
    }

    public function test_servers_can_be_created_on_a_test_mode_node_and_are_installed(): void
    {
        $node = Node::factory()->create(['simulated' => true, 'fqdn' => 'no-such-host.invalid']);
        $allocation = Allocation::factory()->create(['node_id' => $node->id]);
        $egg = Egg::query()->firstOrFail();

        $server = app(ServerCreationService::class)->handle([
            'name' => 'Demo', 'owner_id' => User::factory()->create()->id, 'egg_id' => $egg->id, 'node_id' => $node->id,
            'allocation_id' => $allocation->id, 'memory' => 1024, 'swap' => 0, 'disk' => 1024, 'io' => 500, 'cpu' => 100,
            'image' => 'example:latest', 'startup' => 'run', 'environment' => [], 'start_on_completion' => false,
        ]);

        $this->assertNull($server->refresh()->status);
        $this->assertSame('offline', $server->retrieveResources()['state']);
    }
}
