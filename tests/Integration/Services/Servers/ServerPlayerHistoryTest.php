<?php

namespace App\Tests\Integration\Services\Servers;

use App\Console\Commands\Server\RecordServerStatsCommand;
use App\Models\ServerPlayer;
use App\Tests\Integration\IntegrationTestCase;

class ServerPlayerHistoryTest extends IntegrationTestCase
{
    public function test_players_are_added_once_and_their_minutes_add_up(): void
    {
        $server = $this->createServerModel();
        $command = new RecordServerStatsCommand();

        $command->recordPlayers($server, ['Alex', 'Steve', 'Alex'], now()->subMinute());
        $command->recordPlayers($server, ['Alex'], now());
        $command->recordPlayers($server, [], now());

        $players = ServerPlayer::query()->where('server_id', $server->id)->pluck('minutes_played', 'name');
        $this->assertSame(['Alex' => 2, 'Steve' => 1], $players->sortKeys()->all());
    }
}
