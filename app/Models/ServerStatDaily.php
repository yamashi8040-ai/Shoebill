<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $server_id
 * @property Carbon $date
 * @property int $samples
 * @property float $cpu_sum
 * @property float $memory_sum
 * @property int $player_samples
 * @property int $players_sum
 * @property int|null $players_peak
 */
class ServerStatDaily extends Model
{
    protected $table = 'server_stat_daily';

    public $timestamps = false;

    protected $fillable = ['server_id', 'date', 'samples', 'cpu_sum', 'memory_sum', 'player_samples', 'players_sum', 'players_peak'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
