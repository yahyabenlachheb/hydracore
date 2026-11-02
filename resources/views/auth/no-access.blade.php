<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>No access · {{ config('app.name', 'HydraCore') }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|sora:700" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased">
    @php($firstName = Str::before(auth()->user()->name, ' ') ?: auth()->user()->name)

    <!-- Background (same as the login page) -->
    <div class="fixed inset-0 bg-[radial-gradient(circle_at_center,#43484e_0%,#2d3238_45%,#24282d_100%)]"></div>

    <div class="relative flex min-h-screen flex-col">

        <header class="flex items-center px-6 py-5 sm:px-10">
            <div class="flex items-center gap-2.5">
                <x-logo size="sm" />
            </div>
        </header>

        <main class="flex flex-1 flex-col items-center justify-center px-4 pb-24 text-center">

            <span class="flex size-20 items-center justify-center rounded-md border border-[#30343a] bg-[#1f2126] text-amber-400 shadow-xl shadow-black/30">
                <svg class="size-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </span>

            <h1 class="mt-8 text-3xl font-semibold tracking-tight text-white sm:text-4xl">Hi {{ $firstName }}, your account isn’t ready yet</h1>
            <p class="mt-3 max-w-md text-base text-gray-400">
                Please contact your super admin to finish setting up your access.
            </p>

            <ul class="mt-8 w-full max-w-sm space-y-3 text-left">
                @foreach ([
                    ['missing' => $missing['role'], 'ok' => 'Role assigned', 'ko' => 'Role not assigned', 'hint' => 'Your job in HydraCore.'],
                    ['missing' => $missing['permission'], 'ok' => 'Permissions granted', 'ko' => 'No permissions granted', 'hint' => 'Your role needs at least one permission.'],
                    ['missing' => $missing['brand'], 'ok' => 'Brand assigned', 'ko' => 'Brand not assigned', 'hint' => 'Decides which brands you can work in.'],
                ] as $item)
                    <li class="flex items-center gap-4 rounded border border-[#30343a] bg-[#1f2126] px-4 py-3.5">
                        @if ($item['missing'])
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-red-500/10 text-red-400">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </span>
                        @else
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </span>
                        @endif
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-gray-200">
                                {{ $item['missing'] ? $item['ko'] : $item['ok'] }}
                            </span>
                            <span class="block text-xs text-gray-500">{{ $item['hint'] }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>

            <form method="POST" action="{{ route('logout') }}" class="mt-8 w-full max-w-sm">
                @csrf
                <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded bg-accent-600 py-4 font-bold uppercase tracking-wider text-white transition duration-200 hover:bg-accent-500 active:scale-[0.98]">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    Logout
                </button>
            </form>

        </main>

        <p class="relative pb-6 text-center text-xs text-gray-500">&copy; {{ date('Y') }} HydraCore. All rights reserved.</p>
    </div>
</body>
</html>
