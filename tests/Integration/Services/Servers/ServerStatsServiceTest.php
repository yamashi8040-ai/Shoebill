<?php

namespace App\Tests\Integration\Services\Servers;

use App\Enums\QueryProtocol;
use App\Models\ServerStatDaily;
use App\Models\ServerStatSample;
use App\Services\GameQuery\GameQueryService;
use App\Services\Servers\ServerStatsService;
use App\Tests\Integration\IntegrationTestCase;

class ServerStatsServiceTest extends IntegrationTestCase
{
    public function test_summary_averages_samples_and_daily_rollups(): void
    {
        $this->travelTo(now()->setTime(12, 0));
        $server = $this->createServerModel();

        ServerStatSample::create(['server_id' => $server->id, 'cpu' => 10, 'memory_bytes' => 100, 'players' => 2, 'created_at' => now()->subHour()]);
        ServerStatSample::create(['server_id' => $server->id, 'cpu' => 30, 'memory_bytes' => 300, 'players' => 6, 'created_at' => now()->subMinutes(5)]);
        ServerStatSample::create(['server_id' => $server->id, 'cpu' => 50, 'memory_bytes' => 500, 'players' => null, 'created_at' => now()->subDays(3)]);

        $this->artisan('shoebill:stats:rollup')->assertSuccessful();
        $this->assertSame(1, ServerStatDaily::count(), 'only the finished day is rolled up');

        $summary = app(ServerStatsService::class)->summary($server);

        $this->assertEquals(20, $summary['today']['cpu']);
        $this->assertEquals(4, $summary['today']['players']);
        $this->assertSame(6, $summary['today']['peak']);
        $this->assertEquals(30, $summary['week']['cpu']);
        $this->assertEquals(4, $summary['week']['players'], 'samples without a player count are ignored');
        $this->assertEquals(30, $summary['all']['cpu']);
        $this->assertEquals(300, $summary['all']['memory']);
        $this->assertSame(3, $summary['all']['samples']);
    }

    public function test_protocol_is_detected_from_egg(): void
    {
        $server = $this->createServerModel();
        $egg = $server->egg;

        $egg->forceFill(['name' => 'Paper', 'tags' => ['minecraft'], 'query_protocol' => null]);
        $this->assertSame(QueryProtocol::MinecraftJava, QueryProtocol::forEgg($egg));

        $egg->forceFill(['name' => 'Rust', 'tags' => ['steamcmd'], 'features' => ['steam_disk_space']]);
        $this->assertSame(QueryProtocol::Source, QueryProtocol::forEgg($egg));

        $egg->forceFill(['name' => 'Terraria', 'tags' => [], 'features' => []]);
        $this->assertSame(QueryProtocol::None, QueryProtocol::forEgg($egg));

        $egg->forceFill(['query_protocol' => 'bedrock']);
        $this->assertSame(QueryProtocol::MinecraftBedrock, QueryProtocol::forEgg($egg));
    }

    public function test_query_packets_are_parsed(): void
    {
        $json = json_encode(['players' => ['online' => 3, 'max' => 20, 'sample' => [['name' => 'Steve'], ['name' => 'Alex']]]]);
        $this->assertSame(['online' => 3, 'max' => 20, 'players' => ['Steve', 'Alex']], GameQueryService::parseMinecraftJava("\x00" . GameQueryService::varInt(strlen($json)) . $json));

        $bedrock = "\x1c" . str_repeat("\x00", 34) . 'MCPE;Shoebill;712;1.21;5;10;123;World;Survival;1;19132;19133;';
        $this->assertSame(['online' => 5, 'max' => 10, 'players' => []], GameQueryService::parseMinecraftBedrock($bedrock));

        $info = "\xff\xff\xff\xff\x49\x11Name\x00map\x00folder\x00game\x00\x00\x00\x07\x20\x00";
        $this->assertSame(['online' => 7, 'max' => 32, 'players' => []], GameQueryService::parseSourceInfo($info));

        $players = "\xff\xff\xff\xff\x44\x02\x00Gordon\x00" . pack('l', 5) . pack('g', 1.0) . "\x01Alyx\x00" . pack('l', 9) . pack('g', 2.0);
        $this->assertSame(['Gordon', 'Alyx'], GameQueryService::parseSourcePlayers($players));

        $offset = 0;
        $this->assertSame(300, GameQueryService::decodeVarInt(GameQueryService::varInt(300), $offset));
    }
}
