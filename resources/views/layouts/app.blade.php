@php
    // Sidebar navigation. Each item points at the resource's index route;
    // links fall back to "#" until the route is registered.
    $navigation = [
        'Overview' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
        ],
        'Sales' => [
            ['label' => 'Orders', 'route' => 'orders', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
            ['label' => 'Payments', 'route' => 'payments', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
            ['label' => 'Subscriptions', 'route' => 'subscriptions', 'icon' => 'M6 20.25h12m-7.5-3v3m3-3v3m-10.125-3h17.25c.621 0 1.125-.504 1.125-1.125V4.875c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125Z'],
            ['label' => 'Trials', 'route' => 'trials', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ],
        'Audience' => [
            ['label' => 'Customers', 'route' => 'customers', 'icon' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
            ['label' => 'Contact Messages', 'route' => 'contacts', 'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75'],
        ],
        'Catalog' => [
            ['label' => 'Plans', 'route' => 'plans', 'icon' => 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3ZM6 6h.008v.008H6V6Z'],
            ['label' => 'Packs', 'route' => 'packs', 'icon' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'],
        ],
        'Administration' => [
            ['label' => 'Brands', 'route' => 'brands', 'icon' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z'],
            ['label' => 'Users', 'route' => 'users', 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
            ['label' => 'Roles', 'route' => 'roles', 'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
            ['label' => 'Permissions', 'route' => 'permissions', 'icon' => 'M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z'],
            ['label' => 'Audit Logs', 'route' => 'audit-logs', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z'],
        ],
    ];

    // Only what the user may open: an item needs its "<route>.view" permission (the dashboard is for
    // everyone, and its sections check their own). Groups left empty are dropped with their heading.
    $navigation = array_filter(array_map(
        fn (array $items) => array_values(array_filter($items, fn (array $item) => $item['route'] === 'dashboard' || Gate::allows($item['route'] . '.view'))),
        $navigation,
    ));

    $navUrl = function (string $route): string {
        foreach ([$route . '.index', $route] as $name) {
            if (Route::has($name)) {
                return route($name);
            }
        }

        return '#';
    };

    $navActive = fn (string $route): bool => request()->routeIs($route, $route . '.*');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-neutral-50 dark:bg-neutral-950 dark:scheme-dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title') · @endif{{ config('app.name', 'HydraCore') }}</title>

    {{-- Apply the saved theme before paint to avoid a light flash; falls back to the OS preference. --}}
    <script>
        (() => {
            let theme = null;
            try { theme = localStorage.getItem('theme'); } catch (e) {}
            const dark = theme ? theme === 'dark' : matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|sora:700" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full font-sans text-neutral-900 dark:text-neutral-100 antialiased">
    {{-- Mobile backdrop --}}
    <div data-sidebar-backdrop class="fixed inset-0 z-40 hidden bg-neutral-900/50 backdrop-blur-sm lg:hidden"></div>

    {{-- Sidebar --}}
    <aside data-sidebar
           class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-neutral-900 transition-transform dark:border-r dark:border-white/10 duration-200 ease-out lg:translate-x-0">
        <div class="flex h-16 shrink-0 items-center justify-between gap-3 px-6">
            <a href="{{ $navUrl('dashboard') }}" class="flex items-center gap-3">
                <x-logo />
            </a>
            <button type="button" data-sidebar-close class="rounded-md p-1.5 text-neutral-400 hover:bg-white/10 hover:text-white lg:hidden">
                <span class="sr-only">Close sidebar</span>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Brand switcher --}}
        <div class="relative px-4 pt-2 pb-3" data-brand-switcher>
            @if ($currentBrand)
                <button type="button" data-brand-switcher-toggle aria-haspopup="listbox" aria-expanded="false"
                        class="flex w-full items-center gap-3 rounded-md bg-white/5 p-2 text-left ring-1 ring-white/10 transition-colors hover:bg-white/10">
                    <x-brand-avatar :brand="$currentBrand" class="size-9 rounded-md text-xs" />
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-white">{{ $currentBrand->name }}</span>
                        <span class="block truncate text-xs text-neutral-400">{{ parse_url($currentBrand->website ?? '', PHP_URL_HOST) ?: 'Current brand' }}</span>
                    </span>
                    <svg class="size-4 shrink-0 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                    </svg>
                </button>

                <div data-brand-switcher-menu
                     class="absolute inset-x-4 top-full z-10 mt-2 hidden overflow-hidden rounded-md bg-neutral-800 shadow-xl ring-1 ring-white/10">
                    <p class="px-3 pt-3 pb-1 text-[11px] font-semibold tracking-wider text-neutral-500 uppercase">Switch brand</p>
                    @if ($brands->count() > 5)
                        <div class="px-2 pb-1">
                            <input type="search" data-brand-switcher-search placeholder="Find a brand…"
                                   class="w-full rounded-md border-0 bg-white/5 px-2.5 py-1.5 text-sm text-white ring-1 ring-white/10 ring-inset placeholder:text-neutral-500 focus:ring-2 focus:ring-accent-500 focus:outline-none">
                        </div>
                    @endif
                    <ul class="scrollbar-sidebar max-h-72 overflow-y-auto p-1" role="listbox">
                        @foreach ($brands as $brand)
                            @php($selected = $brand->is($currentBrand))
                            <li data-brand-name="{{ Str::lower($brand->name) }}" role="option" aria-selected="{{ $selected ? 'true' : 'false' }}">
                                <form method="POST" action="{{ route('brands.switch', $brand) }}">
                                    @csrf
                                    <button type="submit" @disabled($selected)
                                            @class([
                                                'flex w-full items-center gap-3 rounded-md px-2 py-1.5 text-left text-sm',
                                                'bg-white/5 text-white' => $selected,
                                                'text-neutral-300 hover:bg-white/5 hover:text-white' => ! $selected,
                                            ])>
                                        <x-brand-avatar :brand="$brand" class="size-7 rounded-md text-[11px]" />
                                        <span class="min-w-0 flex-1 truncate">{{ $brand->name }}</span>
                                        @if ($selected)
                                            <svg class="size-4 shrink-0 text-accent-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                        @endif
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                    @can('brands.view')
                    <a href="{{ $navUrl('brands') }}" class="flex items-center gap-2 border-t border-white/10 px-3 py-2.5 text-sm text-neutral-400 hover:bg-white/5 hover:text-white">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Manage brands
                    </a>
                    @endcan
                </div>
            @else
                @can('brands.create')
                <a href="{{ route('brands.create') }}"
                   class="flex items-center gap-3 rounded-md border border-dashed border-white/15 p-2 text-sm text-neutral-400 hover:border-white/30 hover:text-white">
                    <span class="flex size-9 items-center justify-center rounded-md bg-white/5">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </span>
                    No brands yet — add one
                </a>
                @endcan
            @endif
        </div>

        <nav class="scrollbar-sidebar flex-1 overflow-y-auto pr-2 pl-4 pb-6">
            @foreach ($navigation as $group => $items)
                <div class="mt-6 first:mt-2">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">{{ $group }}</p>
                    <ul class="mt-2 space-y-0.5">
                        @foreach ($items as $item)
                            @php($active = $navActive($item['route']))
                            <li>
                                <a href="{{ $navUrl($item['route']) }}"
                                   @class([
                                       'group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                       'bg-white/10 text-white' => $active,
                                       'text-neutral-400 hover:bg-white/5 hover:text-white' => ! $active,
                                   ])>
                                    <svg @class(['size-5 shrink-0', 'text-accent-400' => $active, 'text-neutral-500 dark:text-neutral-400 group-hover:text-neutral-300' => ! $active])
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                    </svg>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>
    </aside>

    <div class="lg:pl-72">
        {{-- Top bar --}}
        <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-neutral-200 dark:border-white/10 bg-white/80 dark:bg-neutral-950/80 px-4 backdrop-blur sm:px-6 lg:px-8">
            <button type="button" data-sidebar-open class="-ml-1 rounded-md p-1.5 text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-white/10 hover:text-neutral-900 dark:hover:text-white lg:hidden">
                <span class="sr-only">Open sidebar</span>
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <div class="flex flex-1 items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400">
                @hasSection('breadcrumbs')
                    @yield('breadcrumbs')
                @else
                    <span class="font-medium text-neutral-900 dark:text-white">@yield('title', 'Dashboard')</span>
                @endif
            </div>

            @stack('topbar')

            <button type="button" data-theme-toggle
                    class="rounded-md p-2 text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-white/10 dark:hover:text-white"
                    title="Toggle dark mode">
                <span class="sr-only">Toggle dark mode</span>
                {{-- Moon (shown in light mode) --}}
                <svg class="size-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>
                {{-- Sun (shown in dark mode) --}}
                <svg class="hidden size-5 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
            </button>

            {{-- Profile menu --}}
            @auth
                <div class="relative" data-profile-menu>
                    <button type="button" data-profile-menu-toggle aria-haspopup="menu" aria-expanded="false"
                            class="flex items-center gap-2 rounded-full py-0.5 pr-2 pl-0.5 hover:bg-neutral-100 dark:hover:bg-white/10">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-accent-600 text-sm font-semibold text-white">
                            {{ Str::upper(Str::substr(auth()->user()->name ?? auth()->user()->email, 0, 1)) }}
                        </span>
                        <span class="hidden max-w-40 truncate text-sm font-medium text-neutral-700 sm:block dark:text-neutral-200">{{ auth()->user()->name }}</span>
                        <svg class="hidden size-4 text-neutral-400 sm:block" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>

                    <div data-profile-menu-panel role="menu"
                         class="absolute top-full right-0 z-10 mt-2 hidden w-64 overflow-hidden rounded-md bg-white shadow-lg ring-1 ring-neutral-200 dark:bg-neutral-800 dark:ring-white/10">
                        <div class="flex items-center gap-3 border-b border-neutral-200 px-4 py-3 dark:border-white/10">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-accent-600 text-sm font-semibold text-white">
                                {{ Str::upper(Str::substr(auth()->user()->name ?? auth()->user()->email, 0, 1)) }}
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-neutral-900 dark:text-white">{{ auth()->user()->name }}</span>
                                <span class="block truncate text-xs text-neutral-500 dark:text-neutral-400">{{ auth()->user()->email }}</span>
                                @if (auth()->user()->isSuperAdmin())
                                    <span class="mt-1 inline-flex rounded bg-accent-50 px-1.5 py-0.5 text-[10px] font-semibold text-accent-700 uppercase dark:bg-accent-500/10 dark:text-accent-400">Super admin</span>
                                @endif
                            </span>
                        </div>
                        <div class="p-1">
                            @can('users.update')
                                <a href="{{ route('users.edit', auth()->user()) }}" role="menuitem" class="flex items-center gap-2.5 rounded px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-100 dark:text-neutral-200 dark:hover:bg-white/5">
                                    <svg class="size-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                    My account
                                </a>
                            @endcan
                            <a href="{{ route('brands.choose') }}" role="menuitem" class="flex items-center gap-2.5 rounded px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-100 dark:text-neutral-200 dark:hover:bg-white/5">
                                <svg class="size-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" /></svg>
                                Switch brand
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" role="menuitem" class="flex w-full items-center gap-2.5 rounded px-3 py-2 text-left text-sm text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endauth
        </header>

        <main class="px-4 py-8 sm:px-6 lg:px-8">
            <div>
                {{-- Page header --}}
                @hasSection('header')
                    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-semibold tracking-tight text-neutral-900 dark:text-white">@yield('header')</h1>
                            @hasSection('subheader')
                                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">@yield('subheader')</p>
                            @endif
                        </div>
                        @hasSection('actions')
                            <div class="flex shrink-0 items-center gap-3">@yield('actions')</div>
                        @endif
                    </div>
                @endif

                {{-- Flash messages --}}
                @foreach (['success' => 'emerald', 'error' => 'rose', 'warning' => 'amber', 'info' => 'sky'] as $type => $color)
                    @if (session($type))
                        <div data-flash @class([
                            'mb-6 flex items-start gap-3 rounded-md border px-4 py-3 text-sm',
                            'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300' => $color === 'emerald',
                            'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300' => $color === 'rose',
                            'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300' => $color === 'amber',
                            'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-300' => $color === 'sky',
                        ])>
                            <p class="flex-1">{{ session($type) }}</p>
                            <button type="button" data-flash-close class="opacity-60 hover:opacity-100">
                                <span class="sr-only">Dismiss</span>
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif
                @endforeach

                {{-- Validation summary --}}
                @if ($errors->any())
                    <div class="mb-6 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                        <p class="font-medium">Please fix the following {{ Str::plural('error', $errors->count()) }}:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        (() => {
            const sidebar = document.querySelector('[data-sidebar]');
            const backdrop = document.querySelector('[data-sidebar-backdrop]');
            const toggle = (open) => {
                sidebar.classList.toggle('-translate-x-full', !open);
                backdrop.classList.toggle('hidden', !open);
            };

            document.querySelector('[data-sidebar-open]')?.addEventListener('click', () => toggle(true));
            document.querySelector('[data-sidebar-close]')?.addEventListener('click', () => toggle(false));
            backdrop.addEventListener('click', () => toggle(false));
            document.addEventListener('keydown', (e) => e.key === 'Escape' && toggle(false));

            const profile = document.querySelector('[data-profile-menu]');
            const profileToggle = profile?.querySelector('[data-profile-menu-toggle]');
            const profilePanel = profile?.querySelector('[data-profile-menu-panel]');
            const setProfile = (open) => {
                if (!profilePanel) return;
                profilePanel.classList.toggle('hidden', !open);
                profileToggle.setAttribute('aria-expanded', open);
            };
            profileToggle?.addEventListener('click', () => setProfile(profilePanel.classList.contains('hidden')));
            document.addEventListener('click', (e) => profile && !profile.contains(e.target) && setProfile(false));
            document.addEventListener('keydown', (e) => e.key === 'Escape' && setProfile(false));

            const switcher = document.querySelector('[data-brand-switcher]');
            const switcherToggle = switcher?.querySelector('[data-brand-switcher-toggle]');
            const switcherMenu = switcher?.querySelector('[data-brand-switcher-menu]');
            const switcherSearch = switcher?.querySelector('[data-brand-switcher-search]');
            const setSwitcher = (open) => {
                if (!switcherMenu) return;
                switcherMenu.classList.toggle('hidden', !open);
                switcherToggle.setAttribute('aria-expanded', open);
                if (open) switcherSearch?.focus();
            };

            switcherToggle?.addEventListener('click', () => setSwitcher(switcherMenu.classList.contains('hidden')));
            document.addEventListener('click', (e) => switcher && !switcher.contains(e.target) && setSwitcher(false));
            document.addEventListener('keydown', (e) => e.key === 'Escape' && setSwitcher(false));
            switcherSearch?.addEventListener('input', () => {
                const term = switcherSearch.value.trim().toLowerCase();
                switcher.querySelectorAll('[data-brand-name]').forEach((li) =>
                    li.classList.toggle('hidden', !li.dataset.brandName.includes(term))
                );
            });

            document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
                const dark = document.documentElement.classList.toggle('dark');
                try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (e) {}
            });

            // <form data-confirm="Are you sure?"> asks before submitting (used by delete buttons).
            document.addEventListener('submit', (e) => {
                const message = e.target.dataset?.confirm;
                if (message && !window.confirm(message)) e.preventDefault();
            });

            document.querySelectorAll('[data-flash-close]').forEach((btn) =>
                btn.addEventListener('click', () => btn.closest('[data-flash]').remove())
            );
        })();

        // List pages: filters and pagination inside [data-ajax-list] load in place. The page is fetched
        // as usual and only that element is swapped, so the URL, Back/Forward and reloads keep working.
        (() => {
            let controller;
            let searchTimer;

            const sameList = (url) => url.origin === location.origin && url.pathname === location.pathname;

            const load = async (href, push = true, scroll = true) => {
                const list = document.querySelector('[data-ajax-list]');
                if (!list) return location.assign(href);

                controller?.abort();
                controller = new AbortController();
                list.setAttribute('aria-busy', 'true');
                list.classList.add('opacity-60', 'transition-opacity');

                try {
                    const response = await fetch(href, { headers: { Accept: 'text/html' }, signal: controller.signal });
                    const finalUrl = new URL(response.url);
                    // Errors or redirects elsewhere (e.g. an expired session) get a normal page load.
                    if (!response.ok || !sameList(finalUrl)) return location.assign(href);

                    const page = new DOMParser().parseFromString(await response.text(), 'text/html');
                    const next = page.querySelector('[data-ajax-list]');
                    if (!next) return location.assign(href);

                    // Keep typing in the search box without losing focus or the cursor position.
                    const active = list.contains(document.activeElement) ? document.activeElement : null;
                    const caret = active?.selectionStart;

                    list.replaceWith(next);
                    document.title = page.title;
                    if (push) history.pushState({ ajaxList: true }, '', finalUrl);

                    if (active?.name) {
                        const field = next.querySelector(`[name="${CSS.escape(active.name)}"]`);
                        field?.focus();
                        try { if (caret != null) field?.setSelectionRange(caret, caret); } catch (e) {}
                    }
                    if (scroll && next.getBoundingClientRect().top < 0) next.scrollIntoView({ behavior: 'smooth' });
                } catch (e) {
                    if (e.name !== 'AbortError') location.assign(href);
                } finally {
                    list.removeAttribute('aria-busy');
                    list.classList.remove('opacity-60');
                }
            };

            document.addEventListener('submit', (e) => {
                const form = e.target;
                if (e.defaultPrevented || form.method.toLowerCase() !== 'get' || !form.closest('[data-ajax-list]')) return;

                const url = new URL(form.action, location.href);
                if (!sameList(url)) return;
                e.preventDefault();
                clearTimeout(searchTimer);
                // Leave empty filters out of the URL.
                url.search = new URLSearchParams([...new FormData(form)].filter(([, value]) => value !== '')).toString();
                load(url.href);
            });

            document.addEventListener('click', (e) => {
                const link = e.target.closest('[data-ajax-list] a[href]');
                if (!link || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || link.target) return;

                const url = new URL(link.href, location.href);
                if (!sameList(url)) return;
                e.preventDefault();
                load(url.href);
            });

            // Search as you type.
            document.addEventListener('input', (e) => {
                if (!e.target.matches('[data-ajax-list] form[method="GET" i] input[type="search"]')) return;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => e.target.form.requestSubmit(), 400);
            });

            if (document.querySelector('[data-ajax-list]')) history.replaceState({ ajaxList: true }, '');
            window.addEventListener('popstate', (e) => e.state?.ajaxList && load(location.href, false));
            // Lets a page refresh its list in place (e.g. live monitoring) without scrolling.
            window.reloadAjaxList = () => load(location.href, false, false);
        })();
    </script>
    @stack('scripts')
</body>
</html>
