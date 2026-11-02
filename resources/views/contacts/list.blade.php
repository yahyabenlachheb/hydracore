@extends('layouts.app')

@section('title', 'Contact messages')
@section('header', 'Contact messages')
@section('subheader', 'What people sent through this brand’s contact form.')

@section('breadcrumbs')
    <x-crumbs :items="['Contact messages' => null]" />
@endsection

@section('content')
    <x-list-card :items="$contacts" noun="message" :action="route('contacts.index')" :filtered="$filters['q'] !== '' || $filters['status']" empty-text="Messages from your contact form appear here.">
        <x-slot:filters>
            <x-filter-search :value="$filters['q']" placeholder="Name, email or subject…" />
            <x-filter-select name="status" placeholder="All statuses" :options="App\Models\Contact::STATUSES" :selected="$filters['status']" />
        </x-slot:filters>

        <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
            <tr>
                <th class="min-w-56 px-4 py-3 first:pl-6">From</th>
                <th class="min-w-64 px-4 py-3">Message</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 last:pr-6">Received</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
            @foreach ($contacts as $contact)
                <tr class="hover:bg-neutral-50/60 dark:hover:bg-white/5">
                    <td class="px-4 py-3 first:pl-6">
                        <p @class(['truncate text-neutral-900 dark:text-white', 'font-semibold' => $contact->status === 'new'])>{{ $contact->full_name }}</p>
                        <p class="truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $contact->email }}</p>
                    </td>
                    <td class="max-w-md px-4 py-3">
                        <a href="{{ route('contacts.show', $contact) }}" @class(['block truncate text-neutral-900 hover:text-accent-600 dark:text-white dark:hover:text-accent-400', 'font-semibold' => $contact->status === 'new'])>{{ $contact->subject ?: '(no subject)' }}</a>
                        <p class="truncate text-xs text-neutral-500 dark:text-neutral-400">{{ Str::limit($contact->message, 100) }}</p>
                    </td>
                    <td class="px-4 py-3"><x-status :status="$contact->status" /></td>
                    <td class="px-4 py-3 whitespace-nowrap text-neutral-500 last:pr-6 dark:text-neutral-400" title="{{ $contact->created_at?->format('M j, Y H:i') }} UTC">{{ $contact->created_at?->diffForHumans() }}</td>
                </tr>
            @endforeach
        </tbody>
    </x-list-card>
@endsection
