{{-- Shared trial fields for create and edit. Expects $trial, $customer and $packs. --}}
@php($devices = App\Models\Subscription::DEVICE_TYPES + array_filter([$trial->device_type => $trial->device_type]))
<div class="grid gap-6 p-6 sm:grid-cols-6">
    @if ($trial->exists)
        <div class="sm:col-span-6">
            <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Customer</p>
            <p class="mt-1.5 text-sm text-neutral-900 dark:text-white">{{ $customer->full_name }} · {{ $customer->email }}</p>
        </div>
    @else
        <x-customer-picker class="sm:col-span-6" :customer="$customer" />
    @endif

    <x-select wrapper="sm:col-span-3" name="device_type" label="Device" required placeholder="Choose a device…" :options="$devices" :value="$trial->device_type" />
    <x-field wrapper="sm:col-span-3" name="device_info" label="Device details" maxlength="255" :value="$trial->device_info" placeholder="MAC, app ID or model" />
    <x-field wrapper="sm:col-span-3" name="expires_at" label="Expires (UTC)" type="datetime-local" :value="$trial->expires_at?->format('Y-m-d\TH:i')" :hint="'Empty: ' . App\Models\Trial::DEFAULT_HOURS . ' hours from approval.'" />
    <x-pack-picker class="sm:col-span-6" :packs="$packs" :selected="$trial->exists ? $trial->packs->modelKeys() : []" />
</div>
