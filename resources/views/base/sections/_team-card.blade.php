<div class="team-card">
    {{-- Se non c'è una foto caricata dal backend, ricade su quella del tema associata al nome (stessa convenzione del seeder demo) --}}
    <img src="{{ $trainer->getFirstMediaUrl('photo') ?: theme_url('img/team/'.\Illuminate\Support\Str::slug($trainer->name).'.jpg') }}" class="w-100 h-100 object-fit-cover" alt="{{ $trainer->name }}">

    <div class="team-card__overlay">
        <span class="d-block font-heading text-uppercase fw-bold">{{ $trainer->name }}</span>
        <span class="d-block small text-muted-foreground">{{ $trainer->specialty }}</span>
    </div>
</div>
