<footer class="flex flex-col items-center justify-center text-center space-y-2 p-4 text-gray-600 dark:text-gray-400">
    {{ \Filament\Support\Facades\FilamentView::renderHook(\App\Enums\CustomRenderHooks::FooterStart->value) }}

    <span class="font-semibold">&copy; {{ date('Y') }} {{ $appName }}</span>
    <span class="text-xs">
        {!! trans('credits.footer', ['pelican' => '<a class="underline" href="https://github.com/pelican-dev/panel" target="_blank" rel="noopener">Pelican</a>', 'pterodactyl' => '<a class="underline" href="https://github.com/pterodactyl/panel" target="_blank" rel="noopener">Pterodactyl</a>']) !!}
        · <a class="underline" href="{{ route('credits') }}">{{ trans('credits.title') }}</a>
        @if(config('app.source_url')) · <a class="underline" href="{{ config('app.source_url') }}" target="_blank" rel="noopener">{{ trans('credits.source') }}</a>@endif
    </span>

    @if(config('app.debug'))
        <div class="flex space-x-1 text-xs">
            <x-filament::icon
                :icon="'tabler-clock'"
                @class(['w-4 h-4 text-gray-500 dark:text-gray-400'])
            />
            <span>{{ round(microtime(true) - LARAVEL_START, 3) }}s</span>
        </div>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\App\Enums\CustomRenderHooks::FooterEnd->value) }}
</footer>
