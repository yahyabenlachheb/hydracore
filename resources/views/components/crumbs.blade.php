@props(['items'])
{{-- Breadcrumbs after Home. $items: label => url, with null for the current page. --}}
<a href="{{ route('home') }}" class="hover:text-neutral-900 dark:hover:text-white">Home</a>
@foreach ($items as $label => $url)
    <svg class="size-4 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    @if ($url)
        <a href="{{ $url }}" class="hover:text-neutral-900 dark:hover:text-white">{{ $label }}</a>
    @else
        <span class="font-medium text-neutral-900 dark:text-white">{{ $label }}</span>
    @endif
@endforeach
