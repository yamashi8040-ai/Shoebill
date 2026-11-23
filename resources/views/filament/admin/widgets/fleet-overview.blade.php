@php
    // Delta against the same window yesterday. Neutral metrics stay grey; a tiny base reads "was N" (dashboard-design rules).
    $delta = function (?float $now, ?float $before, bool $goodWhenUp = false, string $unit = '') {
        if ($now === null || $before === null) return ['text' => trans('admin/fleet.no_baseline'), 'tone' => 'flat'];
        if (abs($before) < 5 && $unit === '') return ['text' => trans('admin/fleet.was', ['value' => format_number($before, maxPrecision: 1)]), 'tone' => 'flat'];
        $change = $before == 0.0 ? 0 : ($now - $before) / $before * 100;
        $tone = abs($change) < 0.5 ? 'flat' : ($goodWhenUp ? ($change > 0 ? 'up' : 'down') : 'flat');
        return ['text' => ($change > 0 ? '▲ ' : ($change < 0 ? '▼ ' : '±')) . format_number(abs($change), maxPrecision: 0) . '% ' . trans('admin/fleet.vs_yesterday'), 'tone' => $tone];
    };
    $memShare = $summary['memory_total'] > 0 ? $summary['memory_allocated'] / $summary['memory_total'] : null;
    $mib = fn (int $mib) => convert_bytes_to_readable($mib * 1048576);
@endphp

<x-filament-widgets::widget class="sb-fleet">
    <div class="sb-fleet-head">
        <h2 class="sb-fleet-title sb-gradient-text">{{ trans('admin/fleet.title') }}</h2>
        <span class="sb-pill" title="{{ $updatedAt }}"><span class="sb-live-dot"></span>{{ trans('admin/fleet.updated', ['time' => $updatedAt->format('H:i')]) }}</span>
    </div>

    {{-- KPI strip: one card divided by hairlines; every number answers "compared to what?" --}}
    <div class="sb-card sb-kpis">
        <div class="sb-kpi">
            <div class="sb-kpi-label">{{ trans('admin/fleet.running') }}</div>
            <div class="sb-kpi-value"><span data-sb-count>{{ $summary['running'] }}</span><span class="sb-kpi-of">/ {{ $summary['servers'] }}</span></div>
            <div class="sb-delta sb-delta-flat">{{ trans('admin/fleet.servers') }}</div>
        </div>
        <div class="sb-kpi">
            <div class="sb-kpi-label">{{ trans('admin/fleet.nodes') }}</div>
            <div class="sb-kpi-value"><span data-sb-count>{{ $summary['nodes'] - $summary['nodes_down'] }}</span><span class="sb-kpi-of">/ {{ $summary['nodes'] }}</span></div>
            @if($summary['nodes_down'] > 0)
                <div class="sb-delta sb-delta-down">{{ trans('admin/fleet.nodes_down', ['count' => $summary['nodes_down']]) }}</div>
            @else
                <div class="sb-delta sb-delta-flat">{{ trans('admin/fleet.online') }}</div>
            @endif
        </div>
        <div class="sb-kpi">
            <div class="sb-kpi-label">{{ trans('admin/fleet.uptime') }}</div>
            <div class="sb-kpi-value" data-sb-count>{{ $summary['uptime'] === null ? '—' : format_number($summary['uptime'] * 100, maxPrecision: 1) . '%' }}</div>
            @if($summary['uptime'] !== null)
                <div class="sb-meter"><span @class(['sb-meter-fill', 'sb-meter-warn' => $summary['uptime'] < 0.9]) style="width: {{ round($summary['uptime'] * 100) }}%"></span></div>
            @endif
        </div>
        <div class="sb-kpi" title="{{ trans('admin/fleet.cpu_help') }}">
            <div class="sb-kpi-label">{{ trans('admin/fleet.cpu') }}</div>
            <div class="sb-kpi-value" data-sb-count>{{ $summary['cpu'] === null ? '—' : format_number($summary['cpu'], maxPrecision: 1) . '%' }}</div>
            @php($d = $delta($summary['cpu'], $summary['cpu_before'], unit: '%'))
            <div class="sb-delta sb-delta-{{ $d['tone'] }}">{{ $d['text'] }}</div>
        </div>
        <div class="sb-kpi" title="{{ $mib($summary['memory_allocated']) }} / {{ $summary['memory_total'] ? $mib($summary['memory_total']) : '∞' }}">
            <div class="sb-kpi-label">{{ trans('admin/fleet.memory') }}</div>
            <div class="sb-kpi-value" data-sb-count>{{ $memShare === null ? '—' : format_number($memShare * 100, maxPrecision: 0) . '%' }}</div>
            @if($memShare !== null)
                <div class="sb-meter" role="meter" aria-valuenow="{{ round($memShare * 100) }}" aria-valuemin="0" aria-valuemax="100">
                    <span @class(['sb-meter-fill', 'sb-meter-warn' => $memShare >= \App\Services\Servers\FleetStatsService::NODE_FULL]) style="width: {{ min(100, round($memShare * 100)) }}%"></span>
                </div>
            @else
                <div class="sb-delta sb-delta-flat">{{ trans('admin/fleet.unlimited') }}</div>
            @endif
        </div>
        <div class="sb-kpi">
            <div class="sb-kpi-label">{{ trans('admin/fleet.players') }}</div>
            <div class="sb-kpi-value" data-sb-count>{{ $summary['players'] ?? '—' }}</div>
            @php($d = $delta($summary['players'], $summary['players_before'], goodWhenUp: true))
            <div class="sb-delta sb-delta-{{ $d['tone'] }}">{{ $d['text'] }}</div>
        </div>
    </div>

    <div class="sb-fleet-grid">
        {{-- Attention: problems only, each with its action; calm when there is nothing. --}}
        <section class="sb-card sb-panel">
            <h3 class="sb-panel-title">{{ trans('admin/fleet.attention') }} @if($attention->isNotEmpty())<span class="sb-count sb-count-warn">{{ $attention->count() }}</span>@endif</h3>
            @forelse($attention as $item)
                <a href="{{ $item['url'] }}" class="sb-row">
                    <span @class(['sb-chip', 'sb-chip-down' => in_array($item['kind'], ['crash', 'node_down', 'install_failed']), 'sb-chip-warn' => $item['kind'] === 'node_full'])>{{ trans('admin/fleet.kinds.' . $item['kind']) }}</span>
                    <span class="sb-row-name">{{ $item['title'] }}</span>
                    <span class="sb-row-value">{{ $item['detail'] }}</span>
                </a>
            @empty
                <div class="sb-empty"><span class="sb-live-dot"></span>{{ trans('admin/fleet.all_good') }}</div>
            @endforelse
        </section>

        {{-- Node capacity: ranked, slim share bar along each row's bottom edge. --}}
        <section class="sb-card sb-panel">
            <h3 class="sb-panel-title">{{ trans('admin/fleet.capacity') }}</h3>
            @forelse($nodes as $node)
                <a href="/admin/nodes/{{ $node['id'] }}/edit" class="sb-row sb-row-bar">
                    <span class="sb-row-name">{{ $node['name'] }} @if($node['location'])<span class="sb-muted">· {{ $node['location'] }}</span>@endif</span>
                    <span class="sb-row-value" title="{{ $mib($node['allocated']) }} / {{ $node['total'] ? $mib($node['total']) : '∞' }}">{{ $node['down'] ? trans('admin/fleet.kinds.node_down') : ($node['share'] === null ? '∞' : format_number($node['share'] * 100, maxPrecision: 0) . '%') }}</span>
                    @if($node['share'] !== null)
                        <span class="sb-row-track"><span @class(['sb-row-fill', 'sb-meter-warn' => $node['share'] >= \App\Services\Servers\FleetStatsService::NODE_FULL]) style="width: {{ min(100, round($node['share'] * 100)) }}%"></span></span>
                    @endif
                </a>
            @empty
                <div class="sb-empty">{{ trans('admin/fleet.no_nodes') }}</div>
            @endforelse
        </section>

        {{-- Busiest servers today by average CPU. --}}
        <section class="sb-card sb-panel">
            <h3 class="sb-panel-title">{{ trans('admin/fleet.busiest') }}</h3>
            @php($top = max(1, $busiest->max('cpu') ?? 1))
            @forelse($busiest as $server)
                <a href="{{ $server['url'] }}" class="sb-row sb-row-bar">
                    <span class="sb-row-name">{{ $server['name'] }}</span>
                    <span class="sb-row-value">{{ format_number($server['cpu'], maxPrecision: 1) }}%@if($server['players'] !== null)<span class="sb-muted"> · {{ trans('admin/fleet.players_short', ['count' => format_number($server['players'], maxPrecision: 1)]) }}</span>@endif</span>
                    <span class="sb-row-track"><span class="sb-row-fill" style="width: {{ round($server['cpu'] / $top * 100) }}%"></span></span>
                </a>
            @empty
                <div class="sb-empty">{{ trans('admin/fleet.no_samples') }}</div>
            @endforelse
        </section>
    </div>
</x-filament-widgets::widget>
