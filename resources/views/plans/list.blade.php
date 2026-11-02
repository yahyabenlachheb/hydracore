@extends('layouts.app')

@section('title', 'Plans')
@section('header', 'Plans')
@section('subheader', 'What this brand sells. Prices are copied onto each order, so changing one never rewrites history.')

@section('breadcrumbs')
    <x-crumbs :items="['Plans' => null]" />
@endsection

@can('plans.create')
    @section('actions')
        <x-primary-link :href="route('plans.create')">New plan</x-primary-link>
    @endsection
@endcan

@section('content')
    <x-list-card :items="$plans" noun="plan" :action="route('plans.index')" :filtered="$search !== '' || $status" empty-text="Add the plans this brand sells, such as 1, 3, 6 and 12 months.">
        <x-slot:filters>
            <x-filter-search :value="$search" placeholder="Search plans…" />
            <x-filter-select name="status" placeholder="All statuses" :options="['active' => 'Active', 'inactive' => 'Inactive']" :selected="$status" />
        </x-slot:filters>
        <x-slot:empty>
            @can('plans.create')
                <x-primary-link :href="route('plans.create')">New plan</x-primary-link>
            @endcan
        </x-slot:empty>

        <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
            <tr>
                <th class="min-w-48 px-4 py-3 first:pl-6">Plan</th>
                <th class="px-4 py-3">Duration</th>
                <th class="px-4 py-3 text-right">Price</th>
                <th class="px-4 py-3 text-right">Connections</th>
                <th class="px-4 py-3 text-right">Active lines</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 last:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
            @foreach ($plans as $plan)
                <tr @class(['hover:bg-neutral-50/60 dark:hover:bg-white/5', 'opacity-60' => ! $plan->is_active])>
                    <td class="px-4 py-3 font-medium text-neutral-900 first:pl-6 dark:text-white">{{ $plan->name }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-neutral-600 dark:text-neutral-300">{{ $plan->duration_months }} {{ Str::plural('month', $plan->duration_months) }}</td>
                    <td class="px-4 py-3 text-right text-neutral-900 dark:text-white"><x-money :amount="$plan->price" :currency="$plan->currency" /></td>
                    <td class="px-4 py-3 text-right text-neutral-600 tabular-nums dark:text-neutral-300">{{ $plan->number_of_connections }}</td>
                    <td class="px-4 py-3 text-right text-neutral-600 tabular-nums dark:text-neutral-300">{{ number_format($plan->active_lines_count) }}</td>
                    <td class="px-4 py-3"><x-status :status="$plan->is_active ? 'active' : 'inactive'" /></td>
                    <td class="px-4 py-3 last:pr-6">
                        <div class="flex items-center justify-end gap-1">
                            @can('plans.update')
                                <x-row-edit :href="route('plans.edit', $plan)" :label="$plan->name" />
                            @endcan
                            @can('plans.delete')
                                <x-row-delete :action="route('plans.destroy', $plan)" :label="$plan->name" confirm="Delete “{{ $plan->name }}”? This can’t be undone." />
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-list-card>
@endsection
