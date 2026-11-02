@extends('layouts.app')

@section('title', 'Packs')
@section('header', 'Packs')
@section('subheader', 'Channel and VOD packs, shared by all brands.')

@section('breadcrumbs')
    <x-crumbs :items="['Packs' => null]" />
@endsection

@can('packs.create')
    @section('actions')
        <x-primary-link :href="route('packs.create')">New pack</x-primary-link>
    @endsection
@endcan

@section('content')
    <x-list-card :items="$packs" noun="pack" :action="route('packs.index')" :filtered="$search !== '' || $type">
        <x-slot:filters>
            <x-filter-search :value="$search" placeholder="Search name or code…" />
            <x-filter-select name="type" placeholder="All types" :options="App\Models\Pack::TYPES" :selected="$type" />
        </x-slot:filters>
        <x-slot:empty>
            @can('packs.create')
                <x-primary-link :href="route('packs.create')">New pack</x-primary-link>
            @endcan
        </x-slot:empty>

        <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
            <tr>
                <th class="min-w-48 px-4 py-3 first:pl-6">Pack</th>
                <th class="px-4 py-3">Code</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3 text-right">Subscriptions</th>
                <th class="px-4 py-3 text-right">Trials</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 last:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
            @foreach ($packs as $pack)
                <tr @class(['hover:bg-neutral-50/60 dark:hover:bg-white/5', 'opacity-60' => ! $pack->is_active])>
                    <td class="px-4 py-3 font-medium text-neutral-900 first:pl-6 dark:text-white">{{ $pack->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-neutral-600 dark:text-neutral-300">{{ $pack->code }}</td>
                    <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">{{ App\Models\Pack::TYPES[$pack->type] }}</td>
                    <td class="px-4 py-3 text-right text-neutral-600 tabular-nums dark:text-neutral-300">{{ number_format($pack->subscriptions_count) }}</td>
                    <td class="px-4 py-3 text-right text-neutral-600 tabular-nums dark:text-neutral-300">{{ number_format($pack->trials_count) }}</td>
                    <td class="px-4 py-3"><x-status :status="$pack->is_active ? 'active' : 'inactive'" /></td>
                    <td class="px-4 py-3 last:pr-6">
                        <div class="flex items-center justify-end gap-1">
                            @can('packs.update')
                                <x-row-edit :href="route('packs.edit', $pack)" :label="$pack->name" />
                            @endcan
                            @can('packs.delete')
                                <x-row-delete :action="route('packs.destroy', $pack)" :label="$pack->name" confirm="Delete “{{ $pack->name }}”? This can’t be undone." />
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-list-card>
@endsection
