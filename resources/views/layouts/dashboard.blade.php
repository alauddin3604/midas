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
        <div class="flex min-h-svh flex-col md:flex-row">
            <aside class="w-full shrink-0 border-b border-border p-4 md:w-64 md:border-r md:border-b-0">
                <a class="mb-6 block text-lg font-semibold" href="{{ route('dashboard') }}" wire:navigate>
                    {{ config('app.name') }}
                </a>
                <ul class="uk-nav uk-nav-default">
                    <li @class(['uk-active' => request()->routeIs('dashboard')])>
                        <a href="{{ route('dashboard') }}" wire:navigate>Dashboard</a>
                    </li>
                    @can(\Domain\Identity\Enums\Permission::ManageUsers)
                        <li @class(['uk-active' => request()->routeIs('users.*')])>
                            <a href="{{ route('users.index') }}" wire:navigate>Users</a>
                        </li>
                    @endcan
                    @can(\Domain\Identity\Enums\Permission::ViewAuditLog)
                        <li @class(['uk-active' => request()->routeIs('audit.*')])>
                            <a href="{{ route('audit.index') }}" wire:navigate>Audit log</a>
                        </li>
                    @endcan
                </ul>
            </aside>
            <div class="flex min-w-0 flex-1 flex-col">
                <header class="flex items-center justify-end gap-4 border-b border-border px-6 py-3">
                    <span class="text-sm text-muted-foreground">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="uk-btn uk-btn-default uk-btn-sm" type="submit">Log out</button>
                    </form>
                </header>
                <main class="flex-1 p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
