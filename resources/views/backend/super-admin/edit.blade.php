@extends('backend.layouts.app')

@section('title', 'Modifica Super Admin')

@section('content')
    <h1 class="h3 mb-4">Modifica Super Admin</h1>

    <form method="POST" action="{{ route('backend.super-admin.update', $user) }}">
        @csrf
        @method('PUT')
        @include('backend.super-admin._form')

        <button type="submit" class="btn btn-primary">Salva</button>
    </form>
@endsection
