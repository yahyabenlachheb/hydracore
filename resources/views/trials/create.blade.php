@extends('layouts.app')

@section('title', 'New trial')
@section('header', 'New trial')
@section('subheader', 'It starts as pending until you approve it.')

@section('breadcrumbs')
    <x-crumbs :items="['Trials' => route('trials.index'), 'New' => null]" />
@endsection

@section('content')
    <x-form-page :action="route('trials.store')" :cancel="route('trials.index')" submit="Create trial">
        @include('trials._form')
    </x-form-page>
@endsection
