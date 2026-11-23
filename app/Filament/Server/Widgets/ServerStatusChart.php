<?php

namespace App\Filament\Server\Widgets;

use App\Models\Server;
use App\Services\Servers\ServerStatsService;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class ServerStatusChart extends ChartWidget
{
    protected ?string $pollingInterval = '60s';

    protected ?string $maxHeight = '260px';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        /** @var Server $server */
        $server = Filament::getTenant();
        $day = app(ServerStatsService::class)->lastDay($server);

        $datasets = [
            ['label' => trans('server/status.cpu') . ' (%)', 'data' => $day['cpu'], 'borderColor' => '#60a5fa', 'tension' => 0.3, 'yAxisID' => 'y'],
            ['label' => trans('server/status.memory') . ' (MiB)', 'data' => $day['memory'], 'borderColor' => '#a78bfa', 'tension' => 0.3, 'yAxisID' => 'y1'],
        ];

        if (array_filter($day['players'], fn ($v) => $v !== null)) {
            $datasets[] = ['label' => trans('server/status.players'), 'data' => $day['players'], 'borderColor' => '#34d399', 'tension' => 0.3, 'yAxisID' => 'y'];
        }

        return ['datasets' => $datasets, 'labels' => $day['labels']];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => ['min' => 0, 'position' => 'left'],
                'y1' => ['min' => 0, 'position' => 'right', 'grid' => ['drawOnChartArea' => false]],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): string
    {
        return trans('server/status.last_24h');
    }
}
