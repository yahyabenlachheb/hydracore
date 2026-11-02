@props(['value' => '', 'placeholder' => 'Search…'])
<div class="relative">
    <svg class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
    <input type="search" name="q" value="{{ $value }}" placeholder="{{ $placeholder }}"
           class="w-full rounded-md border-0 py-2 pr-3 pl-9 text-sm ring-1 ring-neutral-300 ring-inset placeholder:text-neutral-400 focus:ring-2 focus:ring-accent-600 focus:outline-none sm:w-64 dark:bg-white/5 dark:ring-white/10">
</div>
