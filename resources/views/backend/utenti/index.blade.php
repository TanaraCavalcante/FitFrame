@extends('backend.layouts.app')

@section('title', 'Utenti')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Utenti</h1>
        <a href="{{ route('backend.utenti.create') }}" class="btn btn-primary">Nuovo Utente</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Struttura</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->gym?->name }}</td>
                    <td class="text-end">
                        @canBeImpersonated($user)
                            <a href="{{ route('backend.impersonate', $user->id) }}" class="btn btn-sm btn-outline-primary">Impersona</a>
                        @endCanBeImpersonated
                        <a href="{{ route('backend.utenti.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Modifica</a>
                        <form method="POST" action="{{ route('backend.utenti.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Eliminare questo utente?')">
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
