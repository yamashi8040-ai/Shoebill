<?php

namespace App\Tests\Integration\Services\Servers;

use App\Models\ServerStatSample;
use App\Services\Servers\ServerStatsService;
use App\Tests\Integration\IntegrationTestCase;

class ServerStatsPeriodTest extends IntegrationTestCase
{
    public function test_a_period_compares_with_the_same_length_before_it(): void
    {
        $server = $this->createServerModel();
        ServerStatSample::create(['server_id' => $server->id, 'cpu' => 50, 'memory_bytes' => 100, 'players' => 4, 'created_at' => now()->subHours(2)]);
        ServerStatSample::create(['server_id' => $server->id, 'cpu' => 10, 'memory_bytes' => 100, 'players' => 2, 'created_at' => now()->subHours(30)]);
        ServerStatSample::create(['server_id' => $server->id, 'cpu' => 99, 'memory_bytes' => 100, 'players' => 9, 'created_at' => now()->subDays(3)]);

        $day = app(ServerStatsService::class)->forPeriod($server, 'day');

        $this->assertEqualsWithDelta(50.0, $day['current']['cpu'], 0.01);
        $this->assertEqualsWithDelta(10.0, $day['previous']['cpu'], 0.01);
        $this->assertCount(1, $day['series']['labels']);

        $week = app(ServerStatsService::class)->forPeriod($server, 'week');
        $this->assertSame(3, $week['current']['samples']);
        $this->assertNull(app(ServerStatsService::class)->forPeriod($server, 'all')['previous']);
    }

    public function test_an_unknown_period_falls_back_to_the_last_day(): void
    {
        $server = $this->createServerModel();

        $this->assertArrayHasKey('previous', app(ServerStatsService::class)->forPeriod($server, 'bogus'));
    }
}
