@extends('backend.layouts.app')

@section('title', 'Ordina sezioni')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><span class="fs-7">Ordina sezioni</span></li>
@endsection

@section('content')
    <div class="card border-0 mb-3 py-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h1 class="h3 mb-0">Ordina sezioni</h1>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Indietro</a>
            </div>

            @if ($gyms->isNotEmpty())
                <hr>
                <form method="GET" action="{{ route('backend.setup.order') }}"
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
        <p class="small text-gray-muted mb-3">
            <i class="fa-solid fa-circle-info me-1"></i>
            Determina l'ordine in cui le sezioni appaiono nella pagina pubblica. Hero, intestazione e footer sono fissi e non compaiono qui.
        </p>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th></th>
                        <th>Sezione</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($gymSections as $gymSection)
                        <tr>
                            <td class="text-center" style="width: 48px;">
                                <i class="fa-solid {{ $gymSection->icon() }} fs-4 text-gray-muted"></i>
                            </td>
                            <td>{{ $gymSection->label() }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('backend.setup.order.move-up', $gymSection) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Sposta su" @disabled($loop->first)>
                                        <i class="fa-solid fa-arrow-up"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('backend.setup.order.move-down', $gymSection) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Sposta giù" @disabled($loop->last)>
                                        <i class="fa-solid fa-arrow-down"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
