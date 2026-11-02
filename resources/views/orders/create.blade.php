@extends('layouts.app')

@section('title', $subscription ? 'Renew subscription' : 'New order')
@section('header', $subscription ? "Renew subscription #{$subscription->id}" : 'New order')
@section('subheader', $trial ? "Converts trial #{$trial->id} when paid." : 'The price is copied from the plan. The line starts once a payment is recorded.')

@section('breadcrumbs')
    <x-crumbs :items="['Orders' => route('orders.index'), 'New' => null]" />
@endsection

@section('content')
    @if ($plans->isEmpty())
        <div class="rounded-md bg-white px-6 py-16 text-center shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">This brand has no active plans</h3>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Add a plan before creating orders.</p>
            @can('plans.create')
                <div class="mt-4"><x-primary-link :href="route('plans.create')">New plan</x-primary-link></div>
            @endcan
        </div>
    @else
        <x-form-page :action="route('orders.store')" :cancel="route('orders.index')" :submit="$subscription ? 'Create renewal' : 'Create order'">
            <div class="grid gap-6 p-6 sm:grid-cols-6">
                @if ($subscription)
                    <input type="hidden" name="subscription_id" value="{{ $subscription->id }}">
                    <input type="hidden" name="customer_id" value="{{ $subscription->customer_id }}">
                    <div class="sm:col-span-6">
                        <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Customer</p>
                        <p class="mt-1.5 text-sm text-neutral-900 dark:text-white">{{ $customer->full_name }} · {{ $customer->email }}</p>
                    </div>
                @else
                    <x-customer-picker class="sm:col-span-6" :customer="$customer" />
                @endif

                @if ($trial)
                    <input type="hidden" name="trial" value="{{ $trial->id }}">
                @endif

                <x-select wrapper="sm:col-span-6" name="plan_id" label="Plan" required placeholder="Choose a plan…" :value="$subscription?->plan_id"
                          :options="$plans->mapWithKeys(fn ($plan) => [$plan->id => $plan->name . ' — ' . number_format((float) $plan->price, 2) . ' ' . $plan->currency . ' · ' . $plan->number_of_connections . ' ' . Str::plural('connection', $plan->number_of_connections)])" />

                <x-field wrapper="sm:col-span-3" name="discount" label="Discount" type="number" min="0" step="0.01" placeholder="0.00" hint="In the plan’s currency." />
                <x-field wrapper="sm:col-span-3" name="coupon_code" label="Coupon code" maxlength="50" />
                <x-field wrapper="sm:col-span-6" name="description" label="Description" maxlength="255" />
            </div>
        </x-form-page>
    @endif
@endsection
