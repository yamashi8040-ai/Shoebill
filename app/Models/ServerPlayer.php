<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property Carbon $first_seen_at
 * @property Carbon $last_seen_at
 * @property int $minutes_played
 */
class ServerPlayer extends Model
{
    public $timestamps = false;

    protected $fillable = ['server_id', 'name', 'first_seen_at', 'last_seen_at', 'minutes_played'];

    protected function casts(): array
    {
        return ['first_seen_at' => 'datetime', 'last_seen_at' => 'datetime', 'minutes_played' => 'integer'];
    }
}
