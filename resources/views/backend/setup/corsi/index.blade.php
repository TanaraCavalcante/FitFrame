@extends('backend.layouts.app')

@section('title', 'Corsi')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i
                class="fa-solid fa-house" aria-hidden="true"></i></a></li>
    <li class="breadcrumb-item"><span class="fs-7">Corsi</span></li>
@endsection

@section('content')
    <div class="card border-0 mb-3 py-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h1 class="h3 mb-0">Corsi</h1>

                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i
                            class="fa-solid fa-arrow-left me-1"></i>Indietro</a>
                    <a href="{{ route('backend.setup.corsi.create', ['gym_id' => $gym->id]) }}"
                        class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-plus me-1"></i>Nuovo Corso</a>
                </div>
            </div>

            @if ($gyms->isNotEmpty())
                <hr>
                <form method="GET" action="{{ route('backend.setup.corsi.index') }}"
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
                        <th></th>
                        <th>Nome</th>
                        <th>Descrizione</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $class)
                        <tr>
                            <td class="text-center" style="width: 48px;">
                                <i class="{{ $class->icon }} fs-4 text-gray-muted"></i>
                            </td>
                            <td>{{ $class->name }}</td>
                            <td class="text-truncate" style="max-width: 320px;">{{ $class->description }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('backend.setup.corsi.move-up', $class) }}"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Sposta su"
                                        @disabled($loop->first)>
                                        <i class="fa-solid fa-arrow-up"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('backend.setup.corsi.move-down', $class) }}"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Sposta giù"
                                        @disabled($loop->last)>
                                        <i class="fa-solid fa-arrow-down"></i>
                                    </button>
                                </form>
                                <a href="{{ route('backend.setup.corsi.edit', $class) }}"
                                    class="btn btn-sm btn-outline-warning" title="Modifica"><i
                                        class="fa-solid fa-pen-to-square"></i></a>
                                <form method="POST" action="{{ route('backend.setup.corsi.destroy', $class) }}"
                                    class="d-inline confirm-delete-form"
                                    data-confirm-title="Eliminare {{ $class->name }}?"
                                    data-confirm-text="Questa azione non può essere annullata.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Elimina"><i
                                            class="fa-solid fa-trash"></i></button>
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
