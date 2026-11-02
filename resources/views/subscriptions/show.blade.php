@extends('layouts.app')

@section('title', "Subscription #{$subscription->id}")
@section('header', "Subscription #{$subscription->id}")
@section('subheader', $subscription->customer->full_name . ' · ' . $subscription->plan->name)

@section('breadcrumbs')
    <x-crumbs :items="['Subscriptions' => route('subscriptions.index'), '#' . $subscription->id => null]" />
@endsection

@section('actions')
    <x-status :status="$subscription->status" class="px-2.5 py-1 text-sm" />
    @can('subscriptions.update')
        <a href="{{ route('subscriptions.edit', $subscription) }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-neutral-700 shadow-sm ring-1 ring-neutral-300 ring-inset hover:bg-neutral-50 dark:bg-white/5 dark:text-neutral-300 dark:ring-white/10 dark:hover:bg-white/10">Edit</a>
    @endcan
    @can('orders.create')
        @unless ($subscription->customer->trashed())
            <x-primary-link :href="route('orders.create', ['subscription' => $subscription->id])">Renew</x-primary-link>
        @endunless
    @endcan
@endsection

@section('content')
    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10">
                <h2 class="border-b border-neutral-200 px-5 py-3 text-sm font-semibold text-neutral-900 dark:border-white/10 dark:text-white">Packs</h2>
                @if ($subscription->packs->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No packs on this line.</p>
                @else
                    <div class="flex flex-wrap gap-2 px-5 py-4">
                        @foreach ($subscription->packs as $pack)
                            <span class="inline-flex items-center gap-1.5 rounded bg-neutral-100 px-2 py-1 text-xs font-medium text-neutral-700 dark:bg-white/5 dark:text-neutral-300">
                                {{ $pack->name }} <span class="text-neutral-500 dark:text-neutral-400">{{ App\Models\Pack::TYPES[$pack->type] }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10">
                <h2 class="border-b border-neutral-200 px-5 py-3 text-sm font-semibold text-neutral-900 dark:border-white/10 dark:text-white">Orders</h2>
                @if ($subscription->orders->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No orders for this line.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-white/10">
                            <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
                                <tr><th class="px-5 py-2.5">Reference</th><th class="px-5 py-2.5">Type</th><th class="px-5 py-2.5">Status</th><th class="px-5 py-2.5 text-right">Total</th><th class="px-5 py-2.5">Date</th></tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
                                @foreach ($subscription->orders as $order)
                                    <tr>
                                        <td class="px-5 py-2.5"><a href="{{ route('orders.show', $order) }}" class="font-mono text-xs font-medium text-neutral-900 hover:text-accent-600 dark:text-white dark:hover:text-accent-400">{{ $order->reference_number }}</a></td>
                                        <td class="px-5 py-2.5 text-neutral-600 dark:text-neutral-300">{{ ucfirst($order->type) }}</td>
                                        <td class="px-5 py-2.5"><x-status :status="$order->status" /></td>
                                        <td class="px-5 py-2.5 text-right text-neutral-900 dark:text-white"><x-money :amount="$order->total" :currency="$order->currency" /></td>
                                        <td class="px-5 py-2.5 whitespace-nowrap text-neutral-500 dark:text-neutral-400">{{ $order->created_at?->format('M j, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        <div class="space-y-6">
            <x-details title="Line" :rows="[
                'Customer' => $subscription->customer->full_name,
                'Email' => $subscription->customer->email,
                'Plan' => $subscription->plan->name,
                'Connections' => $subscription->plan->number_of_connections,
                'Device' => App\Models\Subscription::DEVICE_TYPES[$subscription->device_type] ?? $subscription->device_type,
                'Device details' => $subscription->device_info,
                'Starts' => $subscription->starts_at?->format('M j, Y H:i'),
                'Expires' => $subscription->expires_at?->format('M j, Y H:i'),
                'Cancelled' => $subscription->cancelled_at?->format('M j, Y H:i'),
            ]">
                @unless ($subscription->customer->trashed())
                    @can('customers.view')
                        <div class="border-t border-neutral-200 px-5 py-3 dark:border-white/10">
                            <a href="{{ route('customers.show', $subscription->customer) }}" class="text-sm font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">Open the customer →</a>
                        </div>
                    @endcan
                @endunless
            </x-details>

            @can('subscriptions.update')
                @if ($transitions)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($transitions as $action)
                            <x-action-button :action="route('subscriptions.status', $subscription)" :fields="['action' => $action]" :danger="$action === 'cancel'"
                                             :confirm="$action === 'cancel' ? 'Cancel this subscription? It stops working right away.' : null">{{ ucfirst($action) }}</x-action-button>
                        @endforeach
                    </div>
                @endif
            @endcan
        </div>
    </div>
@endsection
