{{-- A country code as a small badge, e.g. <x-country code="US" />. Renders nothing without a code. --}}
@props(['code' => null])
@if ($code)
    <span {{ $attributes->class(['inline-flex h-5 items-center rounded bg-neutral-100 px-1.5 font-mono text-[10px] font-semibold text-neutral-700 ring-1 ring-neutral-500/10 ring-inset dark:bg-white/5 dark:text-neutral-300 dark:ring-white/10']) }}>{{ strtoupper($code) }}</span>
@endif
