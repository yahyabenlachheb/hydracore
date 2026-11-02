@props(['brand', 'variant' => 'soft'])

@php
    // Stable colour per brand for the initials fallback. "soft" suits small avatars on dark
    // surfaces; "solid" is the large gradient tile used on the brand picker.
    $palettes = [
        'soft' => [
            'bg-sky-500/15 text-sky-300',
            'bg-emerald-500/15 text-emerald-300',
            'bg-amber-500/15 text-amber-300',
            'bg-rose-500/15 text-rose-300',
            'bg-lime-500/15 text-lime-300',
            'bg-teal-500/15 text-teal-300',
            'bg-orange-500/15 text-orange-300',
            'bg-cyan-500/15 text-cyan-300',
        ],
        'solid' => [
            'bg-linear-to-br from-sky-400 to-sky-700 text-white',
            'bg-linear-to-br from-emerald-400 to-emerald-700 text-white',
            'bg-linear-to-br from-amber-300 to-amber-600 text-amber-950',
            'bg-linear-to-br from-rose-400 to-rose-700 text-white',
            'bg-linear-to-br from-lime-300 to-lime-600 text-lime-950',
            'bg-linear-to-br from-teal-400 to-teal-700 text-white',
            'bg-linear-to-br from-orange-400 to-orange-700 text-white',
            'bg-linear-to-br from-cyan-400 to-cyan-700 text-white',
        ],
    ];
    $colors = $palettes[$variant] ?? $palettes['soft'];
    $color = $colors[$brand->id % count($colors)];
    // Size, rounding and text size are defaults only; a caller's class replaces them instead of fighting them.
    $defaults = $attributes->has('class') ? '' : 'size-8 rounded-md text-xs';
    $initials = Str::of($brand->name)->explode(' ')->filter()->take(2)->map(fn ($w) => Str::upper(Str::substr($w, 0, 1)))->join('');
@endphp

    <span {{ $attributes->merge(['class' => "flex shrink-0 items-center justify-center font-semibold $color $defaults"]) }}>{{ $initials }}</span>
