@extends('layouts.app')

@section('title', 'New pack')
@section('header', 'New pack')

@section('breadcrumbs')
    <x-crumbs :items="['Packs' => route('packs.index'), 'New' => null]" />
@endsection

@section('content')
    <x-form-page :action="route('packs.store')" :cancel="route('packs.index')" submit="Create pack">
        @include('packs._form')
    </x-form-page>
@endsection
