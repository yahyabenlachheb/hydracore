<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Choose a brand · {{ config('app.name', 'HydraCore') }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|sora:700" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased">
    @php
        $user = auth()->user();
        $firstName = Str::before($user->name, ' ') ?: $user->name;
        $createUrl = Route::has('brands.create') ? route('brands.create') : '#';
    @endphp

    <!-- Background (same as the login page) -->
    <div class="fixed inset-0 bg-[radial-gradient(circle_at_center,#43484e_0%,#2d3238_45%,#24282d_100%)]"></div>

    <div class="relative flex min-h-screen flex-col">

        <header class="flex items-center justify-between px-6 py-5 sm:px-10">
            <div class="flex items-center gap-2.5">
                <x-logo size="sm" />
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 rounded px-3 py-2 text-sm text-gray-400 transition-colors hover:bg-white/5 hover:text-white">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    Sign out
                </button>
            </form>
        </header>

        <main class="flex flex-1 flex-col items-center justify-center px-4 pb-24">

            <h1 class="text-center text-3xl font-semibold tracking-tight text-white sm:text-5xl">Welcome back, {{ $firstName }}</h1>
            <p class="mt-3 text-center text-lg text-gray-400">
                {{ $brands->isEmpty() ? 'There are no brands yet — create the first one' : 'Choose a brand to continue' }}
            </p>

            <div class="mt-12 flex max-w-5xl flex-wrap justify-center gap-x-4 gap-y-6 sm:mt-14 sm:gap-x-6 sm:gap-y-8">
                @foreach ($brands as $brand)
                    <form method="POST" action="{{ route('brands.switch', $brand) }}">
                        @csrf
                        <input type="hidden" name="continue" value="1">

                        <button type="submit" class="group flex w-24 flex-col items-center text-center focus:outline-none sm:w-28">
                            <span class="relative">
                                <x-brand-avatar :brand="$brand" variant="solid"
                                                class="size-24 rounded-md text-2xl sm:text-3xl tracking-tight shadow-xl shadow-black/30 ring-3 ring-transparent transition duration-200 group-hover:-translate-y-1 group-hover:ring-accent-500 group-focus-visible:-translate-y-1 group-focus-visible:ring-accent-500 sm:size-28" />

                                @if ($brand->id === $lastBrandId)
                                    <span class="absolute top-1.5 right-1.5 rounded bg-black/45 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-white uppercase backdrop-blur-sm transition duration-200 group-hover:-translate-y-1">
                                        Last used
                                    </span>
                                @endif
                            </span>

                            <span class="mt-3 block w-full truncate text-sm font-medium text-gray-300 transition-colors group-hover:text-white">{{ $brand->name }}</span>
                            <span class="mt-0.5 block w-full truncate text-xs text-gray-500">{{ parse_url($brand->website ?? '', PHP_URL_HOST) ?: ' ' }}</span>
                        </button>
                    </form>
                @endforeach

                @if ($user->isSuperAdmin())
                    <a href="{{ $createUrl }}" class="group flex w-24 flex-col items-center text-center focus:outline-none sm:w-28">
                        <span class="flex size-24 items-center justify-center rounded-md border-2 border-dashed border-white/15 text-gray-500 transition duration-200 group-hover:-translate-y-1 group-hover:border-accent-500 group-hover:text-white group-focus-visible:-translate-y-1 group-focus-visible:border-accent-500 sm:size-28">
                            <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </span>
                        <span class="mt-3 block text-sm font-medium text-gray-400 transition-colors group-hover:text-white">New brand</span>
                        <span class="mt-0.5 block text-xs text-gray-500">Add another brand</span>
                    </a>
                @endif
            </div>


        </main>

        <p class="relative pb-6 text-center text-xs text-gray-500">&copy; {{ date('Y') }} HydraCore. All rights reserved.</p>
    </div>
</body>
</html>
