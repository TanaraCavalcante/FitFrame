<?php

namespace App\Http\Controllers;

use App\Models\Gym;
use Illuminate\Contracts\View\View;

class GymController extends Controller
{
    /**
     * Mostra la landing page della palestra risolta dal dominio (vedi ResolveGym).
     */
    public function index(Gym $gym): View
    {
        // Ordine delle sezioni riordinabili, definito in `gym_sections` (vedi docs/6-temas.md sezione H).
        $sections = $gym->gymSections()->orderBy('order')->pluck('section');

        return view('home', ['sections' => $sections]);
    }
}
