@extends('layouts.app')

@section('title', 'Audit Logs')
@section('header', 'Audit Logs')
@section('subheader', 'Who changed what, and when.')

@section('breadcrumbs')
    <a href="{{ route('home') }}" class="hover:text-neutral-900 dark:hover:text-white">Home</a>
    <svg class="size-4 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="font-medium text-neutral-900 dark:text-white">Audit Logs</span>
@endsection

@section('content')
    @php
        $filtered = $filters['q'] !== '' || $filters['user'] || $filters['table'] || $filters['action'] || $filters['from'] || $filters['to'];
        $field = 'rounded-md border-0 py-2 text-sm ring-1 ring-neutral-300 ring-inset focus:ring-2 focus:ring-accent-600 focus:outline-none dark:bg-white/5 dark:ring-white/10';
        $actions = [
            'insert' => ['Created', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20'],
            'update' => ['Updated', 'bg-accent-50 text-accent-700 ring-accent-600/20 dark:bg-accent-500/10 dark:text-accent-400 dark:ring-accent-500/20'],
            'delete' => ['Deleted', 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20'],
        ];
    @endphp

    <div data-ajax-list class="overflow-hidden rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10">

        {{-- Filters --}}
        <form method="GET" action="{{ route('audit-logs.index') }}" class="space-y-3 border-b border-neutral-200 px-6 py-4 dark:border-white/10">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative min-w-56 flex-1">
                    <svg class="pointer-events-none absolute top-1/2 left-3 size-4 -tranneutral-y-1/2 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search descriptions…" class="w-full pr-3 pl-9 placeholder:text-neutral-400 {{ $field }}">
                </div>
                <select name="user" onchange="this.form.requestSubmit()" class="pl-3 {{ $field }}">
                    <option value="">All users</option>
                    <option value="system" @selected($filters['user'] === 'system')>System</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) $filters['user'] === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
                <select name="table" onchange="this.form.requestSubmit()" class="pl-3 {{ $field }}">
                    <option value="">All areas</option>
                    @foreach ($tables as $table)
                        <option value="{{ $table }}" @selected($filters['table'] === $table)>{{ Str::headline($table) }}</option>
                    @endforeach
                </select>
                <select name="action" onchange="this.form.requestSubmit()" class="pl-3 {{ $field }}">
                    <option value="">All actions</option>
                    @foreach ($actions as $value => [$label])
                        <option value="{{ $value }}" @selected($filters['action'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400">
                <label class="flex items-center gap-2">From <input type="date" name="from" value="{{ $filters['from']?->toDateString() }}" onchange="this.form.requestSubmit()" class="px-2.5 {{ $field }}"></label>
                <label class="flex items-center gap-2">to <input type="date" name="to" value="{{ $filters['to']?->toDateString() }}" onchange="this.form.requestSubmit()" class="px-2.5 {{ $field }}"></label>
                <span class="flex-1"></span>
                <span>{{ number_format($logs->total()) }} {{ Str::plural('entry', $logs->total()) }}</span>
                @if ($filtered)
                    <a href="{{ route('audit-logs.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-white/10 dark:hover:text-white">Reset</a>
                @endif
            </div>
        </form>

        @if ($logs->isEmpty())
            <div class="px-6 py-16 text-center">
                <svg class="mx-auto size-10 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                <h3 class="mt-3 text-sm font-semibold text-neutral-900 dark:text-white">{{ $filtered ? 'No entries match your filters' : 'Nothing has been logged yet' }}</h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    @if ($filtered)
                        <a href="{{ route('audit-logs.index') }}" class="font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">Reset the filters</a>
                    @else
                        Changes to brands, users, roles and permissions will appear here.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-white/10">
                    <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
                        <tr>
                            <th class="px-4 py-3 first:pl-6">When</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Action</th>
                            <th class="min-w-72 px-4 py-3">What happened</th>
                            <th class="px-4 py-3 text-right">Fields</th>
                            <th class="px-4 py-3 last:pr-6"><span class="sr-only">View</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
                        @foreach ($logs as $log)
                            @php
                                [$actionLabel, $actionClasses] = $actions[$log->action];
                                $fieldCount = count(array_unique([...array_keys($log->old_values ?? []), ...array_keys($log->new_values ?? [])]));
                            @endphp
                            <tr class="hover:bg-neutral-50/60 dark:hover:bg-white/5">
                                <td class="px-4 py-3 whitespace-nowrap first:pl-6" title="{{ $log->created_at->toDayDateTimeString() }}">
                                    <p class="text-neutral-900 dark:text-white">{{ $log->created_at->diffForHumans() }}</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $log->created_at->format('M j, Y · H:i') }}</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($log->user)
                                        <span class="flex items-center gap-2">
                                            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-accent-500/15 text-xs font-semibold text-accent-700 dark:text-accent-300">{{ Str::upper(Str::substr($log->user->name, 0, 1)) }}</span>
                                            <span class="text-neutral-700 dark:text-neutral-300">{{ $log->user->name }}</span>
                                        </span>
                                    @else
                                        <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400">
                                            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-neutral-100 dark:bg-white/10">
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m6.75 7.5 3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0 0 21 18V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                            </span>
                                            System
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $actionClasses }}">{{ $actionLabel }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('audit-logs.show', $log) }}" class="font-medium text-neutral-900 hover:text-accent-600 dark:text-white dark:hover:text-accent-400">{{ $log->description }}</a>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ Str::headline(Str::singular($log->table_name)) }} #{{ $log->record_id }}</p>
                                </td>
                                <td class="px-4 py-3 text-right text-neutral-600 tabular-nums dark:text-neutral-300">{{ $fieldCount }}</td>
                                <td class="px-4 py-3 last:pr-6">
                                    <a href="{{ route('audit-logs.show', $log) }}" title="View details" class="flex justify-end rounded-md p-1.5 text-neutral-400 hover:text-accent-600 dark:hover:text-accent-400">
                                        <span class="sr-only">View details</span>
                                        <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="border-t border-neutral-200 px-6 py-3 dark:border-white/10">
                    {{ $logs->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
