{{-- The full logo: mark + "hydracore" wordmark in Sora, for dark backgrounds. Sizes: sm, md, lg. --}}
@props(['size' => 'md'])
@php
    [$mark, $text] = [
        'sm' => ['size-8', 'text-xl'],
        'md' => ['size-9', 'text-xl'],
        'lg' => ['size-11', 'text-3xl'],
    ][$size];
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-1.5']) }}>
    <x-logo-mark :class="$mark" />
    <span class="font-logo {{ $text }} font-bold tracking-tight text-white">hydra<span class="text-accent-400">core</span></span>
</span>
