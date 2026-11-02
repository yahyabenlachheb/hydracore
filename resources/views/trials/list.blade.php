@extends('layouts.app')

@section('title', 'Trials')
@section('header', 'Trials')
@section('subheader', 'Free trials on this brand, from request to sale.')

@section('breadcrumbs')
    <x-crumbs :items="['Trials' => null]" />
@endsection

@can('trials.create')
    @section('actions')
        <x-primary-link :href="route('trials.create')">New trial</x-primary-link>
    @endsection
@endcan

@section('content')
    <x-list-card :items="$trials" noun="trial" :action="route('trials.index')" :filtered="$filters['q'] !== '' || $filters['status']">
        <x-slot:filters>
            <x-filter-search :value="$filters['q']" placeholder="Customer, device or IP…" />
            <x-filter-select name="status" placeholder="All statuses" :options="App\Models\Trial::STATUSES" :selected="$filters['status']" />
        </x-slot:filters>
        <x-slot:empty>
            @can('trials.create')
                <x-primary-link :href="route('trials.create')">New trial</x-primary-link>
            @endcan
        </x-slot:empty>

        <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
            <tr>
                <th class="px-4 py-3 first:pl-6">Trial</th>
                <th class="min-w-56 px-4 py-3">Customer</th>
                <th class="px-4 py-3">Device</th>
                <th class="px-4 py-3">Packs</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Expires</th>
                <th class="px-4 py-3 last:pr-6">Requested</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
            @foreach ($trials as $trial)
                <tr class="hover:bg-neutral-50/60 dark:hover:bg-white/5">
                    <td class="px-4 py-3 first:pl-6"><a href="{{ route('trials.show', $trial) }}" class="font-medium text-neutral-900 hover:text-accent-600 dark:text-white dark:hover:text-accent-400">#{{ $trial->id }}</a></td>
                    <td class="px-4 py-3">
                        <p class="truncate text-neutral-900 dark:text-white">{{ $trial->customer->full_name }}</p>
                        <p class="truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $trial->customer->email }}</p>
                    </td>
                    <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">
                        {{ App\Models\Subscription::DEVICE_TYPES[$trial->device_type] ?? $trial->device_type }}
                        @if ($trial->device_info)
                            <p class="max-w-48 truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $trial->device_info }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-neutral-600 tabular-nums dark:text-neutral-300" title="{{ $trial->packs->pluck('name')->join(', ') }}">{{ $trial->packs->count() }}</td>
                    <td class="px-4 py-3"><x-status :status="$trial->status" /></td>
                    <td class="px-4 py-3 whitespace-nowrap text-neutral-600 dark:text-neutral-300">{{ $trial->expires_at?->format('M j, H:i') ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-neutral-500 last:pr-6 dark:text-neutral-400">{{ $trial->created_at?->format('M j, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </x-list-card>
@endsection
