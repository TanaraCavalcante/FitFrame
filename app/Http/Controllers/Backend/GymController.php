<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreGymRequest;
use App\Http\Requests\Backend\UpdateGymRequest;
use App\Models\Gym;
use App\Models\GymSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GymController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Gym::class);

        $search = $request->string('search')->toString();

        $gyms = Gym::with('domains')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhereHas('domains', fn ($query) => $query->where('domain', 'like', "%{$search}%"));
            }))
            ->orderBy('name')
            ->get();

        return view('backend.strutture.index', [
            'gyms' => $gyms,
            'search' => $search,
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

        foreach (GymSection::DEFAULT_ORDER as $order => $section) {
            $gym->gymSections()->create(['section' => $section, 'order' => $order]);
        }

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
