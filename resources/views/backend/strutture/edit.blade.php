@extends('backend.layouts.app')

@section('title', 'Modifica Struttura')

@section('content')
    <h1 class="h3 mb-4">Modifica Struttura</h1>

    <form method="POST" action="{{ route('backend.strutture.update', $gym) }}">
        @csrf
        @method('PUT')
        @include('backend.strutture._form')

        <button type="submit" class="btn btn-primary">Salva</button>
    </form>
@endsection
