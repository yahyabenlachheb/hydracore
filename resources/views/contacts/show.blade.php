@extends('layouts.app')

@section('title', $contact->subject ?: 'Message')
@section('header', $contact->subject ?: '(no subject)')
@section('subheader', 'From ' . $contact->full_name . ' · ' . $contact->created_at?->format('M j, Y H:i') . ' UTC')

@section('breadcrumbs')
    <x-crumbs :items="['Contact messages' => route('contacts.index'), 'Message' => null]" />
@endsection

@section('actions')
    <x-status :status="$contact->status" class="px-2.5 py-1 text-sm" />
    <a href="mailto:{{ rawurlencode($contact->email) }}?subject={{ rawurlencode('Re: ' . ($contact->subject ?: 'your message')) }}" class="rounded-md bg-accent-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-accent-500">Reply by email</a>
@endsection

@section('content')
    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="rounded-md bg-white px-6 py-5 text-sm whitespace-pre-line text-neutral-800 shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:text-neutral-200 dark:ring-white/10">{{ $contact->message }}</section>

        <div class="space-y-6">
            <x-details title="Sender" :rows="[
                'Name' => $contact->full_name,
                'Email' => $contact->email,
                'IP address' => $contact->ip_address,
                'Customer' => $contact->customer?->full_name ?? 'Not a customer',
            ]">
                @if ($contact->customer && ! $contact->customer->trashed())
                    @can('customers.view')
                        <div class="border-t border-neutral-200 px-5 py-3 dark:border-white/10">
                            <a href="{{ route('customers.show', $contact->customer) }}" class="text-sm font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">Open the customer →</a>
                        </div>
                    @endcan
                @endif
            </x-details>

            <div class="flex flex-wrap gap-2">
                @can('contacts.update')
                    @foreach (['replied' => 'Mark replied', 'closed' => 'Close', 'new' => 'Mark unread'] as $status => $label)
                        @if ($contact->status !== $status)
                            <x-action-button :action="route('contacts.status', $contact)" :fields="['status' => $status]">{{ $label }}</x-action-button>
                        @endif
                    @endforeach
                @endcan
                @can('contacts.delete')
                    <form method="POST" action="{{ route('contacts.destroy', $contact) }}" data-confirm="Delete this message? This can’t be undone.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-md px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">Delete</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
@endsection
