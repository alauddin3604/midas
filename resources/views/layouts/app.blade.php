<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="uk-theme-zinc uk-radii-md uk-shadows-sm uk-font-sm">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="bg-background font-geist-sans text-foreground antialiased">
        {{ $slot }}

        @livewireScripts
    </body>
</html>
