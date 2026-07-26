@props(['count' => 0, 'target' => 'filtri-collapse'])

<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-sm btn-outline-primary filter-toggle-btn" data-bs-toggle="collapse"
        data-bs-target="#{{ $target }}" aria-expanded="{{ $count > 0 ? 'true' : 'false' }}" aria-controls="{{ $target }}">
        <i class="fa-solid fa-filter me-1" aria-hidden="true"></i>Filtri
        @if ($count > 0)
            <span class="badge bg-primary ms-1">{{ $count }}</span>
        @endif
        <i class="fa-solid fa-chevron-down ms-2" aria-hidden="true"></i>
    </button>
</div>

<div class="collapse @if ($count > 0) show @endif mb-3" id="{{ $target }}">
    {{ $slot }}
</div>
