@extends('layouts.app')

@section('title', "Edit trial #{$trial->id}")
@section('header', "Trial #{$trial->id}")

@section('breadcrumbs')
    <x-crumbs :items="['Trials' => route('trials.index'), '#' . $trial->id => route('trials.show', $trial), 'Edit' => null]" />
@endsection

@section('content')
    <x-form-page :action="route('trials.update', $trial)" :cancel="route('trials.show', $trial)" submit="Save changes" method="PUT">
        @include('trials._form')
    </x-form-page>
@endsection
