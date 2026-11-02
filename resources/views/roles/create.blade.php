@extends('layouts.app')

@section('title', 'New role')
@section('header', 'New role')
@section('subheader', 'Group permissions into a role you can assign to users.')

@section('breadcrumbs')
    <a href="{{ route('home') }}" class="hover:text-neutral-900 dark:hover:text-white">Home</a>
    <svg class="size-4 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <a href="{{ route('roles.index') }}" class="hover:text-neutral-900 dark:hover:text-white">Roles</a>
    <svg class="size-4 text-neutral-300 dark:text-neutral-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    <span class="font-medium text-neutral-900 dark:text-white">New</span>
@endsection

@section('content')
    <form method="POST" action="{{ route('roles.store') }}"
          class="rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10">
        @csrf

        @include('roles._form')

        <div class="flex justify-end gap-3 rounded-b-md border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-white/10 dark:bg-white/5">
            <a href="{{ route('roles.index') }}" class="rounded-md px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-white/10">Cancel</a>
            <button type="submit" class="rounded-md bg-accent-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-accent-500">Create role</button>
        </div>
    </form>
@endsection
