<x-master>
    {{-- Header e Hero sono fissi, sempre in cima (vedi docs/6-temas.md) --}}
    @include('elements.header')
    @include('elements.hero')

    {{-- Sezioni centrali ordinabili per palestra, ordine da `gym_sections`.
         $position segue l'ordine reale mostrato a questa palestra, non un
         numero fisso per sezione (vedi il label "— 01 / X" in ogni sezione). --}}
    @foreach ($sections as $index => $section)
        @include("sections.{$section}", ['position' => $index + 1])
    @endforeach

    {{-- Footer fisso, sempre in fondo --}}
    @include('elements.footer')
</x-master>
