<x-filament-panels::page class="fi-status-page">
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
                                <td class="py-2 pe-4">{{ $row['players'] === null ? '—' : format_number($row['players'], maxPrecision: 1) }}</td>
                                <td class="py-2 pe-4">{{ $row['peak'] ?? '—' }}</td>
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
    @endif
</x-filament-panels::page>
