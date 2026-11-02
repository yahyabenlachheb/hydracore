@props(['status'])
{{-- Coloured badge for any record status. The label says it too, so colour is never the only signal. --}}
@php
    $good = 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20';
    $wait = 'bg-amber-50 text-amber-800 ring-amber-600/25 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20';
    $info = 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/20';
    $bad = 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/20';
    $warn = 'bg-orange-50 text-orange-700 ring-orange-600/20 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/20';
    $off = 'bg-neutral-100 text-neutral-600 ring-neutral-500/20 dark:bg-white/5 dark:text-neutral-300 dark:ring-white/10';
    $colors = [
        'active' => $good, 'paid' => $good, 'succeeded' => $good, 'converted' => $good, 'replied' => $good,
        'pending' => $wait,
        'new' => $info,
        'failed' => $bad, 'rejected' => $bad, 'suspended' => $bad,
        'refunded' => $warn,
    ][$status] ?? $off;
@endphp
<span {{ $attributes->class(['inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium whitespace-nowrap ring-1 ring-inset', $colors]) }}>{{ $slot->isEmpty() ? ucfirst($status) : $slot }}</span>
