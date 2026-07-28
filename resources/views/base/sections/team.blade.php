{{--
    Soglia = quante card entrano per riga a quella dimensione (stesso criterio di
    Corsi/Piani): mobile e tablet condividono la soglia 2 (girano già a partire da 3
    membri), desktop 4. Il server non conosce il viewport, quindi la struttura
    "marquee" (set duplicato) si usa già a partire dalla soglia più bassa; la classe
    --static-desktop dice alla CSS di disattivare l'animazione e mostrare una griglia
    statica su desktop quando la soglia lì non è superata.
--}}
@php
    $mobileThreshold = 2;
    $desktopThreshold = 4;
    $allTrainers = $gym->personalTrainers;
    $trainerCount = $allTrainers->count();
    $needsMarquee = $trainerCount > $mobileThreshold;
    $staticOnDesktop = $trainerCount <= $desktopThreshold;
@endphp

<section id="team" class="py-5 bg-surface">
    <div class="container">
        <span class="section-label">— {{ str_pad($position, 2, '0', STR_PAD_LEFT) }} / TEAM</span>
        <h2 class="font-heading text-uppercase fw-bold mb-4">{{ $gym->content('team_title', 'Il nostro team') }}</h2>

        @if ($needsMarquee)
            {{-- Striscia a scorrimento automatico e continuo, senza frecce (a differenza di Corsi/Piani) --}}
            <div class="team-marquee @if ($staticOnDesktop) team-marquee--static-desktop @endif">
                <div class="team-marquee-track">
                    <div class="team-marquee-group">
                        @foreach ($allTrainers as $trainer)
                            <div class="team-marquee-card">
                                @include('sections._team-card', ['trainer' => $trainer])
                            </div>
                        @endforeach
                    </div>
                    {{-- Copia identica, nascosta agli screen reader: crea il loop senza soluzione di continuità --}}
                    <div class="team-marquee-group" aria-hidden="true">
                        @foreach ($allTrainers as $trainer)
                            <div class="team-marquee-card">
                                @include('sections._team-card', ['trainer' => $trainer])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="row row-cols-2 row-cols-lg-4 g-4 justify-content-center">
                @foreach ($allTrainers as $trainer)
                    <div class="col">
                        @include('sections._team-card', ['trainer' => $trainer])
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
