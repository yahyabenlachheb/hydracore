@extends('layouts.app')

@section('title', 'New plan')
@section('header', 'New plan')
@section('subheader', 'Added to the brand you’re working in.')

@section('breadcrumbs')
    <x-crumbs :items="['Plans' => route('plans.index'), 'New' => null]" />
@endsection

@section('content')
    <x-form-page :action="route('plans.store')" :cancel="route('plans.index')" submit="Create plan">
        @include('plans._form')
    </x-form-page>
@endsection
