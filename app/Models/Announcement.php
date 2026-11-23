<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * An admin message shown at the top of every panel page while it is active.
 *
 * @property int $id
 * @property string $title
 * @property string|null $body
 * @property string $type info, success, warning or danger
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 */
class Announcement extends Model
{
    public const TYPES = ['info', 'success', 'warning', 'danger'];

    protected $fillable = ['title', 'body', 'type', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }
}
