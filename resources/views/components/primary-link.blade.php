@props(['href'])
{{-- The main action of a page, with a plus icon. --}}
<a href="{{ $href }}" {{ $attributes->class(['inline-flex items-center gap-2 rounded-md bg-accent-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-accent-500']) }}>
    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
    {{ $slot }}
</a>
