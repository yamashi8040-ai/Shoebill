<?php

namespace App\Services\GameQuery;

use App\Enums\QueryProtocol;
use App\Models\Server;
use Throwable;

/**
 * Asks a running game server how many players are online. Game-agnostic: the protocol comes from the egg.
 * shortcut: Source split packets (FE FF FF FF) are not reassembled, so very large player lists come back empty; upgrade when a game needs it.
 */
class GameQueryService
{
    public const TIMEOUT = 2;

    private const RAKNET_MAGIC = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";

    /** @return array{online: int, max: int, players: string[]}|null */
    public function query(Server $server): ?array
    {
        $protocol = QueryProtocol::forEgg($server->egg);
        $allocation = $server->allocation;

        if ($protocol === QueryProtocol::None || !$allocation) {
            return null;
        }

        $host = in_array($allocation->ip, ['0.0.0.0', '::'], true) ? $server->node->fqdn : $allocation->ip;
        $queryPort = (int) ($server->variables->firstWhere('env_variable', 'QUERY_PORT')?->server_value ?? 0);
        $port = $queryPort ?: $allocation->port;

        try {
            return match ($protocol) {
                QueryProtocol::MinecraftJava => $this->minecraftJava($host, $allocation->port),
                QueryProtocol::MinecraftBedrock => $this->minecraftBedrock($host, $allocation->port),
                QueryProtocol::Source => $this->source($host, $port),
            };
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array{online: int, max: int, players: string[]}|null */
    public function minecraftJava(string $host, int $port): ?array
    {
        $socket = $this->open("tcp://$host:$port");
        $handshake = "\x00" . self::varInt(767) . self::varInt(strlen($host)) . $host . pack('n', $port) . self::varInt(1);
        fwrite($socket, self::varInt(strlen($handshake)) . $handshake . "\x01\x00");

        $length = self::readVarInt($socket);
        $data = '';
        while (strlen($data) < $length && !feof($socket)) {
            $chunk = fread($socket, $length - strlen($data));
            if ($chunk === false || $chunk === '') {
                break;
            }
            $data .= $chunk;
        }
        fclose($socket);

        return self::parseMinecraftJava($data);
    }

    /** @return array{online: int, max: int, players: string[]}|null */
    public static function parseMinecraftJava(string $packet): ?array
    {
        $offset = 0;
        self::decodeVarInt($packet, $offset); // packet id
        $jsonLength = self::decodeVarInt($packet, $offset);
        $json = json_decode(substr($packet, $offset, $jsonLength), true);

        if (!is_array($json) || !isset($json['players'])) {
            return null;
        }

        return [
            'online' => (int) ($json['players']['online'] ?? 0),
            'max' => (int) ($json['players']['max'] ?? 0),
            'players' => array_values(array_filter(array_map(fn ($p) => $p['name'] ?? null, $json['players']['sample'] ?? []))),
        ];
    }

    /** @return array{online: int, max: int, players: string[]}|null */
    public function minecraftBedrock(string $host, int $port): ?array
    {
        $socket = $this->open("udp://$host:$port");
        fwrite($socket, "\x01" . pack('J', time()) . self::RAKNET_MAGIC . pack('J', random_int(0, PHP_INT_MAX)));
        $response = (string) fread($socket, 2048);
        fclose($socket);

        return self::parseMinecraftBedrock($response);
    }

    /** @return array{online: int, max: int, players: string[]}|null */
    public static function parseMinecraftBedrock(string $response): ?array
    {
        if (($response[0] ?? '') !== "\x1c" || strlen($response) < 35) {
            return null;
        }

        $parts = explode(';', substr($response, 35));

        return isset($parts[5]) ? ['online' => (int) $parts[4], 'max' => (int) $parts[5], 'players' => []] : null;
    }

    /** @return array{online: int, max: int, players: string[]}|null */
    public function source(string $host, int $port): ?array
    {
        $socket = $this->open("udp://$host:$port");
        $info = self::parseSourceInfo($this->sourceRequest($socket, "\x54Source Engine Query\x00"));
        if ($info) {
            $info['players'] = self::parseSourcePlayers($this->sourceRequest($socket, "\x55", "\xff\xff\xff\xff"));
        }
        fclose($socket);

        return $info;
    }

    /** Sends an A2S request and answers the server's challenge once if it asks for one. */
    private function sourceRequest(mixed $socket, string $request, string $challenge = ''): string
    {
        fwrite($socket, "\xff\xff\xff\xff" . $request . $challenge);
        $response = (string) fread($socket, 1400);

        if (($response[4] ?? '') === "\x41") {
            fwrite($socket, "\xff\xff\xff\xff" . $request . substr($response, 5, 4));
            $response = (string) fread($socket, 1400);
        }

        return $response;
    }

    /** @return array{online: int, max: int, players: string[]}|null */
    public static function parseSourceInfo(string $response): ?array
    {
        if (($response[4] ?? '') !== "\x49") {
            return null;
        }

        $offset = 6; // header + type + protocol byte
        for ($i = 0; $i < 4; $i++) { // name, map, folder, game
            $offset = strpos($response, "\x00", $offset) + 1;
        }
        $offset += 2; // steam app id

        return ['online' => ord($response[$offset]), 'max' => ord($response[$offset + 1]), 'players' => []];
    }

    /** @return string[] */
    public static function parseSourcePlayers(string $response): array
    {
        if (($response[4] ?? '') !== "\x44") {
            return [];
        }

        $names = [];
        $offset = 6;
        for ($i = 0, $count = ord($response[5]); $i < $count && $offset < strlen($response); $i++) {
            $end = strpos($response, "\x00", $offset + 1);
            if ($end === false) {
                break;
            }
            $name = substr($response, $offset + 1, $end - $offset - 1);
            if ($name !== '') {
                $names[] = $name;
            }
            $offset = $end + 9; // null byte + int32 score + float32 duration
        }

        return $names;
    }

    /** @return resource */
    private function open(string $address): mixed
    {
        $socket = stream_socket_client($address, $errno, $error, self::TIMEOUT);
        if (!$socket) {
            throw new \RuntimeException($error);
        }
        stream_set_timeout($socket, self::TIMEOUT);

        return $socket;
    }

    public static function varInt(int $value): string
    {
        $value &= 0xFFFFFFFF;
        $bytes = '';
        do {
            $byte = $value & 0x7F;
            $value >>= 7;
            $bytes .= chr($byte | ($value ? 0x80 : 0));
        } while ($value);

        return $bytes;
    }

    public static function decodeVarInt(string $data, int &$offset): int
    {
        $value = 0;
        for ($shift = 0; $shift < 35; $shift += 7) {
            $byte = ord($data[$offset++] ?? "\x00");
            $value |= ($byte & 0x7F) << $shift;
            if (!($byte & 0x80)) {
                break;
            }
        }

        return $value;
    }

    private static function readVarInt(mixed $socket): int
    {
        $bytes = '';
        do {
            $byte = fread($socket, 1);
            if ($byte === false || $byte === '') {
                return 0;
            }
            $bytes .= $byte;
        } while (ord($byte) & 0x80 && strlen($bytes) < 5);
        $offset = 0;

        return self::decodeVarInt($bytes, $offset);
    }
}
