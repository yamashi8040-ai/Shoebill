<?php

namespace App\Http\Controllers\Api\Remote\Servers;

use App\Enums\ContainerStatus;
use App\Exceptions\Http\HttpForbiddenException;
use App\Http\Controllers\Controller;
use App\Jobs\Server\CheckServerCrash;
use App\Models\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerContainersController extends Controller
{
    /**
     * Updates the server container's status on the Panel
     */
    public function status(Request $request, Server $server): JsonResponse
    {
        throw_unless($server->node->is($request->attributes->get('node')), new HttpForbiddenException('Requesting node does not have permission to access this server.'));

        $status = ContainerStatus::tryFrom($request->json('data.new_state')) ?? ContainerStatus::Missing;

        $previous = cache()->get("servers.$server->uuid.status");
        cache()->put("servers.$server->uuid.status", $status, now()->addHour());

        // Shoebill crash alerts: a deliberate stop goes running -> stopping -> offline, an exited process skips "stopping".
        if ($previous === ContainerStatus::Running && $status === ContainerStatus::Offline) {
            CheckServerCrash::dispatch($server, now()->getTimestamp())->delay(CheckServerCrash::DELAY_SECONDS);
        }

        return new JsonResponse([]);
    }
}
