@extends('backend.layouts.app')

@section('title', 'Modifica Struttura')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><a href="{{ route('backend.strutture.index') }}" class="text-decoration-none hover-accent fs-7">Strutture</a></li>
        <li class="breadcrumb-item"><span class="fs-7">Modifica Struttura</span></li>
@endsection

@section('content')
    <h1 class="h3 mb-4">Modifica Struttura</h1>

    <form method="POST" action="{{ route('backend.strutture.update', $gym) }}">
        @csrf
        @method('PUT')
        @include('backend.strutture._form')

        <button type="submit" class="btn btn-primary">Salva</button>
    </form>
@endsection
