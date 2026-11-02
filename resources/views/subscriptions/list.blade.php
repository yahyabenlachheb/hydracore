@extends('layouts.app')

@section('title', 'Subscriptions')
@section('header', 'Subscriptions')
@section('subheader', 'The IPTV lines sold on this brand.')

@section('breadcrumbs')
    <x-crumbs :items="['Subscriptions' => null]" />
@endsection

@can('subscriptions.create')
    @section('actions')
        <x-primary-link :href="route('subscriptions.create')">New subscription</x-primary-link>
    @endsection
@endcan

@section('content')
    <x-list-card :items="$subscriptions" noun="subscription" :action="route('subscriptions.index')" :filtered="$filters['q'] !== '' || $filters['status'] || $filters['plan'] || $filters['expiring']" empty-text="A line is created when a new order is paid.">
        <x-slot:filters>
            <x-filter-search :value="$filters['q']" placeholder="Customer or device…" />
            <x-filter-select name="status" placeholder="All statuses" :options="App\Models\Subscription::STATUSES" :selected="$filters['status']" />
            <x-filter-select name="plan" placeholder="All plans" :options="$plans->pluck('name', 'id')" :selected="$filters['plan']" />
            <x-filter-select name="expiring" placeholder="Any end date" :options="['7' => 'Expiring in 7 days', '30' => 'Expiring in 30 days']" :selected="$filters['expiring']" />
        </x-slot:filters>

        <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
            <tr>
                <th class="px-4 py-3 first:pl-6">Line</th>
                <th class="min-w-56 px-4 py-3">Customer</th>
                <th class="px-4 py-3">Plan</th>
                <th class="px-4 py-3">Device</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 last:pr-6">Expires</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
            @foreach ($subscriptions as $subscription)
                <tr class="hover:bg-neutral-50/60 dark:hover:bg-white/5">
                    <td class="px-4 py-3 first:pl-6"><a href="{{ route('subscriptions.show', $subscription) }}" class="font-medium text-neutral-900 hover:text-accent-600 dark:text-white dark:hover:text-accent-400">#{{ $subscription->id }}</a></td>
                    <td class="px-4 py-3">
                        <p class="truncate text-neutral-900 dark:text-white">{{ $subscription->customer->full_name }}</p>
                        <p class="truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $subscription->customer->email }}</p>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-neutral-600 dark:text-neutral-300">{{ $subscription->plan->name }}</td>
                    <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">
                        {{ App\Models\Subscription::DEVICE_TYPES[$subscription->device_type] ?? $subscription->device_type }}
                        @if ($subscription->device_info)
                            <p class="max-w-48 truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $subscription->device_info }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3"><x-status :status="$subscription->status" /></td>
                    <td class="px-4 py-3 whitespace-nowrap last:pr-6">
                        @if ($subscription->expires_at)
                            <span class="text-neutral-700 dark:text-neutral-300" title="{{ $subscription->expires_at->format('M j, Y H:i') }} UTC">{{ $subscription->expires_at->format('M j, Y') }}</span>
                            @if ($subscription->status === 'active')
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $subscription->expires_at->diffForHumans() }}</p>
                            @endif
                        @else
                            <span class="text-neutral-400">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-list-card>
@endsection
