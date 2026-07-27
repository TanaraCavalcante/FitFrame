@extends('backend.layouts.app')

@section('title', 'Team')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><span class="fs-7">Team</span></li>
@endsection

@section('content')
    <div class="card border-0 mb-3 py-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h1 class="h3 mb-0">Team</h1>
                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Indietro</a>
                    <a href="{{ route('backend.setup.team.create', ['gym_id' => $gym->id]) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-plus me-1"></i>Nuovo Membro</a>
                </div>
            </div>

            @if ($gyms->isNotEmpty())
                <hr>
                <form method="GET" action="{{ route('backend.setup.team.index') }}"
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

    {{-- Il tema attivo nell'admin non corrisponde a quello della struttura mostrata (nessun ResolveGym qui):
         per verificare se esiste una foto di default calcoliamo il nome del tema di $gym esplicitamente,
         con la stessa logica di ResolveGym, invece di affidarci al tema globale attivo. --}}
    @php
        $teamPlaceholderTheme = \Igaster\LaravelTheme\Facades\Theme::exists($gym->slug) ? $gym->slug : 'base';
    @endphp

    <div class="card border-0 p-3">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nome</th>
                        <th>Specialità</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trainers as $trainer)
                        <tr>
                            <td style="width: 48px;">
                                @if ($trainer->getFirstMedia('photo'))
                                    <img src="{{ $trainer->getFirstMediaUrl('photo') }}" alt="Foto di {{ $trainer->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                @elseif (file_exists(public_path("{$teamPlaceholderTheme}/img/team/".\Illuminate\Support\Str::slug($trainer->name).'.jpg')))
                                    <span class="text-success" title="Nessuna foto caricata: verrà usata quella di default del tema">
                                        <i class="fa-solid fa-circle-check fa-lg" aria-hidden="true"></i>
                                    </span>
                                @else
                                    <span class="text-danger" title="Nessuna foto disponibile, né caricata né di default">
                                        <i class="fa-solid fa-circle-xmark fa-lg" aria-hidden="true"></i>
                                    </span>
                                @endif
                            </td>
                            <td>{{ $trainer->name }}</td>
                            <td>{{ $trainer->specialty }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('backend.setup.team.move-up', $trainer) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Sposta su" @disabled($loop->first)>
                                        <i class="fa-solid fa-arrow-up"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('backend.setup.team.move-down', $trainer) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Sposta giù" @disabled($loop->last)>
                                        <i class="fa-solid fa-arrow-down"></i>
                                    </button>
                                </form>
                                <a href="{{ route('backend.setup.team.edit', $trainer) }}" class="btn btn-sm btn-outline-warning" title="Modifica"><i class="fa-solid fa-pen-to-square"></i></a>
                                <form method="POST" action="{{ route('backend.setup.team.destroy', $trainer) }}" class="d-inline confirm-delete-form"
                                    data-confirm-title="Eliminare {{ $trainer->name }}?"
                                    data-confirm-text="Questa azione non può essere annullata.">
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
