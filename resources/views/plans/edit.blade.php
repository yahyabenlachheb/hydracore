@extends('layouts.app')

@section('title', "Edit {$plan->name}")
@section('header', $plan->name)
@section('subheader', 'Changes apply to new orders only.')

@section('breadcrumbs')
    <x-crumbs :items="['Plans' => route('plans.index'), 'Edit' => null]" />
@endsection

@section('content')
    <x-form-page :action="route('plans.update', $plan)" :cancel="route('plans.index')" submit="Save changes" method="PUT">
        @include('plans._form')
    </x-form-page>
@endsection
