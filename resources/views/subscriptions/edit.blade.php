@extends('layouts.app')

@section('title', "Edit subscription #{$subscription->id}")
@section('header', "Subscription #{$subscription->id}")
@section('subheader', 'To extend a paid line, create a renewal instead, so the payment is on record.')

@section('breadcrumbs')
    <x-crumbs :items="['Subscriptions' => route('subscriptions.index'), '#' . $subscription->id => route('subscriptions.show', $subscription), 'Edit' => null]" />
@endsection

@section('content')
    <x-form-page :action="route('subscriptions.update', $subscription)" :cancel="route('subscriptions.show', $subscription)" submit="Save changes" method="PUT">
        @include('subscriptions._form')
    </x-form-page>
@endsection
