@extends('layouts.app')

@section('title', 'Users')
@section('header', 'Users')
@section('subheader', 'People who can sign in to HydraCore, and what they can access.')

@section('breadcrumbs')
    <a href="{{ route('home') }}" class="hover:text-neutral-900 dark:hover:text-white">Home</a>
    <svg class="size-4 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="font-medium text-neutral-900 dark:text-white">Users</span>
@endsection

@can('users.create')
    @section('actions')
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 rounded-md bg-accent-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-accent-500">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New user
        </a>
    @endsection
@endcan

@section('content')
    @php
        $filtered = $search !== '' || $status || $roleId;
    @endphp

    <div data-ajax-list class="overflow-hidden rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10">

        {{-- Filters --}}
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-col gap-3 border-b border-neutral-200 px-6 py-4 lg:flex-row lg:items-center lg:justify-between dark:border-white/10">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                {{ number_format($users->total()) }} {{ Str::plural('user', $users->total()) }}
            </p>
            <div class="flex flex-wrap gap-2">
                <div class="relative">
                    <svg class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search name, username or email…"
                           class="w-full rounded-md border-0 py-2 pr-3 pl-9 text-sm ring-1 ring-neutral-300 ring-inset placeholder:text-neutral-400 focus:ring-2 focus:ring-accent-600 focus:outline-none sm:w-72 dark:bg-white/5 dark:ring-white/10">
                </div>
                <select name="role" onchange="this.form.requestSubmit()"
                        class="rounded-md border-0 py-2 pl-3 text-sm ring-1 ring-neutral-300 ring-inset focus:ring-2 focus:ring-accent-600 focus:outline-none dark:bg-white/5 dark:ring-white/10">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected($roleId === $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.form.requestSubmit()"
                        class="rounded-md border-0 py-2 pl-3 text-sm ring-1 ring-neutral-300 ring-inset focus:ring-2 focus:ring-accent-600 focus:outline-none dark:bg-white/5 dark:ring-white/10">
                    <option value="">All statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    <option value="super-admin" @selected($status === 'super-admin')>Super admins</option>
                </select>
                @if ($filtered)
                    <a href="{{ route('users.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-white/10 dark:hover:text-white">Reset</a>
                @endif
            </div>
        </form>

        @if ($users->isEmpty())
            <div class="px-6 py-16 text-center">
                <svg class="mx-auto size-10 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                <h3 class="mt-3 text-sm font-semibold text-neutral-900 dark:text-white">No users match your filters</h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Try a different search or <a href="{{ route('users.index') }}" class="font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">reset the filters</a>.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-white/10">
                    <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
                        <tr>
                            <th class="min-w-64 px-4 py-3 first:pl-6">User</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Roles</th>
                            <th class="px-4 py-3 text-right">Brands</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 last:pr-6"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
                        @foreach ($users as $listed)
                            @php
                                $isSelf = $listed->is(auth()->user());
                                $manageable = ! $listed->is_super_admin || auth()->user()->isSuperAdmin();
                            @endphp
                            <tr class="hover:bg-neutral-50/60 dark:hover:bg-white/5">
                                <td class="px-4 py-4 first:pl-6">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-accent-500/15 text-sm font-semibold text-accent-700 dark:text-accent-300">
                                            {{ Str::upper(Str::substr($listed->name, 0, 1)) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="flex items-center gap-2 font-medium text-neutral-900 dark:text-white">
                                                {{ $listed->name }}
                                                @if ($isSelf)
                                                    <span class="rounded bg-neutral-100 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/10 dark:text-neutral-400">You</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ '@' . $listed->username }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-neutral-600 dark:text-neutral-300">{{ $listed->email }}</td>
                                <td class="px-4 py-4">
                                    @if ($listed->is_super_admin)
                                        <span class="inline-flex items-center gap-1 rounded bg-accent-50 px-2 py-0.5 text-xs font-medium text-accent-700 ring-1 ring-accent-600/20 ring-inset dark:bg-accent-500/10 dark:text-accent-400 dark:ring-accent-500/20">
                                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                                            Super admin
                                        </span>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @forelse ($listed->roles->take(3) as $role)
                                                <span class="rounded bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-700 dark:bg-white/10 dark:text-neutral-300">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-xs text-amber-600 dark:text-amber-400">No role</span>
                                            @endforelse
                                            @if ($listed->roles->count() > 3)
                                                <span class="text-xs text-neutral-500 dark:text-neutral-400">+{{ $listed->roles->count() - 3 }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right tabular-nums">
                                    @if ($listed->is_super_admin)
                                        <span class="text-neutral-500 dark:text-neutral-400">All</span>
                                    @elseif ($listed->brands_count === 0)
                                        <span class="text-amber-600 dark:text-amber-400">0</span>
                                    @else
                                        <span class="text-neutral-600 dark:text-neutral-300">{{ $listed->brands_count }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if ($listed->is_active)
                                        <span class="inline-flex items-center rounded bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20 ring-inset dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">Active</span>
                                    @else
                                        <span class="inline-flex items-center rounded bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600 ring-1 ring-neutral-500/20 ring-inset dark:bg-white/5 dark:text-neutral-400 dark:ring-white/10">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 last:pr-6">
                                    <div class="flex items-center justify-end gap-1">
                                        @if ($manageable)
                                            @can('users.update')
                                                <a href="{{ route('users.edit', $listed) }}" title="Edit" class="rounded-md p-1.5 text-neutral-400 hover:bg-accent-50 hover:text-accent-600 dark:hover:bg-accent-500/10 dark:hover:text-accent-400">
                                                    <span class="sr-only">Edit {{ $listed->name }}</span>
                                                    <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                                </a>
                                            @endcan
                                            @if (! $isSelf)
                                                @can('users.delete')
                                                    <form method="POST" action="{{ route('users.destroy', $listed) }}" data-confirm="Delete “{{ $listed->name }}”? They will no longer be able to sign in. This can’t be undone.">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" title="Delete" class="rounded-md p-1.5 text-neutral-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-400">
                                                            <span class="sr-only">Delete {{ $listed->name }}</span>
                                                            <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-neutral-200 px-6 py-3 dark:border-white/10">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
