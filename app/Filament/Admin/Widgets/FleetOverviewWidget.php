<?php

namespace App\Filament\Admin\Widgets;

use App\Services\Servers\FleetStatsService;
use Filament\Widgets\Widget;

/** Admin dashboard: "is the fleet OK right now?" — KPIs, what needs attention, node capacity and the busiest servers. */
class FleetOverviewWidget extends Widget
{
    protected string $view = 'filament.admin.widgets.fleet-overview';

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    protected function getViewData(): array
    {
        $stats = app(FleetStatsService::class);

        return [
            'summary' => $stats->summary(),
            'attention' => $stats->attention(),
            'nodes' => $stats->nodeCapacity(),
            'busiest' => $stats->busiestServers(),
            'updatedAt' => now(),
        ];
    }
}
