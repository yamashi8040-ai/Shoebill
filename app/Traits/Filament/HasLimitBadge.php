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

    public static function getNavigationBadge(): ?string
    {
        $count = static::getBadgeCount();

        // Shoebill: an empty section shows no badge instead of a "0" or "0 / 3".
        if ($count === 0) {
            return null;
        }

        // Shoebill: just the count; the limit lives on the page, not in the menu.
        return (string) $count;
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
