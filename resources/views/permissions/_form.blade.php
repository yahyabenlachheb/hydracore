{{-- Shared permission fields for create and edit. Expects $permission. --}}
@php
    $input = 'mt-1.5 block w-full rounded-md border-0 px-3 py-2 text-sm ring-1 ring-inset focus:ring-2 focus:outline-none dark:bg-white/5';
    $ok = 'ring-neutral-300 focus:ring-accent-600 dark:ring-white/10';
    $bad = 'text-rose-900 ring-rose-300 focus:ring-rose-500 dark:bg-rose-500/5 dark:text-rose-300 dark:ring-rose-500/50';
    $locked = $permission->exists && $permission->isSystem();
@endphp

<div class="grid gap-6 p-6 sm:grid-cols-2">
    <div>
        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Name <span class="text-rose-500">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $permission->name) }}" maxlength="100" required autofocus autocomplete="off" placeholder="View offers"
               class="{{ $input }} {{ $errors->has('name') ? $bad : $ok }} placeholder:text-neutral-400">
        @error('name')
            <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @else
            <p class="mt-1.5 text-xs text-neutral-500 dark:text-neutral-400">Shown when picking permissions for a role.</p>
        @enderror
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Slug @unless ($locked)<span class="text-rose-500">*</span>@endunless</label>
        <input type="text" id="slug" name="slug" value="{{ old('slug', $permission->slug) }}" maxlength="150" autocomplete="off" autocapitalize="none" spellcheck="false" placeholder="offers.view"
               @if ($locked) readonly @else required @endif
               class="{{ $input }} font-mono {{ $errors->has('slug') ? $bad : $ok }} placeholder:text-neutral-400 read-only:cursor-not-allowed read-only:bg-neutral-50 read-only:text-neutral-500 dark:read-only:bg-white/5 dark:read-only:text-neutral-400">
        @error('slug')
            <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @else
            <p class="mt-1.5 text-xs text-neutral-500 dark:text-neutral-400">
                @if ($locked)
                    Used by the app, so it can’t be changed.
                @else
                    What the code checks, e.g. <code class="font-mono">@@can('offers.view')</code>. Lowercase, dot-separated.
                @endif
            </p>
        @enderror
    </div>
</div>
