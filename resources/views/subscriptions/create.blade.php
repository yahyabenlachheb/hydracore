@extends('layouts.app')

@section('title', 'New subscription')
@section('header', 'New subscription')
@section('subheader', 'For lines without an order, such as a free line or an import. To sell a line, create an order instead.')

@section('breadcrumbs')
    <x-crumbs :items="['Subscriptions' => route('subscriptions.index'), 'New' => null]" />
@endsection

@section('content')
    <x-form-page :action="route('subscriptions.store')" :cancel="route('subscriptions.index')" submit="Create subscription">
        @include('subscriptions._form')
    </x-form-page>
@endsection
