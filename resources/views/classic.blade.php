<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ config('app.name') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <link rel="icon" href="{{ config('app.favicon', '/shoebill.svg') }}">
    <script>
        window.PterodactylUser = {!! json_encode($user, JSON_HEX_TAG) !!};
        window.SiteConfiguration = {!! json_encode($site, JSON_HEX_TAG) !!};
    </script>
</head>
<body class="bg-neutral-800">
    <div id="modal-portal"></div>
    <div id="app"></div>
    <script src="{{ $bundle['src'] }}" integrity="{{ $bundle['integrity'] ?? '' }}" crossorigin="anonymous"></script>
</body>
</html>
