{{--
    Il piano "in evidenza" non è deciso dalla posizione nel loop, ma dalla
    colonna `highlighted` (booleana) di questo $plan specifico, letta dal DB.
    @class([...]) aggiunge una classe solo se la sua condizione è vera:
    - highlighted = true  → bg-fit-primary (fondo rosso)
    - highlighted = false → bg-surface (fondo grigio scuro)
--}}
<div
    @class([
        'plan-card p-4 h-100',
        'bg-fit-primary' => $plan->highlighted,
        'bg-surface' => ! $plan->highlighted,
    ])
>
    {{-- Badge "Più popolare": renderizzato solo se questo $plan ha highlighted = true --}}
    @if ($plan->highlighted)
        <span class="plan-card__badge bg-fit-accent text-black font-heading text-uppercase small rounded px-3 py-1">Più popolare</span>
    @endif

    <h3 class="font-heading text-uppercase fw-bold fs-4 mb-3">{{ $plan->name }}</h3>

    <div class="mb-3">
        <span class="font-heading fw-bold fs-1">€{{ number_format($plan->price, 2, ',', '.') }}</span>
        <span class="fs-6">/mese</span>
    </div>

    {{-- <hr> eredita il colore da --bs-border-color, rimappata su --color-muted-foreground in general.css --}}
    <hr>

    {{-- planFeatures arriva già ordinato per "order" (eager load in ResolveGym) --}}
    <ul class="list-unstyled d-flex flex-column gap-2 mb-4">
        @foreach ($plan->planFeatures as $feature)
            <li><i class="fa-solid fa-check text-fit-accent me-2"></i>{{ $feature->description }}</li>
        @endforeach
    </ul>

    {{-- Bottone: outline sul piano in evidenza (contrasto sul fondo rosso), pieno sugli altri --}}
    <a href="#contact-cta" class="btn {{ $plan->highlighted ? 'btn-fit-outline text-uppercase' : 'btn-fit-primary' }} w-100">Scegli piano</a>
</div>
