@extends('layouts.app')

@section('title', "Trial #{$trial->id}")
@section('header', "Trial #{$trial->id}")
@section('subheader', $trial->customer->full_name . ' · requested ' . $trial->created_at?->format('M j, Y H:i') . ' UTC')

@section('breadcrumbs')
    <x-crumbs :items="['Trials' => route('trials.index'), '#' . $trial->id => null]" />
@endsection

@section('actions')
    <x-status :status="$trial->status" class="px-2.5 py-1 text-sm" />
    @can('trials.update')
        <a href="{{ route('trials.edit', $trial) }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-neutral-700 shadow-sm ring-1 ring-neutral-300 ring-inset hover:bg-neutral-50 dark:bg-white/5 dark:text-neutral-300 dark:ring-white/10 dark:hover:bg-white/10">Edit</a>
    @endcan
    @can('orders.create')
        @if (in_array($trial->status, ['pending', 'active', 'expired'], true) && ! $trial->customer->trashed())
            <x-primary-link :href="route('orders.create', ['trial' => $trial->id])">Convert to paid</x-primary-link>
        @endif
    @endcan
@endsection

@section('content')
    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            @if ($sameIp || $sameDevice || $sameCustomer)
                <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                    <p class="font-medium">This may be a repeat request.</p>
                    <ul class="mt-1 list-disc pl-5">
                        @if ($sameCustomer)<li>{{ $sameCustomer }} other {{ Str::plural('trial', $sameCustomer) }} for this customer</li>@endif
                        @if ($sameIp)<li>{{ $sameIp }} other {{ Str::plural('trial', $sameIp) }} from this IP address</li>@endif
                        @if ($sameDevice)<li>{{ $sameDevice }} other {{ Str::plural('trial', $sameDevice) }} on this device</li>@endif
                    </ul>
                </div>
            @endif

            <section class="rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10">
                <h2 class="border-b border-neutral-200 px-5 py-3 text-sm font-semibold text-neutral-900 dark:border-white/10 dark:text-white">Packs</h2>
                @if ($trial->packs->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No packs requested.</p>
                @else
                    <div class="flex flex-wrap gap-2 px-5 py-4">
                        @foreach ($trial->packs as $pack)
                            <span class="inline-flex items-center gap-1.5 rounded bg-neutral-100 px-2 py-1 text-xs font-medium text-neutral-700 dark:bg-white/5 dark:text-neutral-300">
                                {{ $pack->name }} <span class="text-neutral-500 dark:text-neutral-400">{{ App\Models\Pack::TYPES[$pack->type] }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <div class="space-y-6">
            <x-details title="Details" :rows="[
                'Customer' => $trial->customer->full_name,
                'Email' => $trial->customer->email,
                'Device' => App\Models\Subscription::DEVICE_TYPES[$trial->device_type] ?? $trial->device_type,
                'Device details' => $trial->device_info,
                'IP address' => $trial->ip_address,
                'Expires' => $trial->expires_at?->format('M j, Y H:i'),
                'Became' => $trial->convertedSubscription ? 'Subscription #' . $trial->convertedSubscription->id : null,
            ]">
                @if ($trial->convertedSubscription)
                    @can('subscriptions.view')
                        <div class="border-t border-neutral-200 px-5 py-3 dark:border-white/10">
                            <a href="{{ route('subscriptions.show', $trial->convertedSubscription) }}" class="text-sm font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">Open the subscription →</a>
                        </div>
                    @endcan
                @endif
            </x-details>

            <div class="flex flex-wrap gap-2">
                @can('trials.update')
                    @foreach ($transitions as $action)
                        <x-action-button :action="route('trials.status', $trial)" :fields="['action' => $action]" :danger="$action === 'reject'">{{ ucfirst($action) }}</x-action-button>
                    @endforeach
                @endcan
                @can('trials.delete')
                    @if ($trial->status !== 'converted')
                        <form method="POST" action="{{ route('trials.destroy', $trial) }}" data-confirm="Delete trial #{{ $trial->id }}? This can’t be undone.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-md px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">Delete</button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>
@endsection
