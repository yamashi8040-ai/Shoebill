<?php

namespace App\Tests\Integration\Services\Nodes;

use App\Exceptions\DisplayException;
use App\Models\Allocation;
use App\Models\Egg;
use App\Models\Node;
use App\Models\User;
use App\Services\Deployment\FindViableNodesService;
use App\Services\Servers\ServerCreationService;
use App\Tests\Integration\IntegrationTestCase;

class NodeEggTagsTest extends IntegrationTestCase
{
    public function test_games_tags_limit_which_eggs_a_node_runs(): void
    {
        $egg = Egg::query()->firstOrFail();
        $egg->update(['tags' => ['minecraft']]);

        $this->assertTrue(Node::factory()->make(['egg_tags' => []])->acceptsEgg($egg));
        $this->assertTrue(Node::factory()->make(['egg_tags' => ['minecraft', 'rust']])->acceptsEgg($egg));
        $this->assertFalse(Node::factory()->make(['egg_tags' => ['rust']])->acceptsEgg($egg));
    }

    public function test_reserved_nodes_only_take_deployments_naming_all_their_tags(): void
    {
        $node = Node::factory()->make(['reserved_tags' => ['vip', 'eu']]);

        $this->assertFalse($node->acceptsDeploymentTags([]));
        $this->assertFalse($node->acceptsDeploymentTags(['vip']));
        $this->assertTrue($node->acceptsDeploymentTags(['vip', 'eu', 'extra']));
    }

    public function test_deployment_skips_nodes_that_refuse_the_egg(): void
    {
        $egg = Egg::query()->firstOrFail();
        $egg->update(['tags' => ['minecraft']]);
        $refusing = Node::factory()->create(['public' => true, 'egg_tags' => ['rust']]);
        $accepting = Node::factory()->create(['public' => true, 'egg_tags' => ['minecraft']]);

        $ids = app(FindViableNodesService::class)->handle(egg: $egg)->pluck('id');

        $this->assertContains($accepting->id, $ids);
        $this->assertNotContains($refusing->id, $ids);
    }

    public function test_creating_a_server_on_a_node_that_refuses_the_egg_fails(): void
    {
        $egg = Egg::query()->firstOrFail();
        $egg->update(['tags' => ['minecraft']]);
        $node = Node::factory()->create(['egg_tags' => ['rust'], 'simulated' => true]);
        $allocation = Allocation::factory()->create(['node_id' => $node->id]);

        $this->expectException(DisplayException::class);
        app(ServerCreationService::class)->handle([
            'name' => 'Nope', 'owner_id' => User::factory()->create()->id, 'egg_id' => $egg->id, 'allocation_id' => $allocation->id,
            'memory' => 1024, 'swap' => 0, 'disk' => 1024, 'io' => 500, 'cpu' => 100, 'environment' => [],
        ]);
    }
}
