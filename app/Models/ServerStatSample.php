<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $server_id
 * @property float $cpu
 * @property int $memory_bytes
 * @property int|null $players
 * @property int|null $max_players
 * @property Carbon $created_at
 */
class ServerStatSample extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['server_id', 'cpu', 'memory_bytes', 'players', 'max_players', 'created_at'];

    protected function casts(): array
    {
        return ['cpu' => 'float', 'memory_bytes' => 'integer', 'players' => 'integer', 'max_players' => 'integer', 'created_at' => 'datetime'];
    }
}
