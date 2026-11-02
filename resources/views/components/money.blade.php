@props(['amount', 'currency'])
{{-- 29.99 USD. Prices can be in any currency, so the code is always shown. --}}
<span {{ $attributes->class(['tabular-nums whitespace-nowrap']) }}>{{ number_format((float) $amount, 2) }} <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $currency }}</span></span>
