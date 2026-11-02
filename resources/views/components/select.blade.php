@props(['name', 'label', 'options', 'value' => null, 'placeholder' => null, 'hint' => null, 'wrapper' => ''])
{{-- A labelled select with its validation message. $options: value => label. --}}
@php($current = (string) old($name, $value))
<div class="{{ $wrapper }}">
    <label for="{{ $name }}" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $label }} @if ($attributes->has('required'))<span class="text-rose-500">*</span>@endif</label>
    <select id="{{ $name }}" name="{{ $name }}"
            {{ $attributes->class([
                'mt-1.5 block w-full rounded-md border-0 px-3 py-2 text-sm ring-1 ring-inset focus:ring-2 focus:outline-none dark:bg-white/5',
                'text-rose-900 ring-rose-300 focus:ring-rose-500 dark:bg-rose-500/5 dark:text-rose-300 dark:ring-rose-500/50' => $errors->has($name),
                'ring-neutral-300 focus:ring-accent-600 dark:ring-white/10' => ! $errors->has($name),
            ]) }}>
        @if ($placeholder !== null)
            <option value="" @selected($current === '')>{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($current === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
    @else
        @if ($hint)
            <p class="mt-1.5 text-xs text-neutral-500 dark:text-neutral-400">{{ $hint }}</p>
        @endif
    @enderror
</div>
