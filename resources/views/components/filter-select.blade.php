@props(['name', 'options', 'selected' => null, 'placeholder'])
{{-- A list filter that reloads the list when changed. $options: value => label. --}}
<select name="{{ $name }}" aria-label="{{ $placeholder }}" onchange="this.form.requestSubmit()"
        class="max-w-48 rounded-md border-0 py-2 pl-3 text-sm ring-1 ring-neutral-300 ring-inset focus:ring-2 focus:ring-accent-600 focus:outline-none dark:bg-white/5 dark:ring-white/10">
    <option value="">{{ $placeholder }}</option>
    @foreach ($options as $value => $label)
        <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $label }}</option>
    @endforeach
</select>
