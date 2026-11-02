@extends('layouts.app')

@section('title', "Audit log #{$log->id}")
@section('header', $log->description)
@section('subheader', $log->created_at->toDayDateTimeString() . ' · ' . $log->created_at->diffForHumans())

@section('breadcrumbs')
    <a href="{{ route('home') }}" class="hover:text-neutral-900 dark:hover:text-white">Home</a>
    <svg class="size-4 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <a href="{{ route('audit-logs.index') }}" class="hover:text-neutral-900 dark:hover:text-white">Audit Logs</a>
    <svg class="size-4 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="font-medium text-neutral-900 dark:text-white">#{{ $log->id }}</span>
@endsection

@section('actions')
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('audit-logs.index') }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-neutral-700 shadow-sm ring-1 ring-neutral-300 ring-inset hover:bg-neutral-50 dark:bg-white/5 dark:text-neutral-300 dark:ring-white/10 dark:hover:bg-white/10">Back</a>
@endsection

@section('content')
    @php
        $actions = [
            'insert' => ['Created', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20'],
            'update' => ['Updated', 'bg-accent-50 text-accent-700 ring-accent-600/20 dark:bg-accent-500/10 dark:text-accent-400 dark:ring-accent-500/20'],
            'delete' => ['Deleted', 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20'],
        ];
        [$actionLabel, $actionClasses] = $actions[$log->action];
        $isList = fn ($value) => is_array($value) && array_is_list($value);
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Summary --}}
        <div class="rounded-md bg-white p-6 shadow-sm ring-1 ring-neutral-200 lg:order-last dark:bg-neutral-900 dark:ring-white/10">
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Action</dt>
                    <dd class="mt-1"><span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $actionClasses }}">{{ $actionLabel }}</span></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-neutral-500 dark:text-neutral-400">By</dt>
                    <dd class="mt-1 text-neutral-900 dark:text-white">
                        @if ($log->user)
                            {{ $log->user->name }}
                            <span class="block text-xs text-neutral-500 dark:text-neutral-400">{{ '@' . $log->user->username }} · {{ $log->user->email }}</span>
                        @else
                            System <span class="block text-xs text-neutral-500 dark:text-neutral-400">Console command, seeder or background job</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Record</dt>
                    <dd class="mt-1 text-neutral-900 dark:text-white">
                        {{ Str::headline(Str::singular($log->table_name)) }} #{{ $log->record_id }}
                        @if ($recordUrl)
                            <a href="{{ $recordUrl }}" class="mt-0.5 block text-xs font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">Open record →</a>
                        @elseif ($log->action === 'delete')
                            <span class="block text-xs text-neutral-500 dark:text-neutral-400">This record was deleted.</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-neutral-500 dark:text-neutral-400">When</dt>
                    <dd class="mt-1 text-neutral-900 dark:text-white">{{ $log->created_at->format('M j, Y · H:i:s') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Entry</dt>
                    <dd class="mt-1 font-mono text-xs text-neutral-500 dark:text-neutral-400">#{{ $log->id }} · {{ $log->table_name }}</dd>
                </div>
            </dl>
        </div>

        {{-- Changes --}}
        <div class="overflow-hidden rounded-md bg-white shadow-sm ring-1 ring-neutral-200 lg:col-span-2 dark:bg-neutral-900 dark:ring-white/10">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-white/10">
                <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">
                    {{ ['insert' => 'Values when created', 'update' => 'What changed', 'delete' => 'Values before deletion'][$log->action] }}
                </h2>
            </div>

            @if ($changes === [])
                <p class="px-6 py-10 text-center text-sm text-neutral-500 dark:text-neutral-400">No field values were recorded for this entry.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-white/10">
                        <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
                            <tr>
                                <th class="w-44 px-4 py-3 first:pl-6">Field</th>
                                @if ($log->action !== 'insert')<th class="px-4 py-3">Before</th>@endif
                                @if ($log->action !== 'delete')<th class="px-4 py-3 last:pr-6">After</th>@endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 align-top dark:divide-white/10">
                            @foreach ($changes as $field => ['old' => $old, 'new' => $new])
                                @php
                                    $listDiff = $isList($old) || $isList($new);
                                    $removed = $listDiff ? array_diff((array) $old, (array) $new) : [];
                                    $added = $listDiff ? array_diff((array) $new, (array) $old) : [];
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs text-neutral-500 first:pl-6 dark:text-neutral-400">{{ $field }}</td>
                                    @foreach (array_filter(['old' => $log->action !== 'insert', 'new' => $log->action !== 'delete']) as $side => $_)
                                        @php($value = $side === 'old' ? $old : $new)
                                        <td @class([
                                            'px-4 py-3 last:pr-6',
                                            'bg-rose-50/60 dark:bg-rose-500/5' => $log->action === 'update' && $side === 'old' && ! $listDiff,
                                            'bg-emerald-50/60 dark:bg-emerald-500/5' => $log->action === 'update' && $side === 'new' && ! $listDiff,
                                        ])>
                                            @if ($value === null)
                                                <span class="text-neutral-400 dark:text-neutral-500">—</span>
                                            @elseif ($value === \App\Support\Audit::MASK)
                                                <span class="text-neutral-500 dark:text-neutral-400">{{ $value }} <span class="text-xs">(hidden)</span></span>
                                            @elseif (is_bool($value))
                                                <span class="text-neutral-900 dark:text-white">{{ $value ? 'Yes' : 'No' }}</span>
                                            @elseif ($isList($value))
                                                <div class="flex flex-wrap gap-1">
                                                    @forelse ($value as $item)
                                                        <span @class([
                                                            'rounded px-2 py-0.5 text-xs font-medium',
                                                            'bg-rose-50 text-rose-700 line-through dark:bg-rose-500/10 dark:text-rose-400' => $side === 'old' && in_array($item, $removed, true),
                                                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' => $side === 'new' && in_array($item, $added, true),
                                                            'bg-neutral-100 text-neutral-700 dark:bg-white/10 dark:text-neutral-300' => ! (($side === 'old' && in_array($item, $removed, true)) || ($side === 'new' && in_array($item, $added, true))),
                                                        ])>{{ is_scalar($item) ? $item : json_encode($item) }}</span>
                                                    @empty
                                                        <span class="text-neutral-400 dark:text-neutral-500">None</span>
                                                    @endforelse
                                                </div>
                                            @elseif (is_array($value))
                                                <pre class="max-h-48 overflow-auto rounded bg-neutral-50 p-2 font-mono text-xs text-neutral-700 dark:bg-white/5 dark:text-neutral-300">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @else
                                                <span class="break-words whitespace-pre-line text-neutral-900 dark:text-white">{{ Str::limit((string) $value, 1000) }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
