<x-filament-panels::page class="fi-status-page">
    @php
        $cur = $stats['current']; $prev = $stats['previous'];
        $fmtMem = fn ($b) => $b === null ? '—' : convert_bytes_to_readable((int) $b);
        $num = fn ($v, $suffix = '') => $v === null ? null : format_number($v, maxPrecision: 1) . $suffix;
        $tiles = array_values(array_filter([
            $hasPlayers ? ['label' => trans('server/status.avg_players'), 'value' => $num($cur['players']) ?? '—', 'base' => $num($prev['players'] ?? null)] : null,
            $hasPlayers ? ['label' => trans('server/status.peak_players'), 'value' => $cur['peak'] ?? '—', 'base' => $prev['peak'] ?? null] : null,
            ['label' => trans('server/status.avg_cpu'), 'value' => $num($cur['cpu'], '%') ?? '—', 'base' => $num($prev['cpu'] ?? null, '%'), 'share' => $cpuLimit && $cur['cpu'] !== null ? $cur['cpu'] / $cpuLimit : null, 'tip' => $cpuLimit ? '/ ' . $cpuLimit . '%' : null],
            ['label' => trans('server/status.uptime'), 'value' => $cur['uptime'] === null ? '—' : format_number($cur['uptime'] * 100, maxPrecision: 1) . '%', 'base' => ($prev['uptime'] ?? null) === null ? null : format_number($prev['uptime'] * 100, maxPrecision: 1) . '%', 'share' => $cur['uptime'], 'good' => true],
            ['label' => trans('server/status.avg_memory'), 'value' => $fmtMem($cur['memory']), 'base' => ($prev['memory'] ?? null) === null ? null : $fmtMem($prev['memory']), 'share' => $memoryLimit && $cur['memory'] !== null ? $cur['memory'] / $memoryLimit : null, 'tip' => $memoryLimit ? '/ ' . convert_bytes_to_readable($memoryLimit) : null],
        ]));
    @endphp

    {{-- Period picker: segmented pills; the tiles and the chart below follow it. --}}
    <div class="sb-status-head">
        <div class="sb-segmented" role="tablist" aria-label="{{ trans('server/status.period') }}">
            @foreach($periods as $key)
                <button type="button" role="tab" aria-selected="{{ $period === $key ? 'true' : 'false' }}" wire:click="$set('period', '{{ $key }}')"
                        @class(['sb-segment', 'sb-segment-active' => $period === $key])>{{ trans("server/status.periods.$key") }}</button>
            @endforeach
        </div>
        <span class="sb-muted sb-status-source">{{ $hasPlayers ? trans('server/status.protocol', ['protocol' => $protocol->getLabel()]) : trans('server/status.no_players') }}</span>
    </div>

    {{-- The chosen period at a glance, each number against the period just before it. --}}
    <div class="sb-card sb-kpis" style="grid-template-columns: repeat({{ count($tiles) }}, minmax(0, 1fr))" wire:key="tiles-{{ $period }}">
        @foreach($tiles as $tile)
            <div class="sb-kpi" @if(!empty($tile['tip'])) title="{{ $tile['tip'] }}" @endif>
                <div class="sb-kpi-label">{{ $tile['label'] }}</div>
                <div class="sb-kpi-value" data-sb-count>{{ $tile['value'] }}</div>
                @if(isset($tile['share']) && $tile['share'] !== null)
                    <div class="sb-meter"><span @class(['sb-meter-fill', 'sb-meter-warn' => empty($tile['good']) ? $tile['share'] >= 0.9 : $tile['share'] < 0.9]) style="width: {{ min(100, round($tile['share'] * 100)) }}%"></span></div>
                @endif
                <div class="sb-delta sb-delta-flat">{{ $prev === null ? trans('server/status.all_time_note') : ($tile['base'] === null ? trans('admin/fleet.no_baseline') : trans('server/status.previous_baseline', ['value' => $tile['base']])) }}</div>
            </div>
        @endforeach
    </div>

    @if($cur['samples'] === 0)
        <p class="text-sm sb-muted">{{ trans('server/status.empty') }}</p>
    @endif

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

        @if($historyEnabled)
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
    @endif
</x-filament-panels::page>
