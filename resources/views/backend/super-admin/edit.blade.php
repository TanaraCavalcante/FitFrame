@extends('backend.layouts.app')

@section('title', 'Modifica Super Admin')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><a href="{{ route('backend.super-admin.index') }}" class="text-decoration-none hover-accent fs-7">Super Admin</a></li>
        <li class="breadcrumb-item"><span class="fs-7">Modifica Super Admin</span></li>
@endsection

@section('content')
    <h1 class="h3 mb-4">Modifica Super Admin</h1>

    <form method="POST" action="{{ route('backend.super-admin.update', $user) }}">
        @csrf
        @method('PUT')
        @include('backend.super-admin._form')

        <button type="submit" class="btn btn-primary">Salva</button>
    </form>
@endsection
