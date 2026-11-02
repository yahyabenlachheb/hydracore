@extends('layouts.app')

@section('title', "Edit {$customer->email}")
@section('header', $customer->full_name)
@section('subheader', $customer->email)

@section('breadcrumbs')
    <x-crumbs :items="['Customers' => route('customers.index'), $customer->full_name => route('customers.show', $customer), 'Edit' => null]" />
@endsection

@section('content')
    <x-form-page :action="route('customers.update', $customer)" :cancel="route('customers.show', $customer)" submit="Save changes" method="PUT">
        @include('customers._form')
    </x-form-page>
@endsection
