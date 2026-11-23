<?php

namespace App\Tests\Integration\Services\Servers;

use App\Facades\Activity;
use App\Models\Node;
use App\Models\ServerStatSample;
use App\Services\Servers\FleetStatsService;
use App\Tests\Integration\IntegrationTestCase;

class FleetStatsServiceTest extends IntegrationTestCase
{
    public function test_summary_counts_running_servers_and_players_from_recent_samples(): void
    {
        $server = $this->createServerModel();
        ServerStatSample::create(['server_id' => $server->id, 'cpu' => 40, 'memory_bytes' => 1, 'players' => 7, 'created_at' => now()->subMinute()]);
        ServerStatSample::create(['server_id' => $server->id, 'cpu' => 20, 'memory_bytes' => 1, 'players' => 3, 'created_at' => now()->subDay()->subMinute()]);

        $summary = app(FleetStatsService::class)->summary();

        $this->assertSame(1, $summary['running']);
        $this->assertSame(7, $summary['players']);
        $this->assertSame(3, $summary['players_before']);
        $this->assertEqualsWithDelta(40.0, $summary['cpu'], 0.01);
    }

    public function test_attention_lists_crashes_and_full_nodes_only(): void
    {
        $server = $this->createServerModel(['memory' => 950]);
        $server->node->update(['memory' => 1000]);
        Activity::event('server:crashed')->subject($server)->log();

        $kinds = app(FleetStatsService::class)->attention()->pluck('kind')->sort()->values()->all();

        $this->assertSame(['crash', 'node_full'], $kinds);
    }

    public function test_attention_is_empty_when_all_is_well(): void
    {
        Node::factory()->create(['memory' => 0]);

        $this->assertTrue(app(FleetStatsService::class)->attention()->isEmpty());
    }
}
