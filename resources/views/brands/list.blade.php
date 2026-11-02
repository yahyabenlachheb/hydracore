@extends('layouts.app')

@section('title', 'Brands')
@section('header', 'Brands')
@section('subheader', 'The storefronts you sell under.')

@section('breadcrumbs')
    <a href="{{ route('home') }}" class="hover:text-neutral-900 dark:hover:text-white">Home</a>
    <svg class="size-4 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="font-medium text-neutral-900 dark:text-white">Brands</span>
@endsection

@can('brands.create')
    @section('actions')
        <a href="{{ route('brands.create') }}" class="inline-flex items-center gap-2 rounded-md bg-accent-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-accent-500">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New brand
        </a>
    @endsection
@endcan

@section('content')
    <div data-ajax-list class="overflow-hidden rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10">

        {{-- Filters --}}
        <form method="GET" action="{{ route('brands.index') }}" class="flex flex-col gap-3 border-b border-neutral-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                {{ number_format($brands->total()) }} {{ Str::plural('brand', $brands->total()) }}
            </p>
            <div class="flex gap-2">
                <div class="relative">
                    <svg class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search name or website…"
                           class="w-full rounded-md border-0 py-2 pr-3 pl-9 text-sm ring-1 ring-neutral-300 ring-inset placeholder:text-neutral-400 focus:ring-2 focus:ring-accent-600 focus:outline-none sm:w-64 dark:bg-white/5 dark:ring-white/10">
                </div>
                <select name="status" onchange="this.form.requestSubmit()"
                        class="rounded-md border-0 py-2 pr-8 pl-3 text-sm ring-1 ring-neutral-300 ring-inset focus:ring-2 focus:ring-accent-600 focus:outline-none dark:bg-white/5 dark:ring-white/10">
                    <option value="">All statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
                @if ($search !== '' || $status)
                    <a href="{{ route('brands.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-white/10 dark:hover:text-white">Reset</a>
                @endif
            </div>
        </form>

        @if ($brands->isEmpty())
            {{-- Empty state --}}
            <div class="px-6 py-16 text-center">
                <svg class="mx-auto size-10 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" /></svg>
                @if ($search !== '' || $status)
                    <h3 class="mt-3 text-sm font-semibold text-neutral-900 dark:text-white">No brands match your filters</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Try a different search or <a href="{{ route('brands.index') }}" class="font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">reset the filters</a>.</p>
                @else
                    <h3 class="mt-3 text-sm font-semibold text-neutral-900 dark:text-white">No brands yet</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Create a brand to start adding plans and customers.</p>
                    @can('brands.create')
                        <a href="{{ route('brands.create') }}" class="mt-4 inline-flex rounded-md bg-accent-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-accent-500">New brand</a>
                    @endcan
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-white/10">
                    <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
                        <tr>
                            <th class="min-w-64 px-4 py-3 first:pl-6">Brand</th>
                            <th class="px-4 py-3">Website</th>
                            <th class="px-4 py-3 text-right">Plans</th>
                            <th class="px-4 py-3 text-right">Customers</th>
                            <th class="px-4 py-3 text-right">Users</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Created</th>
                            <th class="px-4 py-3 last:pr-6"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
                        @foreach ($brands as $brand)
                            <tr class="hover:bg-neutral-50/60 dark:hover:bg-white/5">
                                <td class="px-4 py-4 first:pl-6">
                                    <div class="flex items-center gap-3">
                                        <x-brand-avatar :brand="$brand" variant="solid" class="size-10 rounded-md text-sm" />
                                        <div class="min-w-0">
                                            <p class="font-medium text-neutral-900 dark:text-white">{{ $brand->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if ($brand->website)
                                        <a href="{{ $brand->website }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-neutral-600 hover:text-accent-600 dark:text-neutral-300 dark:hover:text-accent-400">
                                            {{ parse_url($brand->website, PHP_URL_HOST) ?: $brand->website }}
                                            <svg class="size-3.5 opacity-60" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                        </a>
                                    @else
                                        <span class="text-neutral-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right text-neutral-600 tabular-nums dark:text-neutral-300">{{ $brand->plans_count }}</td>
                                <td class="px-4 py-4 text-right text-neutral-600 tabular-nums dark:text-neutral-300">{{ number_format($brand->customers_count) }}</td>
                                <td class="px-4 py-4 text-right text-neutral-600 tabular-nums dark:text-neutral-300">{{ $brand->users_count }}</td>
                                <td class="px-4 py-4">
                                    @if ($brand->is_active)
                                        <span class="inline-flex items-center rounded bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20 ring-inset dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">Active</span>
                                    @else
                                        <span class="inline-flex items-center rounded bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600 ring-1 ring-neutral-500/20 ring-inset dark:bg-white/5 dark:text-neutral-400 dark:ring-white/10">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-neutral-500 dark:text-neutral-400" title="{{ $brand->created_at->toDayDateTimeString() }}">{{ $brand->created_at->format('M j, Y') }}</td>
                                <td class="px-4 py-4 last:pr-6">
                                    <div class="flex items-center justify-end gap-1">
                                        @can('brands.update')
                                            <a href="{{ route('brands.edit', $brand) }}" title="Edit" class="rounded-md p-1.5 text-neutral-400 hover:bg-accent-50 hover:text-accent-600 dark:hover:bg-accent-500/10 dark:hover:text-accent-400">
                                                <span class="sr-only">Edit {{ $brand->name }}</span>
                                                <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                            </a>
                                        @endcan
                                        @can('brands.delete')
                                            <form method="POST" action="{{ route('brands.destroy', $brand) }}" data-confirm="Delete “{{ $brand->name }}”? This can’t be undone.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete" class="rounded-md p-1.5 text-neutral-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-400">
                                                    <span class="sr-only">Delete {{ $brand->name }}</span>
                                                    <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($brands->hasPages())
                <div class="border-t border-neutral-200 px-6 py-3 dark:border-white/10">
                    {{ $brands->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
