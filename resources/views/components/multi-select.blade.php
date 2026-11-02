@props(['name', 'placeholder', 'plural', 'options', 'selected' => [], 'counts' => [], 'searchable' => false, 'mono' => false])
{{--
    Multi-select filter for list pages: a dropdown of checkboxes submitting {name}[]. The form is
    submitted when the dropdown closes (Apply, click outside, Escape) and the choice changed, so ticking
    several boxes reloads the list once.
    $options: value => label, in the order shown (ticked ones come first). $counts: value => number
    shown beside it. With mono, two-letter values are country codes and get the country's name.
--}}
@php
    $selected = array_map('strval', $selected);
    $options = collect($options)->sortBy(fn ($label, $value) => in_array((string) $value, $selected, true) ? 0 : 1)->all();
    $summary = match (count($selected)) {
        0 => $placeholder,
        1 => $options[$selected[0]] ?? $selected[0],
        default => count($selected) . ' ' . $plural,
    };
@endphp
<details data-multi class="relative" {{ $attributes }}>
    <summary @class([
        'flex cursor-pointer list-none items-center gap-2 rounded-md py-2 pr-2.5 pl-3 text-sm ring-1 ring-inset select-none focus:ring-2 focus:ring-accent-600 focus:outline-none dark:bg-white/5 [&::-webkit-details-marker]:hidden',
        'ring-neutral-300 dark:ring-white/10' => ! $selected,
        'bg-accent-50 text-accent-800 ring-accent-500 dark:bg-accent-500/10 dark:text-accent-200 dark:ring-accent-400/60' => $selected,
    ])>
        <span data-multi-text class="max-w-40 truncate">{{ $summary }}</span>
        <svg class="size-4 shrink-0 text-neutral-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
    </summary>
    {{-- Positioned on the window when opened (see the script): list cards clip what overflows them. --}}
    <div data-multi-panel class="fixed z-50 w-60 rounded-md bg-white shadow-lg ring-1 ring-neutral-200 dark:bg-neutral-800 dark:ring-white/10">
        @if ($searchable)
            <div class="border-b border-neutral-100 p-2 dark:border-white/10">
                <input type="text" data-multi-search placeholder="Search…" autocomplete="off"
                       class="block w-full rounded border-0 px-2 py-1 text-sm ring-1 ring-neutral-300 ring-inset placeholder:text-neutral-400 focus:ring-2 focus:ring-accent-600 focus:outline-none dark:bg-white/5 dark:ring-white/10">
            </div>
        @endif
        <div data-multi-list class="max-h-64 overflow-y-auto py-1">
            @foreach ($options as $value => $label)
                <label data-multi-option class="flex cursor-pointer items-center gap-2.5 px-3 py-1.5 text-sm text-neutral-700 hover:bg-neutral-50 dark:text-neutral-300 dark:hover:bg-white/5">
                    <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}" @checked(in_array((string) $value, $selected, true)) class="size-3.5 rounded accent-accent-600">
                    @if ($mono && strlen((string) $value) === 2)
                        <span class="font-mono text-xs font-semibold">{{ $label }}</span>
                        <span class="truncate text-xs text-neutral-500 dark:text-neutral-400" data-country="{{ $value }}"></span>
                    @else
                        <span>{{ $label }}</span>
                    @endif
                    @if (isset($counts[$value]))<span class="ml-auto pl-2 text-xs text-neutral-400 tabular-nums">{{ number_format($counts[$value]) }}</span>@endif
                </label>
            @endforeach
            <p data-multi-none hidden class="px-3 py-2 text-sm text-neutral-500 dark:text-neutral-400">No match</p>
        </div>
        <div class="flex items-center justify-between border-t border-neutral-100 px-3 py-2 text-xs dark:border-white/10">
            <button type="button" data-multi-clear class="font-medium text-neutral-500 hover:text-rose-600 dark:text-neutral-400 dark:hover:text-rose-400">Clear</button>
            <button type="button" data-multi-apply class="rounded bg-accent-600 px-2.5 py-1 font-semibold text-white hover:bg-accent-500">Apply</button>
        </div>
    </div>
</details>

@once
    @push('scripts')
        <script>
            (() => {
                // Delegated to the document: list pages swap their filters in place after each load.
                const state = (multi) => [...multi.querySelectorAll('input[type=checkbox]:checked')].map((box) => box.value).join(',');
                let names = null;
                try { names = new Intl.DisplayNames(['en'], { type: 'region' }); } catch {}
                const fillNames = (root) => root.querySelectorAll('[data-multi] [data-country]:empty').forEach((el) => {
                    try { el.textContent = names?.of(el.dataset.country) ?? ''; } catch {}
                    el.closest('[data-multi-option]').dataset.search = (el.closest('[data-multi-option]').textContent).toLowerCase();
                });

                // Under the button, right-aligned with it, kept inside the window; above it when there's
                // more room there. The list shrinks to fit.
                const place = (multi) => {
                    const panel = multi.querySelector('[data-multi-panel]');
                    const list = multi.querySelector('[data-multi-list]');
                    const button = multi.querySelector('summary').getBoundingClientRect();
                    const gap = 4;
                    const below = window.innerHeight - button.bottom - gap - 8;
                    const above = button.top - gap - 8;
                    const up = below < 220 && above > below;
                    list.style.maxHeight = '';
                    const chrome = panel.offsetHeight - list.offsetHeight; // search box and buttons
                    list.style.maxHeight = `${Math.max(96, Math.min(256, (up ? above : below) - chrome))}px`;
                    panel.style.left = `${Math.max(8, Math.min(button.right - panel.offsetWidth, window.innerWidth - panel.offsetWidth - 8))}px`;
                    panel.style.top = up ? `${button.top - gap - panel.offsetHeight}px` : `${button.bottom + gap}px`;
                };
                const placeOpen = () => document.querySelectorAll('[data-multi][open]').forEach(place);
                window.addEventListener('resize', placeOpen);
                document.addEventListener('scroll', (e) => e.target.closest?.('[data-multi-panel]') || placeOpen(), true);

                document.addEventListener('toggle', (e) => {
                    const multi = e.target;
                    if (!multi.matches?.('[data-multi]')) return;
                    if (multi.open) {
                        place(multi);
                        multi.dataset.initial = state(multi);
                        fillNames(multi);
                        multi.querySelector('[data-multi-search]')?.focus();
                    } else if (state(multi) !== multi.dataset.initial) {
                        multi.closest('form')?.requestSubmit();
                    }
                }, true);

                // Closing by clicking anywhere else.
                document.addEventListener('click', (e) => {
                    document.querySelectorAll('[data-multi][open]').forEach((multi) => multi.contains(e.target) || (multi.open = false));
                    const button = e.target.closest('[data-multi-apply], [data-multi-clear]');
                    if (!button) return;
                    const multi = button.closest('[data-multi]');
                    if (button.matches('[data-multi-clear]')) multi.querySelectorAll('input[type=checkbox]').forEach((box) => (box.checked = false));
                    multi.open = false;
                });
                document.addEventListener('keydown', (e) => {
                    const multi = e.target.closest?.('[data-multi][open]');
                    if (!multi) return;
                    if (e.key === 'Escape') {
                        multi.open = false;
                        multi.querySelector('summary').focus();
                    } else if (e.key === 'Enter' && e.target.matches('[data-multi-search]')) {
                        e.preventDefault(); // Enter applies instead of submitting half-picked
                        multi.open = false;
                    }
                });

                document.addEventListener('input', (e) => {
                    if (!e.target.matches('[data-multi-search]')) return;
                    const multi = e.target.closest('[data-multi]');
                    const term = e.target.value.trim().toLowerCase();
                    let shown = 0;
                    multi.querySelectorAll('[data-multi-option]').forEach((option) => {
                        const visible = !term || (option.dataset.search ?? option.textContent.toLowerCase()).includes(term);
                        option.hidden = !visible;
                        shown += visible;
                    });
                    multi.querySelector('[data-multi-none]').hidden = shown > 0;
                });
            })();
        </script>
    @endpush
@endonce
