@extends('layouts.app')

@section('title', $customer->full_name)
@section('header', $customer->full_name)
@section('subheader', $customer->email)

@section('breadcrumbs')
    <x-crumbs :items="['Customers' => route('customers.index'), $customer->full_name => null]" />
@endsection

@section('actions')
    @can('customers.update')
        <a href="{{ route('customers.edit', $customer) }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-neutral-700 shadow-sm ring-1 ring-neutral-300 ring-inset hover:bg-neutral-50 dark:bg-white/5 dark:text-neutral-300 dark:ring-white/10 dark:hover:bg-white/10">Edit</a>
    @endcan
    @can('orders.create')
        <x-primary-link :href="route('orders.create', ['customer' => $customer->id])">New order</x-primary-link>
    @endcan
@endsection

@section('content')
    @php
        $card = 'rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10';
        $head = 'flex items-center justify-between gap-3 border-b border-neutral-200 px-5 py-3 dark:border-white/10';
        $title = 'text-sm font-semibold text-neutral-900 dark:text-white';
        $th = 'bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400';
        $link = 'font-medium text-neutral-900 hover:text-accent-600 dark:text-white dark:hover:text-accent-400';
        $empty = 'px-5 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400';
        $action = 'text-xs font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400';
    @endphp

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="{{ $card }}">
                <div class="{{ $head }}">
                    <h2 class="{{ $title }}">Subscriptions</h2>
                    @can('subscriptions.create')<a href="{{ route('subscriptions.create', ['customer' => $customer->id]) }}" class="{{ $action }}">Add by hand</a>@endcan
                </div>
                @if ($subscriptions->isEmpty())
                    <p class="{{ $empty }}">No subscriptions on this brand.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-white/10">
                            <thead class="{{ $th }}"><tr><th class="px-5 py-2.5">Line</th><th class="px-5 py-2.5">Device</th><th class="px-5 py-2.5">Status</th><th class="px-5 py-2.5">Expires</th></tr></thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
                                @foreach ($subscriptions as $subscription)
                                    <tr>
                                        <td class="px-5 py-2.5"><a href="{{ route('subscriptions.show', $subscription) }}" class="{{ $link }}">#{{ $subscription->id }} · {{ $subscription->plan->name }}</a></td>
                                        <td class="px-5 py-2.5 text-neutral-600 dark:text-neutral-300">{{ App\Models\Subscription::DEVICE_TYPES[$subscription->device_type] ?? $subscription->device_type }}<p class="max-w-48 truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $subscription->device_info }}</p></td>
                                        <td class="px-5 py-2.5"><x-status :status="$subscription->status" /></td>
                                        <td class="px-5 py-2.5 whitespace-nowrap text-neutral-600 dark:text-neutral-300" title="{{ $subscription->expires_at?->format('M j, Y H:i') }} UTC">{{ $subscription->expires_at?->format('M j, Y') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="{{ $card }}">
                <div class="{{ $head }}"><h2 class="{{ $title }}">Orders</h2></div>
                @if ($orders->isEmpty())
                    <p class="{{ $empty }}">No orders on this brand.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-white/10">
                            <thead class="{{ $th }}"><tr><th class="px-5 py-2.5">Reference</th><th class="px-5 py-2.5">Plan</th><th class="px-5 py-2.5">Status</th><th class="px-5 py-2.5 text-right">Total</th><th class="px-5 py-2.5">Date</th></tr></thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
                                @foreach ($orders as $order)
                                    <tr>
                                        <td class="px-5 py-2.5"><a href="{{ route('orders.show', $order) }}" class="font-mono text-xs {{ $link }}">{{ $order->reference_number }}</a></td>
                                        <td class="px-5 py-2.5 text-neutral-600 dark:text-neutral-300">{{ $order->plan_name }}</td>
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

            <section class="{{ $card }}">
                <div class="{{ $head }}">
                    <h2 class="{{ $title }}">Trials</h2>
                    @can('trials.create')<a href="{{ route('trials.create', ['customer' => $customer->id]) }}" class="{{ $action }}">New trial</a>@endcan
                </div>
                @if ($trials->isEmpty())
                    <p class="{{ $empty }}">No trials on this brand.</p>
                @else
                    <ul class="divide-y divide-neutral-100 text-sm dark:divide-white/10">
                        @foreach ($trials as $trial)
                            <li class="flex items-center justify-between gap-3 px-5 py-2.5">
                                <a href="{{ route('trials.show', $trial) }}" class="{{ $link }}">Trial #{{ $trial->id }}</a>
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $trial->created_at?->format('M j, Y') }}</span>
                                <x-status :status="$trial->status" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            @if ($contacts->isNotEmpty())
                <section class="{{ $card }}">
                    <div class="{{ $head }}"><h2 class="{{ $title }}">Contact messages</h2></div>
                    <ul class="divide-y divide-neutral-100 text-sm dark:divide-white/10">
                        @foreach ($contacts as $contact)
                            <li class="flex items-center justify-between gap-3 px-5 py-2.5">
                                <a href="{{ route('contacts.show', $contact) }}" class="truncate {{ $link }}">{{ $contact->subject ?: Str::limit($contact->message, 60) }}</a>
                                <x-status :status="$contact->status" />
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <div class="space-y-6">
            <x-details title="Details" :rows="[
                'Phone' => $customer->phone,
                'Country' => $customer->country,
                'Signed up on' => $customer->brand?->name,
                'Added' => $customer->created_at?->format('M j, Y'),
            ]" />

            @if ($customer->notes)
                <div class="{{ $card }}">
                    <h3 class="border-b border-neutral-200 px-5 py-3 {{ $title }} dark:border-white/10">Internal notes</h3>
                    <p class="px-5 py-3 text-sm whitespace-pre-line text-neutral-700 dark:text-neutral-300">{{ $customer->notes }}</p>
                </div>
            @endif

            @can('customers.delete')
                <form method="POST" action="{{ route('customers.destroy', $customer) }}" data-confirm="Archive {{ $customer->email }}? Their history is kept and you can restore them later.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-medium text-rose-600 hover:text-rose-500 dark:text-rose-400">Archive this customer</button>
                </form>
            @endcan
        </div>
    </div>
@endsection
