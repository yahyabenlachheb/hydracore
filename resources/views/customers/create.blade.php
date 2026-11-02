@extends('layouts.app')

@section('title', 'New customer')
@section('header', 'New customer')
@section('subheader', 'Added to the brand you’re working in.')

@section('breadcrumbs')
    <x-crumbs :items="['Customers' => route('customers.index'), 'New' => null]" />
@endsection

@section('content')
    <x-form-page :action="route('customers.store')" :cancel="route('customers.index')" submit="Create customer">
        @include('customers._form')
    </x-form-page>
@endsection
