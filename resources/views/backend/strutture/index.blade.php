@extends('backend.layouts.app')

@section('title', 'Strutture')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><a href="{{ route('backend.strutture.index') }}" class="text-decoration-none hover-accent fs-7">Strutture</a></li>
        <li class="breadcrumb-item"><span class="fs-7">Elenco</span></li>
@endsection

@section('content')
    <div class="card border-0 mb-3 py-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Strutture</h1>
                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Indietro</a>
                    <a href="{{ route('backend.strutture.create') }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-plus me-1"></i>Nuova Struttura</a>
                </div>
            </div>
        </div>
    </div>

    @php $filtriAttivi = collect(['search' => $search])->filter(fn ($value) => $value !== '')->count(); @endphp

    <div class="card border-0 p-3">
        <x-filter-toggle :count="$filtriAttivi" target="strutture-filtri">
            <form method="GET" action="{{ route('backend.strutture.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-gray-muted">Cerca</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Nome, tema, dominio">
                </div>
                <div class="col-md-auto">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>Cerca</button>
                        @if ($filtriAttivi)
                            <a href="{{ route('backend.strutture.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-xmark me-1"></i>Reset</a>
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
                        <th>Tema</th>
                        <th>Dominio</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gyms as $gym)
                        <tr>
                            <td>{{ $gym->name }}</td>
                            <td>{{ $gym->slug }}</td>
                            <td>{{ $gym->domains->first()?->domain }}</td>
                            <td class="text-end">
                                <a href="{{ route('backend.strutture.edit', $gym) }}" class="btn btn-sm btn-outline-warning" title="Modifica"><i class="fa-solid fa-pen-to-square"></i></a>
                                <form method="POST" action="{{ route('backend.strutture.destroy', $gym) }}" class="d-inline" onsubmit="return confirm('Eliminare questa struttura?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Elimina"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-gray-muted py-5">
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
