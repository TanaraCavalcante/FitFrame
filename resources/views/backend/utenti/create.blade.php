@extends('backend.layouts.app')

@section('title', 'Nuovo Utente')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><a href="{{ route('backend.utenti.index') }}" class="text-decoration-none hover-accent fs-7">Utenti</a></li>
        <li class="breadcrumb-item"><span class="fs-7">Nuovo Utente</span></li>
@endsection

@section('content')
    <h1 class="h3 mb-4">Nuovo Utente</h1>

    <form method="POST" action="{{ route('backend.utenti.store') }}">
        @csrf
        @include('backend.utenti._form')

        <button type="submit" class="btn btn-primary">Crea</button>
    </form>
@endsection
