@extends('backend.layouts.app')

@section('title', 'Utenti')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><a href="{{ route('backend.utenti.index') }}" class="text-decoration-none hover-accent fs-7">Utenti</a></li>
        <li class="breadcrumb-item"><span class="fs-7">Elenco</span></li>
@endsection


@section('content')
    <div class="card border-0 mb-3 py-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Utenti</h1>
                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Indietro</a>
                    <a href="{{ route('backend.utenti.create') }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-plus me-1"></i>Nuovo Utente</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 p-3">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                            <td>{{ $user->name }} {{ $user->surname }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->gym?->name }}</td>
                            <td class="text-end">
                                @canBeImpersonated($user)
                                    <a href="{{ route('backend.impersonate', $user->id) }}" class="btn btn-sm btn-outline-info" title="Impersona"><i class="fa-solid fa-user-secret"></i></a>
                                @endCanBeImpersonated
                                @if (auth()->user()->isSuperAdmin())
                                    <a href="#" class="btn btn-sm btn-outline-secondary" title="Reset password"><i class="fa-solid fa-lock"></i></a>
                                @endif
                                <a href="{{ route('backend.utenti.edit', $user) }}" class="btn btn-sm btn-outline-warning" title="Modifica"><i class="fa-solid fa-pen-to-square"></i></a>
                                <form method="POST" action="{{ route('backend.utenti.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Eliminare questo utente?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Elimina"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
