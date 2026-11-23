<?php

namespace App\Http\Controllers\Api\Client\Servers;

use App\Enums\QueryProtocol;
use App\Http\Controllers\Api\Client\ClientApiController;
use App\Http\Requests\Api\Client\Servers\GetServerRequest;
use App\Models\Server;
use App\Services\Servers\ServerStatsService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Cache;

#[Group('Server', weight: 3)]
class ServerStatsController extends ClientApiController
{
    /**
     * View status averages
     *
     * Average CPU, memory and player counts for today, the last 7 and 30 days and all time,
     * hourly averages for the last 24 hours, and who is online now (Shoebill).
     *
     * @return array<string, mixed>
     */
    public function __invoke(GetServerRequest $request, Server $server, ServerStatsService $stats): array
    {
        $protocol = QueryProtocol::forEgg($server->egg);

        return [
            'object' => 'server_stats',
            'attributes' => [
                'protocol' => $protocol->value,
                'summary' => $stats->summary($server),
                'last_day' => $stats->lastDay($server),
                'online' => $protocol === QueryProtocol::None ? null : Cache::get("servers.$server->uuid.players"),
                'limits' => ['cpu' => $server->cpu, 'memory_bytes' => $server->memory * 1048576],
            ],
        ];
    }
}
