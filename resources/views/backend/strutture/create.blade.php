@extends('backend.layouts.app')

@section('title', 'Nuova Struttura')

@section('content')
    <h1 class="h3 mb-4">Nuova Struttura</h1>

    <form method="POST" action="{{ route('backend.strutture.store') }}">
        @csrf
        @include('backend.strutture._form')

        <button type="submit" class="btn btn-primary">Crea</button>
    </form>
@endsection
