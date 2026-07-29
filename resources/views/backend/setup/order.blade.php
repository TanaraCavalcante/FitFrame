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
                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Indietro</a>
                    <button type="submit" form="titles-form" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-floppy-disk me-1"></i>Salva titoli</button>
                </div>
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

    <div class="card border-0">
        <div class="card-body">
            <ul class="nav nav-underline mb-4">
                <li class="nav-item">
                    <button type="button" class="nav-link active tab-toggle-btn hover-accent" data-tab-group="order" data-tab-target="order-riordino"><i class="fa-solid fa-sort me-1"></i>Ordina</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link tab-toggle-btn hover-accent" data-tab-group="order" data-tab-target="order-titoli"><i class="fa-solid fa-heading me-1"></i>Titoli</button>
                </li>
            </ul>

            <div data-tab-group="order" data-tab-panel="order-riordino">
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

            <div data-tab-group="order" data-tab-panel="order-titoli" class="d-none">
                <p class="small text-gray-muted mb-3">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Titolo mostrato sopra ogni sezione nella pagina pubblica. Il titolo del CTA finale si gestisce nella sua pagina dedicata.
                </p>
                <form id="titles-form" method="POST" action="{{ route('backend.setup.order.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="gym_id" value="{{ $gym->id }}">

                    <div class="row">
                        @foreach ($titledSections as $gymSection)
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ $gymSection->label() }}</label>
                                <input type="text" name="{{ $gymSection->titleContentKey() }}" value="{{ old($gymSection->titleContentKey(), $gym->content($gymSection->titleContentKey(), $gymSection->defaultTitle())) }}" class="form-control @error($gymSection->titleContentKey()) is-invalid @enderror" placeholder="{{ $gymSection->defaultTitle() }}">
                                @error($gymSection->titleContentKey())
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
