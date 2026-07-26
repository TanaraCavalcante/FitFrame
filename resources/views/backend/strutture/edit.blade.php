@extends('backend.layouts.app')

@section('title', 'Modifica Struttura')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><a href="{{ route('backend.strutture.index') }}" class="text-decoration-none hover-accent fs-7">Strutture</a></li>
        <li class="breadcrumb-item"><span class="fs-7">Modifica Struttura</span></li>
@endsection

@section('content')
    <div class="card border-0 mb-3 py-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Modifica Struttura</h1>
                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Indietro</a>
                    <button type="submit" form="gym-form" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-floppy-disk me-1"></i>Salva</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0">
        <div class="card-body">
            <form id="gym-form" method="POST" action="{{ route('backend.strutture.update', $gym) }}">
                @csrf
                @method('PUT')
                @include('backend.strutture._form')

            </form>
        </div>
    </div>
@endsection
