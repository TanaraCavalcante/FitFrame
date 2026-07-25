@extends('backend.layouts.app')

@section('title', 'Nuovo Utente')

@section('content')
    <h1 class="h3 mb-4">Nuovo Utente</h1>

    <form method="POST" action="{{ route('backend.utenti.store') }}">
        @csrf
        @include('backend.utenti._form')

        <button type="submit" class="btn btn-primary">Crea</button>
    </form>
@endsection
