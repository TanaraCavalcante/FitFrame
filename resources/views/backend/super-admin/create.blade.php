@extends('backend.layouts.app')

@section('title', 'Nuovo Super Admin')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><a href="{{ route('backend.super-admin.index') }}" class="text-decoration-none hover-accent fs-7">Super Admin</a></li>
        <li class="breadcrumb-item"><span class="fs-7">Nuovo Super Admin</span></li>
@endsection

@section('content')
    <div class="card border-0 mb-3 py-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Nuovo Super Admin</h1>
                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Indietro</a>
                    <button type="submit" form="super-admin-form" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-floppy-disk me-1"></i>Salva</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0">
        <div class="card-body">
            <form id="super-admin-form" method="POST" action="{{ route('backend.super-admin.store') }}">
                @csrf
                @include('backend.super-admin._form')

            </form>
        </div>
    </div>
@endsection
