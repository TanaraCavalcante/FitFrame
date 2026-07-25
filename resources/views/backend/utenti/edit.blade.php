@extends('backend.layouts.app')

@section('title', 'Modifica Utente')

@section('content')
    <h1 class="h3 mb-4">Modifica Utente</h1>

    <form method="POST" action="{{ route('backend.utenti.update', $user) }}">
        @csrf
        @method('PUT')
        @include('backend.utenti._form')

        <button type="submit" class="btn btn-primary">Salva</button>
    </form>
@endsection
