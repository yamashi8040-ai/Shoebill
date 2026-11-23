<x-filament-panels::page class="fi-status-page">
    @php
        $today = $summary['today']; $week = $summary['week'];
        $fmtMem = fn ($b) => $b === null ? '—' : convert_bytes_to_readable((int) $b);
        $tiles = array_values(array_filter([
            $hasPlayers ? ['label' => trans('server/status.avg_players'), 'value' => $today['players'] === null ? '—' : format_number($today['players'], maxPrecision: 1), 'base' => $week['players'] === null ? null : format_number($week['players'], maxPrecision: 1)] : null,
            $hasPlayers ? ['label' => trans('server/status.peak_players'), 'value' => $today['peak'] ?? '—', 'base' => $week['peak'] ?? null] : null,
            ['label' => trans('server/status.avg_cpu'), 'value' => $today['cpu'] === null ? '—' : format_number($today['cpu'], maxPrecision: 1) . '%', 'base' => $week['cpu'] === null ? null : format_number($week['cpu'], maxPrecision: 1) . '%', 'share' => $cpuLimit && $today['cpu'] !== null ? $today['cpu'] / $cpuLimit : null],
            ['label' => trans('server/status.avg_memory'), 'value' => $fmtMem($today['memory']), 'base' => $week['memory'] === null ? null : $fmtMem($week['memory']), 'share' => $memoryLimit && $today['memory'] !== null ? $today['memory'] / $memoryLimit : null],
        ]));
    @endphp

    {{-- Today at a glance, each number against its 7-day average (dashboard-design: every number answers "compared to what?"). --}}
    <div class="sb-card sb-kpis" style="grid-template-columns: repeat({{ count($tiles) }}, minmax(0, 1fr))">
        @foreach($tiles as $tile)
            <div class="sb-kpi">
                <div class="sb-kpi-label">{{ $tile['label'] }} <span class="sb-muted">· {{ trans('server/status.periods.today') }}</span></div>
                <div class="sb-kpi-value" data-sb-count>{{ $tile['value'] }}</div>
                @if(isset($tile['share']) && $tile['share'] !== null)
                    <div class="sb-meter"><span @class(['sb-meter-fill', 'sb-meter-warn' => $tile['share'] >= 0.9]) style="width: {{ min(100, round($tile['share'] * 100)) }}%"></span></div>
                @endif
                <div class="sb-delta sb-delta-flat">{{ $tile['base'] === null ? trans('admin/fleet.no_baseline') : trans('server/status.week_baseline', ['value' => $tile['base']]) }}</div>
            </div>
        @endforeach
    </div>
    <x-filament::section :heading="trans('server/status.averages')" :description="$hasPlayers ? trans('server/status.protocol', ['protocol' => $protocol->getLabel()]) : trans('server/status.no_players')">
        <div class="overflow-x-auto">
            <table class="w-full text-sm sb-stagger">
                <thead>
                    <tr class="text-left text-gray-500 dark:text-gray-400">
                        <th class="py-2 pe-4">{{ trans('server/status.period') }}</th>
                        @if($hasPlayers)
                            <th class="py-2 pe-4">{{ trans('server/status.avg_players') }}</th>
                            <th class="py-2 pe-4">{{ trans('server/status.peak_players') }}</th>
                        @endif
                        <th class="py-2 pe-4">{{ trans('server/status.avg_cpu') }}</th>
                        <th class="py-2 pe-4">{{ trans('server/status.avg_memory') }}</th>
                    </tr>
                </thead>
                <tbody class="tabular-nums">
                    @foreach($summary as $period => $row)
                        <tr class="border-t border-gray-200 dark:border-white/10">
                            <td class="py-2 pe-4 font-medium">{{ trans("server/status.periods.$period") }}</td>
                            @if($hasPlayers)
                                <td class="py-2 pe-4" data-sb-count>{{ $row['players'] === null ? '—' : format_number($row['players'], maxPrecision: 1) }}</td>
                                <td class="py-2 pe-4" data-sb-count>{{ $row['peak'] ?? '—' }}</td>
                            @endif
                            <td class="py-2 pe-4">
                                @if($row['cpu'] === null) — @else
                                    {{ format_number($row['cpu'], maxPrecision: 1) }}%
                                    @if($cpuLimit)<span class="text-gray-500">/ {{ $cpuLimit }}% ({{ format_number($row['cpu'] / $cpuLimit * 100, maxPrecision: 0) }}%)</span>@endif
                                @endif
                            </td>
                            <td class="py-2 pe-4">
                                @if($row['memory'] === null) — @else
                                    {{ convert_bytes_to_readable((int) $row['memory']) }}
                                    @if($memoryLimit)<span class="text-gray-500">({{ format_number($row['memory'] / $memoryLimit * 100, maxPrecision: 0) }}%)</span>@endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($summary['all']['samples'] === 0)
            <p class="mt-4 text-sm text-gray-500">{{ trans('server/status.empty') }}</p>
        @endif
    </x-filament::section>

    @if($hasPlayers)
        <x-filament::section :heading="trans('server/status.online_now', ['count' => $online['online'] ?? 0, 'max' => $online['max'] ?? 0])">
            @if(!empty($online['players']))
                <ul class="flex flex-wrap gap-2 sb-stagger">
                    @foreach($online['players'] as $name)
                        <li><x-filament::badge>{{ $name }}</x-filament::badge></li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500">{{ trans('server/status.no_names') }}</p>
            @endif
        </x-filament::section>

        <x-filament::section :heading="trans('server/status.history')" :description="trans('server/status.history_help')">
            @if($history->isEmpty())
                <p class="text-sm text-gray-500">{{ trans('server/status.history_empty') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm sb-stagger">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-gray-400">
                                <th class="py-2 pe-4">{{ trans('server/status.player') }}</th>
                                <th class="py-2 pe-4">{{ trans('server/status.last_seen') }}</th>
                                <th class="py-2 pe-4">{{ trans('server/status.first_seen') }}</th>
                                <th class="py-2 pe-4">{{ trans('server/status.play_time') }}</th>
                            </tr>
                        </thead>
                        <tbody class="tabular-nums">
                            @foreach($history as $player)
                                <tr class="border-t border-gray-200 dark:border-white/10">
                                    <td class="py-2 pe-4 font-medium">{{ $player->name }}</td>
                                    <td class="py-2 pe-4" title="{{ $player->last_seen_at }}">{{ $player->last_seen_at->diffForHumans() }}</td>
                                    <td class="py-2 pe-4">{{ $player->first_seen_at->toDateString() }}</td>
                                    <td class="py-2 pe-4">{{ \Carbon\CarbonInterval::minutes($player->minutes_played)->cascade()->forHumans(short: true) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
