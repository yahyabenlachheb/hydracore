<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Sign in · {{ config('app.name', 'HydraCore') }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|sora:700" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased">
    <!-- Background -->
    <div class="fixed inset-0 bg-[radial-gradient(circle_at_center,#43484e_0%,#2d3238_45%,#24282d_100%)]"></div>

    <div class="relative flex min-h-screen items-center justify-center px-4">

        <div class="w-full max-w-sm">

            <div class="mb-10 flex items-center justify-center gap-3">
                <x-logo size="lg" />
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf

                <!-- Username -->
                <div>
                    <div @class([
                        'flex items-center rounded bg-[#1f2126] border',
                        'border-red-500/60' => $errors->has('username'),
                        'border-[#30343a]' => ! $errors->has('username'),
                    ])>

                        <div class="px-4 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5.121 17.804A9.969 9.969 0 0112 15c2.5 0 4.786.92 6.879 2.438M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>

                        <input
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Username"
                            autocomplete="username"
                            autofocus
                            required
                            class="w-full pl-2 bg-transparent py-4 pr-4 text-gray-200 placeholder-gray-500 focus:outline-none">

                    </div>

                    @error('username')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <div @class([
                        'flex items-center rounded bg-[#1f2126] border',
                        'border-red-500/60' => $errors->has('password'),
                        'border-[#30343a]' => ! $errors->has('password'),
                    ])>

                        <div class="px-4 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m6-8h1a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2v-8a2 2 0 012-2h1m12 0V7a6 6 0 10-12 0v2m12 0H6"/>
                            </svg>
                        </div>

                        <input
                            type="password"
                            name="password"
                            placeholder="Password"
                            autocomplete="current-password"
                            required
                            class="w-full pl-2 bg-transparent py-4 pr-4 text-gray-200 placeholder-gray-500 focus:outline-none">

                    </div>

                    @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember -->
                <div class="flex items-center">

                    <input
                        type="checkbox"
                        name="remember"
                        id="remember"
                        value="1"
                        @checked(old('remember'))
                        class="rounded border-gray-600 bg-gray-700 text-cyan-500 focus:ring-cyan-500">

                    <label for="remember" class="ml-2 text-sm text-gray-400">
                        Remember me
                    </label>

                </div>

                <!-- Button -->
                <button
                    type="submit"
                    class="w-full rounded bg-accent-600 py-4 font-bold uppercase tracking-wider text-white transition duration-200 hover:bg-accent-500 active:scale-[0.98]">

                    Login

                </button>

            </form>

            <p class="mt-8 text-center text-xs text-gray-500">
                &copy; {{ date('Y') }} HydraCore. All rights reserved.
            </p>

        </div>

    </div>
</body>
</html>
