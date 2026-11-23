<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('credits.title') }} · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ config('app.favicon', '/shoebill.svg') }}">
    <style>
        body { font: 15px/1.6 system-ui, sans-serif; max-width: 46rem; margin: 3rem auto; padding: 0 1.25rem; color: #1f2933; background: #f7f8fa; }
        @media (prefers-color-scheme: dark) { body { color: #e4e7eb; background: #111418; } a { color: #8ab4f8; } }
        h1 { font-size: 1.6rem; } table { border-collapse: collapse; width: 100%; } td, th { text-align: left; padding: .5rem .5rem .5rem 0; border-bottom: 1px solid #8884; vertical-align: top; }
        pre { white-space: pre-wrap; font-size: 12px; opacity: .8; }
    </style>
</head>
<body>
    <h1><img src="/shoebill.svg" alt="" width="32" height="32" style="vertical-align: middle"> {{ config('app.name') }}</h1>
    <p>{{ trans('credits.intro') }}</p>
    <table>
        <tr><th>{{ trans('credits.part') }}</th><th>{{ trans('credits.project') }}</th><th>{{ trans('credits.license') }}</th></tr>
        <tr><td>{{ trans('credits.backend') }}</td><td><a href="https://github.com/pelican-dev/panel" rel="noopener">Pelican Panel</a> © Pelican contributors</td><td>AGPL-3.0</td></tr>
        <tr><td>{{ trans('credits.classic') }}</td><td><a href="https://github.com/pterodactyl/panel" rel="noopener">Pterodactyl Panel</a> © Dane Everitt and contributors</td><td>MIT</td></tr>
        <tr><td>{{ trans('credits.changes') }}</td><td>Shoebill contributors</td><td>AGPL-3.0</td></tr>
    </table>
    <p>{{ trans('credits.agpl') }}
        @if(config('app.source_url'))
            <a href="{{ config('app.source_url') }}" rel="noopener">{{ trans('credits.source') }}</a>
        @else
            {{ trans('credits.source_missing') }}
        @endif
    </p>
    <p>{{ trans('credits.trademarks') }}</p>
    <h2>{{ trans('credits.mit_notice') }}</h2>
    <pre>{{ file_get_contents(base_path('licenses/PTERODACTYL-MIT.md')) }}</pre>
</body>
</html>
