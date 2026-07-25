@extends('backend.layouts.app')

@section('title', 'Strutture')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Strutture</h1>
        <a href="{{ route('backend.strutture.create') }}" class="btn btn-primary">Nuova Struttura</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Tema</th>
                <th>Dominio</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($gyms as $gym)
                <tr>
                    <td>{{ $gym->name }}</td>
                    <td>{{ $gym->slug }}</td>
                    <td>{{ $gym->domains->first()?->domain }}</td>
                    <td class="text-end">
                        <a href="{{ route('backend.strutture.edit', $gym) }}" class="btn btn-sm btn-outline-secondary">Modifica</a>
                        <form method="POST" action="{{ route('backend.strutture.destroy', $gym) }}" class="d-inline" onsubmit="return confirm('Eliminare questa struttura?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Elimina</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
