@extends('backend.layouts.app')

@section('title', 'Piani')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><span class="fs-7">Piani</span></li>
@endsection

@section('content')
    <div class="card border-0 mb-3 py-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h1 class="h3 mb-0">Piani</h1>
                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Indietro</a>
                    <a href="{{ route('backend.setup.piani.create', ['gym_id' => $gym->id]) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-plus me-1"></i>Nuovo Piano</a>
                </div>
            </div>
            @if ($gyms->isNotEmpty())
                <hr>
                <form method="GET" action="{{ route('backend.setup.piani.index') }}"
                    class="d-flex justify-content-end align-items-center gap-2">
                    <label class="form-label small text-gray-muted mb-0">Struttura</label>
                    <select name="gym_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        @foreach ($gyms as $g)
                            <option value="{{ $g->id }}" @selected($g->id === $gym->id)>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </div>

    <div class="card border-0 p-3">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Prezzo</th>
                        <th>Caratteristiche</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plans as $plan)
                        <tr>
                            <td>{{ $plan->name }}</td>
                            <td>€{{ number_format($plan->price, 2, ',', '.') }}</td>
                            <td>{{ $plan->planFeatures->count() }}</td>
                            <td>
                                @if ($plan->highlighted)
                                    <span class="badge bg-primary">In evidenza</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('backend.setup.piani.move-up', $plan) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Sposta su" @disabled($loop->first)>
                                        <i class="fa-solid fa-arrow-up"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('backend.setup.piani.move-down', $plan) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Sposta giù" @disabled($loop->last)>
                                        <i class="fa-solid fa-arrow-down"></i>
                                    </button>
                                </form>
                                <a href="{{ route('backend.setup.piani.edit', $plan) }}" class="btn btn-sm btn-outline-warning" title="Modifica"><i class="fa-solid fa-pen-to-square"></i></a>
                                <form method="POST" action="{{ route('backend.setup.piani.destroy', $plan) }}" class="d-inline confirm-delete-form"
                                    data-confirm-title="Eliminare {{ $plan->name }}?"
                                    data-confirm-text="Questa azione non può essere annullata.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Elimina"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-gray-muted py-5">
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
