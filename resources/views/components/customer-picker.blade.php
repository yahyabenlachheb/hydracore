@props(['customer' => null, 'name' => 'customer_id'])
{{-- Searches customers as you type (there can be too many for a select) and submits the chosen id. --}}
@php
    $oldId = old($name);
    $chosen = $oldId && (int) $oldId !== $customer?->id
        ? App\Models\Customer::visibleTo(app(App\Support\BrandContext::class)->current()?->id)->find($oldId)
        : $customer;
@endphp
<div {{ $attributes->class(['relative']) }} data-customer-picker data-url="{{ route('customers.search') }}">
    <label for="{{ $name }}_search" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Customer <span class="text-rose-500">*</span></label>
    <input type="hidden" name="{{ $name }}" value="{{ $chosen?->id }}" data-customer-id>
    <input type="text" id="{{ $name }}_search" data-customer-search autocomplete="off" placeholder="Search by name or email…"
           value="{{ $chosen ? "{$chosen->full_name} · {$chosen->email}" : '' }}"
           @class([
               'mt-1.5 block w-full rounded-md border-0 px-3 py-2 text-sm ring-1 ring-inset placeholder:text-neutral-400 focus:ring-2 focus:outline-none dark:bg-white/5',
               'ring-rose-300 focus:ring-rose-500 dark:ring-rose-500/50' => $errors->has($name),
               'ring-neutral-300 focus:ring-accent-600 dark:ring-white/10' => ! $errors->has($name),
           ])>
    <ul data-customer-results class="absolute inset-x-0 z-20 mt-1 hidden max-h-64 overflow-y-auto rounded-md bg-white py-1 text-sm shadow-lg ring-1 ring-neutral-200 dark:bg-neutral-800 dark:ring-white/10"></ul>
    @error($name)
        <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
    @else
        @can('customers.create')
            <p class="mt-1.5 text-xs text-neutral-500 dark:text-neutral-400">Not in the list? <a href="{{ route('customers.create') }}" class="font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">Add a customer</a> first.</p>
        @endcan
    @enderror
</div>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-customer-picker]').forEach((picker) => {
                const search = picker.querySelector('[data-customer-search]');
                const id = picker.querySelector('[data-customer-id]');
                const results = picker.querySelector('[data-customer-results]');
                let timer, controller;

                const close = () => results.classList.add('hidden');
                const load = async () => {
                    controller?.abort();
                    controller = new AbortController();
                    try {
                        const url = `${picker.dataset.url}?q=${encodeURIComponent(search.value.trim())}`;
                        const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
                        const customers = await response.json();

                        // Built with textContent: names come from customers.
                        results.replaceChildren(...customers.map((customer) => {
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'block w-full px-3 py-2 text-left hover:bg-neutral-100 dark:hover:bg-white/5';
                            const name = document.createElement('span');
                            name.className = 'block font-medium text-neutral-900 dark:text-white';
                            name.textContent = customer.full_name;
                            const email = document.createElement('span');
                            email.className = 'block text-xs text-neutral-500 dark:text-neutral-400';
                            email.textContent = customer.email;
                            button.append(name, email);
                            button.addEventListener('click', () => {
                                id.value = customer.id;
                                search.value = `${customer.full_name} · ${customer.email}`;
                                close();
                            });
                            const item = document.createElement('li');
                            item.append(button);
                            return item;
                        }));
                        if (!customers.length) {
                            const empty = document.createElement('li');
                            empty.className = 'px-3 py-2 text-neutral-500 dark:text-neutral-400';
                            empty.textContent = 'No customers found.';
                            results.append(empty);
                        }
                        results.classList.remove('hidden');
                    } catch (e) {
                        if (e.name !== 'AbortError') close();
                    }
                };

                search.addEventListener('input', () => {
                    id.value = ''; // Typing again clears the choice, so a stale id can't be submitted.
                    clearTimeout(timer);
                    timer = setTimeout(load, 250);
                });
                search.addEventListener('focus', () => { if (!id.value) load(); });
                document.addEventListener('click', (e) => { if (!picker.contains(e.target)) close(); });
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
            });
        </script>
    @endpush
@endonce
