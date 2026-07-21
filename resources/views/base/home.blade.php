<x-master>
    {{-- Header e Hero sono fissi, sempre in cima (vedi docs/6-temas.md) --}}
    @include('elements.header')
    @include('elements.hero')

    {{-- Sezioni centrali ordinabili per palestra, ordine da `gym_sections` --}}
    @foreach ($sections as $section)
        @include("sections.{$section}")
    @endforeach

    {{-- Footer fisso, sempre in fondo --}}
    @include('elements.footer')
</x-master>
