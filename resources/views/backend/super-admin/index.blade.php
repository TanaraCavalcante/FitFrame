@extends('backend.layouts.app')

@section('title', 'Super Admin')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><a href="{{ route('backend.super-admin.index') }}" class="text-decoration-none hover-accent fs-7">Super Admin</a></li>
        <li class="breadcrumb-item"><span class="fs-7">Elenco</span></li>
@endsection

@section('content')
    <div class="card border-0 mb-3 py-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Super Admin</h1>
                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Indietro</a>
                    <a href="{{ route('backend.super-admin.create') }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-plus me-1"></i>Nuovo Super Admin</a>
                </div>
            </div>
        </div>
    </div>

    @php $filtriAttivi = $search !== '' ? 1 : 0; @endphp

    <div class="card border-0 p-3">
        <x-filter-toggle :count="$filtriAttivi" target="super-admin-filtri">
            <form method="GET" action="{{ route('backend.super-admin.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-gray-muted">Cerca</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Nome, cognome, email">
                </div>
                <div class="col-md-auto">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>Cerca</button>
                        @if ($filtriAttivi)
                            <a href="{{ route('backend.super-admin.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-xmark me-1"></i>Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </x-filter-toggle>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }} {{ $user->surname }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="text-end">
                                <a href="{{ route('backend.super-admin.edit', $user) }}" class="btn btn-sm btn-outline-warning" title="Modifica"><i class="fa-solid fa-pen-to-square"></i></a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('backend.super-admin.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Eliminare questo super admin?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Elimina"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-gray-muted py-5">
                                <i class="fa-solid fa-magnifying-glass fa-2x d-block mb-2" aria-hidden="true"></i>
                                Nessun risultato trovato.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
