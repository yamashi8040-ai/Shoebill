<?php

return [

    'name' => env('APP_NAME', 'Shoebill'),
    'logo' => env('APP_LOGO'),
    // AGPL-3.0 section 13: where users of this panel can get its source code.
    'source_url' => env('SHOEBILL_SOURCE_URL'),
    // Default accent (primary) color as #rrggbb; users can pick their own on their profile.
    'accent' => env('APP_ACCENT'),
    // Animation level for everyone: off, standard or rich. prefers-reduced-motion always turns it off for that viewer.
    'motion' => env('APP_MOTION', 'rich'),
    // Comma-separated language codes users may pick; empty means every folder in lang/.
    'languages' => array_filter(explode(',', (string) env('APP_LANGUAGES', ''))),
    'favicon' => env('APP_FAVICON', '/shoebill.svg'),

    // Shoebill's own release. app.version stays "canary" so Pelican's self-updater never swaps in an upstream Pelican release.
    'shoebill_version' => '1.0.0-alpha.1',

    'version' => 'canary',

    'timezone' => 'UTC',

    'installed' => env('APP_INSTALLED', true),

    'exceptions' => [
        'report_all' => env('APP_REPORT_ALL_EXCEPTIONS', false),
    ],

    'fallback_locale' => 'en',

];
