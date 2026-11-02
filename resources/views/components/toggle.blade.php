@props(['name', 'label', 'checked' => false, 'hint' => null])
<label {{ $attributes->class(['flex cursor-pointer items-start justify-between gap-6 rounded-md p-4 ring-1 ring-neutral-200 ring-inset dark:ring-white/10']) }}>
    <span>
        <span class="block text-sm font-medium text-neutral-900 dark:text-white">{{ $label }}</span>
        @if ($hint)
            <span class="block text-xs text-neutral-500 dark:text-neutral-400">{{ $hint }}</span>
        @endif
    </span>
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked)) class="peer sr-only">
    <span class="relative mt-0.5 h-6 w-11 shrink-0 rounded-full bg-neutral-200 transition-colors after:absolute after:top-0.5 after:left-0.5 after:size-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:bg-accent-600 peer-checked:after:tranneutral-x-5 peer-focus-visible:ring-2 peer-focus-visible:ring-accent-500 peer-focus-visible:ring-offset-2 dark:bg-white/10 dark:peer-focus-visible:ring-offset-neutral-900"></span>
</label>
