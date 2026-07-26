<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreGymRequest;
use App\Http\Requests\Backend\UpdateGymRequest;
use App\Models\Gym;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GymController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Gym::class);

        return view('backend.strutture.index', [
            'gyms' => Gym::with('domains')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Gym::class);

        return view('backend.strutture.create', [
            'gym' => null,
        ]);
    }

    public function store(StoreGymRequest $request): RedirectResponse
    {
        $gym = Gym::create($request->safe()->only(['name', 'slug']));

        $gym->domains()->create(['domain' => $request->validated('domain')]);

        return redirect()->route('backend.strutture.index')->with('success', 'Struttura creata con successo.');
    }

    public function edit(Gym $gym): View
    {
        Gate::authorize('update', $gym);

        return view('backend.strutture.edit', [
            'gym' => $gym->load('domains'),
        ]);
    }

    public function update(UpdateGymRequest $request, Gym $gym): RedirectResponse
    {
        $gym->update($request->safe()->only(['name', 'slug']));

        $domain = $gym->domains->first();

        if ($domain) {
            $domain->update(['domain' => $request->validated('domain')]);
        } else {
            $gym->domains()->create(['domain' => $request->validated('domain')]);
        }

        return redirect()->route('backend.strutture.index')->with('success', 'Struttura aggiornata con successo.');
    }

    public function destroy(Gym $gym): RedirectResponse
    {
        Gate::authorize('delete', $gym);

        if ($gym->admins()->exists()) {
            return redirect()->route('backend.strutture.index')
                ->with('error', 'Impossibile eliminare: la struttura ha ancora utenti assegnati.');
        }

        $gym->delete();

        return redirect()->route('backend.strutture.index')->with('success', 'Struttura eliminata con successo.');
    }
}
