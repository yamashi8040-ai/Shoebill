<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

    /** Announcements this user closed and that were not edited since. @param Builder<self> $query */
    public function scopeNotDismissedBy(Builder $query, ?User $user): void
    {
        if (!$user) {
            return;
        }

        $query->whereNotExists(fn ($q) => $q->from('announcement_dismissals')
            ->whereColumn('announcement_dismissals.announcement_id', 'announcements.id')
            ->where('announcement_dismissals.user_id', $user->id)
            ->whereColumn('announcement_dismissals.dismissed_at', '>=', 'announcements.updated_at'));
    }

    public function dismissFor(User $user): void
    {
        DB::table('announcement_dismissals')->upsert(
            [['announcement_id' => $this->id, 'user_id' => $user->id, 'dismissed_at' => now()]],
            ['announcement_id', 'user_id'],
            ['dismissed_at'],
        );
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }
}
