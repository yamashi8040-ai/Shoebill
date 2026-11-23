<?php

namespace App\Traits\Filament;

trait HasLimitBadge
{
    public const WARNING_THRESHOLD = 0.7;

    protected static function getBadgeCount(): int
    {
        return 0;
    }

    protected static function getBadgeLimit(): int
    {
        return 0;
    }

    // Shoebill: no count badges in the server menu.
    public static function getNavigationBadge(): ?string
    {
        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $limit = static::getBadgeLimit();
        $count = static::getBadgeCount();

        if ($limit === 0) {
            return null;
        }

        return $count >= $limit ? 'danger' : ($count >= $limit * self::WARNING_THRESHOLD ? 'warning' : 'success');
    }
}
