@extends('backend.layouts.app')

@section('title', 'Nuovo Super Admin')

@section('content')
    <h1 class="h3 mb-4">Nuovo Super Admin</h1>

    <form method="POST" action="{{ route('backend.super-admin.store') }}">
        @csrf
        @include('backend.super-admin._form')

        <button type="submit" class="btn btn-primary">Crea</button>
    </form>
@endsection
