<?php

namespace App\Services\Helpers;

use Illuminate\Support\Facades\File;
use Locale;

class LanguageService
{
    public const TRANSLATED_COMPLETELY = [
        'en',
    ];

    public function __construct(protected PluginService $pluginService) {}

    public function isLanguageTranslated(string $countryCode = 'en'): bool
    {
        return in_array($countryCode, self::TRANSLATED_COMPLETELY, true);
    }

    public function getLanguageDisplayName(string $code): string
    {
        $key = 'profile.current_language_name';
        $trans = trans($key, locale: $code);

        return $trans !== $key ? $trans : title_case(Locale::getDisplayName($code, $code));
    }

    public function isEnabled(string $code): bool
    {
        $enabled = config('app.languages');

        return !$enabled || $code === 'en' || in_array($code, $enabled, true);
    }

    /** Adds a language folder seeded with the English files, so translators have every key to fill in. */
    public function addLanguage(string $code): void
    {
        File::copyDirectory(base_path('lang/en'), base_path('lang/' . $code));
    }

    /** @return array<array-key, string> */
    public function getAvailableLanguages(string $path = 'lang', bool $includeDisabled = false): array
    {
        $enabled = config('app.languages');

        $baseLanguages = collect(File::directories(base_path($path)))->mapWithKeys(function ($path) {
            $code = basename($path);

            return [$code => $this->getLanguageDisplayName($code)];
        })->toArray();

        $pluginLanguages = collect($this->pluginService->getPluginLanguages())->mapWithKeys(fn ($code) => [$code => $this->getLanguageDisplayName($code)])->toArray();

        $languages = array_merge($baseLanguages, $pluginLanguages);
        if (!$includeDisabled && $enabled) {
            // English always stays: it is the fallback every other language builds on.
            $languages = array_intersect_key($languages, array_flip([...$enabled, 'en']));
        }

        return array_sort(array_unique($languages));
    }
}
