{{-- Shared subscription fields for create and edit. Expects $subscription, $customer, $plans and $packs. --}}
@php
    $dateValue = fn ($date) => $date?->format('Y-m-d\TH:i');
    // A device stored by the website may not be one of ours; keep it selectable.
    $devices = App\Models\Subscription::DEVICE_TYPES + array_filter([$subscription->device_type => $subscription->device_type]);
@endphp
<div class="grid gap-6 p-6 sm:grid-cols-6">
    @if ($subscription->exists)
        <div class="sm:col-span-6">
            <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Customer</p>
            <p class="mt-1.5 text-sm text-neutral-900 dark:text-white">{{ $customer->full_name }} · {{ $customer->email }}</p>
        </div>
    @else
        <x-customer-picker class="sm:col-span-6" :customer="$customer" />
    @endif

    <x-select wrapper="sm:col-span-3" name="plan_id" label="Plan" required placeholder="Choose a plan…" :value="$subscription->plan_id"
              :options="$plans->mapWithKeys(fn ($plan) => [$plan->id => $plan->name . ($plan->is_active ? '' : ' (inactive)')])" />
    @unless ($subscription->exists)
        <x-select wrapper="sm:col-span-3" name="status" label="Status" required :options="['active' => 'Active now', 'pending' => 'Pending']" :value="$subscription->status" />
    @endunless

    <x-select wrapper="sm:col-span-3" name="device_type" label="Device" required placeholder="Choose a device…" :options="$devices" :value="$subscription->device_type" />
    <x-field wrapper="sm:col-span-3" name="device_info" label="Device details" maxlength="255" :value="$subscription->device_info" placeholder="MAC, app ID or model" />

    <x-field wrapper="sm:col-span-3" name="starts_at" label="Starts (UTC)" type="datetime-local" :value="$dateValue($subscription->starts_at)" :hint="$subscription->exists ? null : 'Leave empty to start now.'" />
    <x-field wrapper="sm:col-span-3" name="expires_at" label="Expires (UTC)" type="datetime-local" :value="$dateValue($subscription->expires_at)" :hint="$subscription->exists ? null : 'Leave empty to use the plan’s duration.'" />

    <x-pack-picker class="sm:col-span-6" :packs="$packs" :selected="$subscription->exists ? $subscription->packs->modelKeys() : []" />
</div>
