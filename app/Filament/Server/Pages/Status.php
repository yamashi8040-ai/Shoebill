<?php

namespace App\Filament\Server\Pages;

use App\Enums\QueryProtocol;
use App\Enums\SubuserPermission;
use App\Enums\TablerIcon;
use App\Filament\Server\Widgets\ServerStatusChart;
use App\Models\Server;
use App\Models\ServerPlayer;
use App\Services\Servers\ServerStatsService;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;

class Status extends Page
{
    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = TablerIcon::ChartLine;

    protected string $view = 'filament.server.pages.status';

    /** Chosen period, kept in the URL so a reload or a shared link shows the same view. */
    #[Url]
    public string $period = 'day';

    public static function canAccess(): bool
    {
        return parent::canAccess() && user()?->can(SubuserPermission::WebsocketConnect, Filament::getTenant());
    }

    protected function getViewData(): array
    {
        /** @var Server $server */
        $server = Filament::getTenant();
        $protocol = QueryProtocol::forEgg($server->egg);

        $this->period = array_key_exists($this->period, ServerStatsService::PERIODS) ? $this->period : 'day';

        return [
            'stats' => app(ServerStatsService::class)->forPeriod($server, $this->period),
            'periods' => array_keys(ServerStatsService::PERIODS),
            'hasPlayers' => $protocol !== QueryProtocol::None,
            'protocol' => $protocol,
            'online' => Cache::get("servers.$server->uuid.players"),
            'history' => ServerPlayer::query()->where('server_id', $server->id)->latest('last_seen_at')->limit(50)->get(),
            'cpuLimit' => $server->cpu,
            'memoryLimit' => $server->memory * 1048576,
        ];
    }

    public function getWidgetData(): array
    {
        return ['period' => $this->period];
    }

    protected function getFooterWidgets(): array
    {
        return [ServerStatusChart::class];
    }

    public function getTitle(): string
    {
        return trans('server/status.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('server/status.title');
    }
}
