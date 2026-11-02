@extends('layouts.app')

@section('title', 'Permissions')
@section('header', 'Permissions')
@section('subheader', 'Individual abilities you group into roles.')

@section('breadcrumbs')
    <a href="{{ route('home') }}" class="hover:text-neutral-900 dark:hover:text-white">Home</a>
    <svg class="size-4 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="font-medium text-neutral-900 dark:text-white">Permissions</span>
@endsection

@can('permissions.create')
    @section('actions')
        <a href="{{ route('permissions.create') }}" class="inline-flex items-center gap-2 rounded-md bg-accent-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-accent-500">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New permission
        </a>
    @endsection
@endcan

@section('content')
    <div data-ajax-list class="overflow-hidden rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10">

        <form method="GET" action="{{ route('permissions.index') }}" class="flex flex-col gap-3 border-b border-neutral-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                {{ number_format($permissions->total()) }} {{ Str::plural('permission', $permissions->total()) }}
            </p>
            <div class="flex gap-2">
                <div class="relative">
                    <svg class="pointer-events-none absolute top-1/2 left-3 size-4 -tranneutral-y-1/2 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search name or slug…"
                           class="w-full rounded-md border-0 py-2 pr-3 pl-9 text-sm ring-1 ring-neutral-300 ring-inset placeholder:text-neutral-400 focus:ring-2 focus:ring-accent-600 focus:outline-none sm:w-64 dark:bg-white/5 dark:ring-white/10">
                </div>
                @if ($search !== '')
                    <a href="{{ route('permissions.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-white/10 dark:hover:text-white">Reset</a>
                @endif
            </div>
        </form>

        @if ($permissions->isEmpty())
            <div class="px-6 py-16 text-center">
                <svg class="mx-auto size-10 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg>
                <h3 class="mt-3 text-sm font-semibold text-neutral-900 dark:text-white">{{ $search !== '' ? "No permissions match “{$search}”" : 'No permissions yet' }}</h3>
                @if ($search !== '')
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"><a href="{{ route('permissions.index') }}" class="font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">Show all permissions</a></p>
                @else
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Run <code class="rounded bg-neutral-100 px-1 text-xs dark:bg-white/10">php artisan db:seed --class=PermissionSeeder</code> to add the built-in ones.</p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-white/10">
                    <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
                        <tr>
                            <th class="min-w-56 px-4 py-3 first:pl-6">Permission</th>
                            <th class="px-4 py-3">Slug</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3 text-right">Roles</th>
                            <th class="px-4 py-3 last:pr-6"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
                        @foreach ($permissions as $permission)
                            <tr class="hover:bg-neutral-50/60 dark:hover:bg-white/5">
                                <td class="px-4 py-3 font-medium text-neutral-900 first:pl-6 dark:text-white">{{ $permission->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap"><code class="rounded bg-neutral-100 px-1.5 py-0.5 font-mono text-xs text-neutral-700 dark:bg-white/10 dark:text-neutral-300">{{ $permission->slug }}</code></td>
                                <td class="px-4 py-3">
                                    @if ($permission->isSystem())
                                        <span class="inline-flex items-center gap-1 rounded bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600 ring-1 ring-neutral-500/20 ring-inset dark:bg-white/5 dark:text-neutral-400 dark:ring-white/10" title="Used by the app; the slug can’t be changed and it can’t be deleted">
                                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                            System
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded bg-accent-50 px-2 py-0.5 text-xs font-medium text-accent-700 ring-1 ring-accent-600/20 ring-inset dark:bg-accent-500/10 dark:text-accent-400 dark:ring-accent-500/20">Custom</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-neutral-600 tabular-nums dark:text-neutral-300">{{ $permission->roles_count }}</td>
                                <td class="px-4 py-3 last:pr-6">
                                    <div class="flex items-center justify-end gap-1">
                                        @can('permissions.update')
                                            <a href="{{ route('permissions.edit', $permission) }}" title="Edit" class="rounded-md p-1.5 text-neutral-400 hover:bg-accent-50 hover:text-accent-600 dark:hover:bg-accent-500/10 dark:hover:text-accent-400">
                                                <span class="sr-only">Edit {{ $permission->slug }}</span>
                                                <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                            </a>
                                        @endcan
                                        @can('permissions.delete')
                                            @if ($permission->isSystem())
                                                <span class="cursor-not-allowed rounded-md p-1.5 text-neutral-300 dark:text-neutral-600" title="System permissions can’t be deleted">
                                                    <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                </span>
                                            @else
                                                <form method="POST" action="{{ route('permissions.destroy', $permission) }}"
                                                      data-confirm="Delete “{{ $permission->slug }}”?{{ $permission->roles_count ? ' It will be removed from ' . $permission->roles_count . ' ' . Str::plural('role', $permission->roles_count) . '.' : '' }} This can’t be undone.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Delete" class="rounded-md p-1.5 text-neutral-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-400">
                                                        <span class="sr-only">Delete {{ $permission->slug }}</span>
                                                        <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($permissions->hasPages())
                <div class="border-t border-neutral-200 px-6 py-3 dark:border-white/10">
                    {{ $permissions->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
