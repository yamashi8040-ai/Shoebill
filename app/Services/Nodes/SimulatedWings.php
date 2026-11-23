<?php

namespace App\Services\Nodes;

use App\Models\Node;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;

/**
 * Answers the panel's Wings calls for a node in test mode, so servers, ports and IPs can be set up without a daemon.
 * Nothing actually runs: servers stay offline, file lists are empty and power actions do nothing.
 * shortcut: backups started on a test-mode node never complete; add a fake "backup finished" callback if demos need them.
 */
class SimulatedWings
{
    /** Guzzle middleware that answers every request itself instead of passing it on to the network. */
    public static function middleware(Node $node): callable
    {
        return fn (callable $next) => function (RequestInterface $request, array $options) use ($node) {
            $response = self::respond($node, $request);
            // Laravel reads the effective URI from transfer stats, so report them like a real transfer would.
            if (isset($options['on_stats'])) {
                ($options['on_stats'])(new TransferStats($request, $response, 0.0));
            }

            return Create::promiseFor($response);
        };
    }

    public static function respond(Node $node, RequestInterface $request): Response
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        $body = match (true) {
            $path === '/api/system' => [
                'architecture' => 'amd64',
                'cpu_count' => max(1, intdiv($node->cpu ?: 6400, 100)),
                'kernel_version' => 'simulated',
                'os' => 'linux',
                'version' => config('panel.wings.min_version'),
            ],
            $path === '/api/system/utilization' => [
                'memory_total' => $node->memory * 1048576,
                'memory_used' => 0,
                'swap_total' => 0,
                'swap_used' => 0,
                'load_average1' => 0.0,
                'load_average5' => 0.0,
                'load_average15' => 0.0,
                'cpu_percent' => 0.0,
                'disk_total' => $node->disk * 1048576,
                'disk_used' => 0,
            ],
            $path === '/api/system/ips' => ['ip_addresses' => array_values(array_filter([is_ip($node->fqdn) ? $node->fqdn : null, '127.0.0.1']))],
            $method === 'GET' && (bool) preg_match('#^/api/servers/[^/]+$#', $path) => [
                'state' => 'offline',
                'is_suspended' => false,
                'utilization' => ['memory_bytes' => 0, 'memory_limit_bytes' => 0, 'cpu_absolute' => 0, 'network' => ['rx_bytes' => 0, 'tx_bytes' => 0], 'uptime' => 0, 'state' => 'offline', 'disk_bytes' => 0],
            ],
            str_ends_with($path, '/files/list-directory'), str_ends_with($path, '/files/search') => [],
            str_ends_with($path, '/files/contents'), str_ends_with($path, '/install-logs') => '',
            default => null,
        };

        $headers = ['User-Agent' => "Pelican Wings/v0.0.0 (id:$node->daemon_token_id)", 'Content-Type' => 'application/json'];

        return $body === null ? new Response(204, $headers) : new Response(200, $headers, is_string($body) ? $body : json_encode($body));
    }
}
