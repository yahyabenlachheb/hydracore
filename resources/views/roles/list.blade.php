@extends('layouts.app')

@section('title', 'Roles')
@section('header', 'Roles')
@section('subheader', 'Bundles of permissions you assign to users.')

@section('breadcrumbs')
    <a href="{{ route('home') }}" class="hover:text-neutral-900 dark:hover:text-white">Home</a>
    <svg class="size-4 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="font-medium text-neutral-900 dark:text-white">Roles</span>
@endsection

@can('roles.create')
    @section('actions')
        <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-2 rounded-md bg-accent-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-accent-500">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New role
        </a>
    @endsection
@endcan

@section('content')
    <div data-ajax-list class="overflow-hidden rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10">

        <form method="GET" action="{{ route('roles.index') }}" class="flex flex-col gap-3 border-b border-neutral-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                {{ number_format($roles->total()) }} {{ Str::plural('role', $roles->total()) }}
            </p>
            <div class="flex gap-2">
                <div class="relative">
                    <svg class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search roles…"
                           class="w-full rounded-md border-0 py-2 pr-3 pl-9 text-sm ring-1 ring-neutral-300 ring-inset placeholder:text-neutral-400 focus:ring-2 focus:ring-accent-600 focus:outline-none sm:w-64 dark:bg-white/5 dark:ring-white/10">
                </div>
                @if ($search !== '')
                    <a href="{{ route('roles.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-white/10 dark:hover:text-white">Reset</a>
                @endif
            </div>
        </form>

        @if ($roles->isEmpty())
            <div class="px-6 py-16 text-center">
                <svg class="mx-auto size-10 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                @if ($search !== '')
                    <h3 class="mt-3 text-sm font-semibold text-neutral-900 dark:text-white">No roles match “{{ $search }}”</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"><a href="{{ route('roles.index') }}" class="font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">Show all roles</a></p>
                @else
                    <h3 class="mt-3 text-sm font-semibold text-neutral-900 dark:text-white">No roles yet</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Users need a role with permissions before they can sign in.</p>
                    @can('roles.create')
                        <a href="{{ route('roles.create') }}" class="mt-4 inline-flex rounded-md bg-accent-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-accent-500">New role</a>
                    @endcan
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-white/10">
                    <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
                        <tr>
                            <th class="min-w-64 px-4 py-3 first:pl-6">Role</th>
                            <th class="px-4 py-3 text-right">Permissions</th>
                            <th class="px-4 py-3 text-right">Users</th>
                            <th class="px-4 py-3">Created</th>
                            <th class="px-4 py-3 last:pr-6"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
                        @foreach ($roles as $role)
                            <tr class="hover:bg-neutral-50/60 dark:hover:bg-white/5">
                                <td class="px-4 py-4 first:pl-6">
                                    <p class="font-medium text-neutral-900 dark:text-white">{{ $role->name }}</p>
                                    @if ($role->description)
                                        <p class="max-w-md truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $role->description }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right tabular-nums">
                                    @if ($role->permissions_count === 0)
                                        <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400" title="Users with only this role can’t sign in">
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                                            0
                                        </span>
                                    @else
                                        <span class="text-neutral-600 dark:text-neutral-300">{{ $role->permissions_count }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right text-neutral-600 tabular-nums dark:text-neutral-300">
                                    @can('users.view')
                                        <a href="{{ route('users.index', ['role' => $role->id]) }}" class="hover:text-accent-600 dark:hover:text-accent-400">{{ $role->users_count }}</a>
                                    @else
                                        {{ $role->users_count }}
                                    @endcan
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-neutral-500 dark:text-neutral-400">{{ $role->created_at->format('M j, Y') }}</td>
                                <td class="px-4 py-4 last:pr-6">
                                    <div class="flex items-center justify-end gap-1">
                                        @can('roles.update')
                                            <a href="{{ route('roles.edit', $role) }}" title="Edit" class="rounded-md p-1.5 text-neutral-400 hover:bg-accent-50 hover:text-accent-600 dark:hover:bg-accent-500/10 dark:hover:text-accent-400">
                                                <span class="sr-only">Edit {{ $role->name }}</span>
                                                <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                            </a>
                                        @endcan
                                        @can('roles.delete')
                                            <form method="POST" action="{{ route('roles.destroy', $role) }}"
                                                  data-confirm="Delete the “{{ $role->name }}” role?{{ $role->users_count ? ' ' . $role->users_count . ' ' . Str::plural('user', $role->users_count) . ' will lose it, and may lose access to HydraCore.' : '' }} This can’t be undone.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete" class="rounded-md p-1.5 text-neutral-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-400">
                                                    <span class="sr-only">Delete {{ $role->name }}</span>
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

            @if ($roles->hasPages())
                <div class="border-t border-neutral-200 px-6 py-3 dark:border-white/10">
                    {{ $roles->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
