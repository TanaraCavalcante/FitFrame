@extends('backend.layouts.app')

@section('title', 'Nuovo Super Admin')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><a href="{{ route('backend.super-admin.index') }}" class="text-decoration-none hover-accent fs-7">Super Admin</a></li>
        <li class="breadcrumb-item"><span class="fs-7">Nuovo Super Admin</span></li>
@endsection

@section('content')
    <h1 class="h3 mb-4">Nuovo Super Admin</h1>

    <form method="POST" action="{{ route('backend.super-admin.store') }}">
        @csrf
        @include('backend.super-admin._form')

        <button type="submit" class="btn btn-primary">Crea</button>
    </form>
@endsection
