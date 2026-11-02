@extends('layouts.app')

@section('title', 'Orders')
@section('header', 'Orders')
@section('subheader', 'New subscriptions and renewals.')

@section('breadcrumbs')
    <x-crumbs :items="['Orders' => null]" />
@endsection

@can('orders.create')
    @section('actions')
        <x-primary-link :href="route('orders.create')">New order</x-primary-link>
    @endsection
@endcan

@section('content')
    <x-list-card :items="$orders" noun="order" :action="route('orders.index')" :filtered="$filters['q'] !== '' || $filters['status'] || $filters['type']">
        <x-slot:filters>
            <x-filter-search :value="$filters['q']" placeholder="Reference, customer or coupon…" />
            <x-filter-select name="status" placeholder="All statuses" :options="App\Models\Order::STATUSES" :selected="$filters['status']" />
            <x-filter-select name="type" placeholder="All types" :options="['new' => 'New', 'renewal' => 'Renewal']" :selected="$filters['type']" />
        </x-slot:filters>
        <x-slot:empty>
            @can('orders.create')
                <x-primary-link :href="route('orders.create')">New order</x-primary-link>
            @endcan
        </x-slot:empty>

        <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
            <tr>
                <th class="px-4 py-3 first:pl-6">Reference</th>
                <th class="min-w-56 px-4 py-3">Customer</th>
                <th class="px-4 py-3">Plan</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3 last:pr-6">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
            @foreach ($orders as $order)
                <tr class="hover:bg-neutral-50/60 dark:hover:bg-white/5">
                    <td class="px-4 py-3 whitespace-nowrap first:pl-6"><a href="{{ route('orders.show', $order) }}" class="font-mono text-xs font-medium text-neutral-900 hover:text-accent-600 dark:text-white dark:hover:text-accent-400">{{ $order->reference_number }}</a></td>
                    <td class="px-4 py-3">
                        <p class="truncate text-neutral-900 dark:text-white">{{ $order->customer->full_name }}</p>
                        <p class="truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $order->customer->email }}</p>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-neutral-600 dark:text-neutral-300">{{ $order->plan_name }}</td>
                    <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">{{ ucfirst($order->type) }}</td>
                    <td class="px-4 py-3"><x-status :status="$order->status" /></td>
                    <td class="px-4 py-3 text-right text-neutral-900 dark:text-white"><x-money :amount="$order->total" :currency="$order->currency" /></td>
                    <td class="px-4 py-3 whitespace-nowrap text-neutral-500 last:pr-6 dark:text-neutral-400" title="{{ $order->created_at?->format('M j, Y H:i') }} UTC">{{ $order->created_at?->format('M j, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </x-list-card>
@endsection
