@extends('layouts.app')

@section('title', "Edit {$pack->name}")
@section('header', $pack->name)

@section('breadcrumbs')
    <x-crumbs :items="['Packs' => route('packs.index'), 'Edit' => null]" />
@endsection

@section('content')
    <x-form-page :action="route('packs.update', $pack)" :cancel="route('packs.index')" submit="Save changes" method="PUT">
        @include('packs._form')
    </x-form-page>
@endsection
