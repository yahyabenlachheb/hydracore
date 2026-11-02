@extends('layouts.app')

@section('title', 'Customers')
@section('header', 'Customers')
@section('subheader', 'Everyone who signed up, bought or tried this brand.')

@section('breadcrumbs')
    <x-crumbs :items="['Customers' => null]" />
@endsection

@can('customers.create')
    @section('actions')
        <x-primary-link :href="route('customers.create')">New customer</x-primary-link>
    @endsection
@endcan

@section('content')
    <x-list-card :items="$customers" noun="customer" :action="route('customers.index')" :filtered="$filters['q'] !== '' || $filters['line'] || $filters['country'] || $filters['archived']">
        <x-slot:filters>
            <x-filter-search :value="$filters['q']" placeholder="Name, email or phone…" />
            <x-filter-select name="line" placeholder="Any line status" :options="['active' => 'Has an active line', 'none' => 'No active line']" :selected="$filters['line']" />
            @if ($countries->isNotEmpty())
                <x-filter-select name="country" placeholder="Any country" :options="$countries->combine($countries)" :selected="$filters['country']" />
            @endif
            <x-filter-select name="archived" placeholder="Current" :options="['1' => 'Archived']" :selected="$filters['archived'] ? '1' : ''" />
        </x-slot:filters>
        <x-slot:empty>
            @can('customers.create')
                <x-primary-link :href="route('customers.create')">New customer</x-primary-link>
            @endcan
        </x-slot:empty>

        <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
            <tr>
                <th class="min-w-64 px-4 py-3 first:pl-6">Customer</th>
                <th class="px-4 py-3">Phone</th>
                <th class="px-4 py-3">Country</th>
                <th class="px-4 py-3 text-right">Active lines</th>
                <th class="px-4 py-3 text-right">Orders</th>
                <th class="px-4 py-3">Added</th>
                <th class="px-4 py-3 last:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
            @foreach ($customers as $customer)
                <tr @class(['hover:bg-neutral-50/60 dark:hover:bg-white/5', 'opacity-60' => $customer->trashed()])>
                    <td class="px-4 py-3 first:pl-6">
                        <div class="flex items-center gap-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-neutral-100 text-xs font-semibold text-neutral-600 uppercase dark:bg-white/10 dark:text-neutral-300">{{ Str::substr($customer->full_name, 0, 1) }}</span>
                            <div class="min-w-0">
                                @if ($customer->trashed())
                                    <span class="block truncate font-medium text-neutral-900 dark:text-white">{{ $customer->full_name }}</span>
                                @else
                                    <a href="{{ route('customers.show', $customer) }}" class="block truncate font-medium text-neutral-900 hover:text-accent-600 dark:text-white dark:hover:text-accent-400">{{ $customer->full_name }}</a>
                                @endif
                                <p class="truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $customer->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-neutral-600 dark:text-neutral-300">{{ $customer->phone ?: '—' }}</td>
                    <td class="px-4 py-3">
                        @if ($customer->country)
                            <x-country :code="$customer->country" />
                        @else
                            <span class="text-neutral-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-neutral-600 tabular-nums dark:text-neutral-300">{{ $customer->active_lines_count }}</td>
                    <td class="px-4 py-3 text-right text-neutral-600 tabular-nums dark:text-neutral-300">{{ $customer->orders_count }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-neutral-500 dark:text-neutral-400" title="{{ $customer->created_at?->toDayDateTimeString() }}">{{ $customer->created_at?->format('M j, Y') }}</td>
                    <td class="px-4 py-3 last:pr-6">
                        <div class="flex items-center justify-end gap-1">
                            @if ($customer->trashed())
                                @can('customers.delete')
                                    <x-action-button :action="route('customers.restore', $customer)">Restore</x-action-button>
                                @endcan
                            @else
                                @can('customers.update')
                                    <x-row-edit :href="route('customers.edit', $customer)" :label="$customer->email" />
                                @endcan
                                @can('customers.delete')
                                    <x-row-delete :action="route('customers.destroy', $customer)" :label="$customer->email" confirm="Archive {{ $customer->email }}? Their history is kept and you can restore them later." />
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-list-card>
@endsection
