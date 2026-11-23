@php($languages = app(\App\Services\Helpers\LanguageService::class)->getAvailableLanguages())
@if(count($languages) > 1)
    <div @class(['sb-language', 'mt-6 flex justify-center' => $simple ?? false])>
        {{-- Long language lists scroll inside the menu; on the login page it opens upwards, where there is room. --}}
        <x-filament::dropdown :placement="($simple ?? false) ? 'top' : 'bottom-end'" max-height="min(22rem, 60vh)">
            <x-slot name="trigger">
                <x-filament::button color="gray" outlined size="sm" :icon="\App\Enums\TablerIcon::Language" :label-sr-only="!($simple ?? false)">
                    {{ $languages[app()->getLocale()] ?? strtoupper(app()->getLocale()) }}
                </x-filament::button>
            </x-slot>
            <x-filament::dropdown.list>
                @foreach($languages as $code => $name)
                    <x-filament::dropdown.list.item tag="a" :href="route('language.switch', $code)" :color="$code === app()->getLocale() ? 'primary' : 'gray'">
                        {{ $name }}
                    </x-filament::dropdown.list.item>
                @endforeach
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    </div>
@endif
