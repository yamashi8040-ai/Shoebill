<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Shoebill's own features, each switchable by the admin: globally, and for server features per node and per server.
 * Add a case when a feature ships; FeatureService::enabled() is the one check the feature makes.
 */
enum ShoebillFeature: string implements HasLabel
{
    case StatusPage = 'status_page';
    case PlayerHistory = 'player_history';
    case CrashAlerts = 'crash_alerts';

    public function getLabel(): string
    {
        return trans('admin/feature.features.' . $this->value . '.label');
    }

    public function getDescription(): string
    {
        return trans('admin/feature.features.' . $this->value . '.description');
    }

    /** Whether nodes and servers can override the global switch (false for panel-wide tools). */
    public function isServerFeature(): bool
    {
        return true;
    }
}
