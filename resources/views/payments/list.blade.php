@extends('layouts.app')

@section('title', 'Payments')
@section('header', 'Payments')
@section('subheader', 'Every payment attempt on this brand’s orders.')

@section('breadcrumbs')
    <x-crumbs :items="['Payments' => null]" />
@endsection

@section('content')
    <x-list-card :items="$payments" noun="payment" :action="route('payments.index')" :filtered="$filters['q'] !== '' || $filters['status'] || $filters['provider']" empty-text="They appear here when a payment is recorded on an order.">
        <x-slot:filters>
            <x-filter-search :value="$filters['q']" placeholder="Transaction or reference…" />
            <x-filter-select name="status" placeholder="All statuses" :options="App\Models\Payment::STATUSES" :selected="$filters['status']" />
            <x-filter-select name="provider" placeholder="All providers" :options="App\Models\Payment::PROVIDERS" :selected="$filters['provider']" />
        </x-slot:filters>

        <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
            <tr>
                <th class="px-4 py-3 first:pl-6">Payment</th>
                <th class="px-4 py-3">Order</th>
                <th class="min-w-56 px-4 py-3">Customer</th>
                <th class="px-4 py-3">Provider</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Amount</th>
                <th class="px-4 py-3 last:pr-6">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
            @foreach ($payments as $payment)
                <tr class="hover:bg-neutral-50/60 dark:hover:bg-white/5">
                    <td class="px-4 py-3 whitespace-nowrap first:pl-6"><a href="{{ route('payments.show', $payment) }}" class="font-mono text-xs font-medium text-neutral-900 hover:text-accent-600 dark:text-white dark:hover:text-accent-400">{{ $payment->provider_payment_id ? Str::limit($payment->provider_payment_id, 28) : "#{$payment->id}" }}</a></td>
                    <td class="px-4 py-3 whitespace-nowrap font-mono text-xs text-neutral-600 dark:text-neutral-300">
                        @can('orders.view')
                            <a href="{{ route('orders.show', $payment->order) }}" class="hover:text-accent-600 dark:hover:text-accent-400">{{ $payment->order->reference_number }}</a>
                        @else
                            {{ $payment->order->reference_number }}
                        @endcan
                    </td>
                    <td class="px-4 py-3">
                        <p class="truncate text-neutral-900 dark:text-white">{{ $payment->order->customer->full_name }}</p>
                        <p class="truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $payment->order->customer->email }}</p>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-neutral-600 dark:text-neutral-300">{{ App\Models\Payment::PROVIDERS[$payment->provider] ?? $payment->provider }}</td>
                    <td class="px-4 py-3"><x-status :status="$payment->status" /></td>
                    <td class="px-4 py-3 text-right text-neutral-900 dark:text-white"><x-money :amount="$payment->amount" :currency="$payment->currency" /></td>
                    <td class="px-4 py-3 whitespace-nowrap text-neutral-500 last:pr-6 dark:text-neutral-400" title="{{ $payment->created_at?->format('M j, Y H:i') }} UTC">{{ $payment->created_at?->format('M j, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </x-list-card>
@endsection
