@props(['items', 'noun', 'action', 'filtered' => false, 'emptyTitle' => null, 'emptyText' => null])
{{--
    A list page: count and filters on top, then the table in the slot, then pagination.
    $items is the paginator. Slots: filters, and empty (a call to action when nothing exists yet).
--}}
<div data-ajax-list class="overflow-hidden rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10">
    <form method="GET" action="{{ $action }}" class="flex flex-col gap-3 border-b border-neutral-200 px-6 py-4 xl:flex-row xl:items-center xl:justify-between dark:border-white/10">
        <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ number_format($items->total()) }} {{ Str::plural($noun, $items->total()) }}</p>
        <div class="flex flex-wrap gap-2">
            {{ $filters }}
            <button type="submit" class="sr-only">Search</button>
            @if ($filtered)
                <a href="{{ $action }}" class="rounded-md px-3 py-2 text-sm font-medium text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-white/10 dark:hover:text-white">Reset</a>
            @endif
        </div>
    </form>

    @if ($items->isEmpty())
        <div class="px-6 py-16 text-center">
            @if ($filtered)
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">No {{ Str::plural($noun) }} match your filters</h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"><a href="{{ $action }}" class="font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">Reset the filters</a></p>
            @else
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $emptyTitle ?? 'No ' . Str::plural($noun) . ' yet' }}</h3>
                @if ($emptyText)
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ $emptyText }}</p>
                @endif
                @isset($empty)
                    <div class="mt-4">{{ $empty }}</div>
                @endisset
            @endif
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-white/10">
                {{ $slot }}
            </table>
        </div>

        @if ($items->hasPages())
            <div class="border-t border-neutral-200 px-6 py-3 dark:border-white/10">{{ $items->links() }}</div>
        @endif
    @endif
</div>
