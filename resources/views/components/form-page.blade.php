@props(['action', 'cancel', 'submit', 'method' => 'POST'])
{{-- The card around a create or edit form, with Cancel and Save in its footer. --}}
<form method="POST" action="{{ $action }}" {{ $attributes->class(['rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10']) }}>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    {{ $slot }}

    <div class="flex justify-end gap-3 rounded-b-md border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-white/10 dark:bg-white/5">
        <a href="{{ $cancel }}" class="rounded-md px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-white/10">Cancel</a>
        <button type="submit" class="rounded-md bg-accent-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-accent-500">{{ $submit }}</button>
    </div>
</form>
