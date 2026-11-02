@props(['action', 'fields' => [], 'confirm' => null, 'danger' => false])
{{-- A button that POSTs a small action, optionally asking first. --}}
<form method="POST" action="{{ $action }}" @if ($confirm) data-confirm="{{ $confirm }}" @endif>
    @csrf
    @foreach ($fields as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
    <button type="submit" @class([
        'rounded-md px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset',
        'bg-white text-rose-600 ring-rose-300 hover:bg-rose-50 dark:bg-white/5 dark:text-rose-400 dark:ring-rose-500/40 dark:hover:bg-rose-500/10' => $danger,
        'bg-white text-neutral-700 ring-neutral-300 hover:bg-neutral-50 dark:bg-white/5 dark:text-neutral-300 dark:ring-white/10 dark:hover:bg-white/10' => ! $danger,
    ])>{{ $slot }}</button>
</form>
