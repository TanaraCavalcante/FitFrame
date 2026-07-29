<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Concerns\ResolvesGymFromRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\UpdateGymSectionTitlesRequest;
use App\Models\Gym;
use App\Models\GymSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GymSectionController extends Controller
{
    use ResolvesGymFromRequest;

    public function index(Request $request): View
    {
        $gym = $this->resolveGym($request);

        Gate::authorize('manage', $gym);

        return view('backend.setup.order', [
            'gym' => $gym,
            'gyms' => $request->user()->isSuperAdmin() ? Gym::orderBy('name')->get() : collect(),
            'gymSections' => $gym->gymSections,
            // Ordine fisso (non quello scelto dalla struttura): il form dei titoli non deve
            // "saltare" quando l'utente riordina le sezioni nella tabella sopra.
            'titledSections' => $gym->gymSections
                ->sortBy(fn (GymSection $gymSection) => array_search($gymSection->section, GymSection::DEFAULT_ORDER))
                ->filter(fn (GymSection $gymSection) => $gymSection->titleContentKey() !== null),
        ]);
    }

    public function updateTitles(UpdateGymSectionTitlesRequest $request): RedirectResponse
    {
        $gym = Gym::findOrFail($request->validated('gym_id'));

        foreach (GymSection::DEFAULT_ORDER as $section) {
            $key = GymSection::titleContentKeyFor($section);

            if ($key === null) {
                continue;
            }

            $value = $request->validated($key);

            if ($value === null || $value === '') {
                $gym->contents()->where('key', $key)->delete();
            } else {
                $gym->contents()->updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        return redirect()->route('backend.setup.order', ['gym_id' => $gym->id])->with('success', 'Titoli aggiornati con successo.');
    }

    public function moveUp(GymSection $gymSection): RedirectResponse
    {
        Gate::authorize('manage', $gymSection->gym);

        $this->swapOrder($gymSection, $gymSection->previousSibling());

        return redirect()->route('backend.setup.order', ['gym_id' => $gymSection->gym_id]);
    }

    public function moveDown(GymSection $gymSection): RedirectResponse
    {
        Gate::authorize('manage', $gymSection->gym);

        $this->swapOrder($gymSection, $gymSection->nextSibling());

        return redirect()->route('backend.setup.order', ['gym_id' => $gymSection->gym_id]);
    }

    private function swapOrder(GymSection $gymSection, ?GymSection $sibling): void
    {
        if ($sibling === null) {
            return;
        }

        $order = $gymSection->order;
        $gymSection->update(['order' => $sibling->order]);
        $sibling->update(['order' => $order]);
    }
}
