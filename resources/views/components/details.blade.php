@props(['title', 'rows'])
{{-- A side card of label/value rows. $rows: label => value (HtmlString for markup, null shows a dash). --}}
<div {{ $attributes->class(['rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10']) }}>
    <h3 class="border-b border-neutral-200 px-5 py-3 text-sm font-semibold text-neutral-900 dark:border-white/10 dark:text-white">{{ $title }}</h3>
    <dl class="divide-y divide-neutral-100 text-sm dark:divide-white/10">
        @foreach ($rows as $label => $value)
            <div class="flex items-baseline justify-between gap-4 px-5 py-2.5">
                <dt class="shrink-0 text-neutral-500 dark:text-neutral-400">{{ $label }}</dt>
                <dd class="min-w-0 text-right break-words text-neutral-900 dark:text-neutral-200">{{ $value === null || $value === '' ? '—' : $value }}</dd>
            </div>
        @endforeach
    </dl>
    {{ $slot }}
</div>
