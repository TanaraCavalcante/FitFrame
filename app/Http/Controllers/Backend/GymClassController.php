<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreGymClassRequest;
use App\Http\Requests\Backend\UpdateGymClassRequest;
use App\Models\Gym;
use App\Models\GymClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GymClassController extends Controller
{
    public function index(Request $request): View
    {
        $gym = $this->resolveGym($request);

        Gate::authorize('manage', $gym);

        return view('backend.setup.corsi.index', [
            'gym' => $gym,
            'gyms' => $request->user()->isSuperAdmin() ? Gym::orderBy('name')->get() : collect(),
            'classes' => $gym->gymClasses,
        ]);
    }

    public function create(Request $request): View
    {
        $gym = $this->resolveGym($request);

        Gate::authorize('manage', $gym);

        return view('backend.setup.corsi.create', [
            'gym' => $gym,
            'gymClass' => null,
        ]);
    }

    public function store(StoreGymClassRequest $request): RedirectResponse
    {
        $gym = Gym::findOrFail($request->validated('gym_id'));

        GymClass::create([
            ...$request->safe()->only(['name', 'description', 'icon']),
            'gym_id' => $gym->id,
            'order' => ((int) $gym->gymClasses()->max('order')) + 1,
        ]);

        return redirect()->route('backend.setup.corsi.index', ['gym_id' => $gym->id])->with('success', 'Corso creato con successo.');
    }

    public function edit(GymClass $gymClass): View
    {
        Gate::authorize('manage', $gymClass->gym);

        return view('backend.setup.corsi.edit', [
            'gym' => $gymClass->gym,
            'gymClass' => $gymClass,
        ]);
    }

    public function update(UpdateGymClassRequest $request, GymClass $gymClass): RedirectResponse
    {
        $gymClass->update($request->validated());

        return redirect()->route('backend.setup.corsi.index', ['gym_id' => $gymClass->gym_id])->with('success', 'Corso aggiornato con successo.');
    }

    public function destroy(GymClass $gymClass): RedirectResponse
    {
        Gate::authorize('manage', $gymClass->gym);

        $gymId = $gymClass->gym_id;
        $gymClass->delete();

        return redirect()->route('backend.setup.corsi.index', ['gym_id' => $gymId])->with('success', 'Corso eliminato con successo.');
    }

    public function moveUp(GymClass $gymClass): RedirectResponse
    {
        Gate::authorize('manage', $gymClass->gym);

        $this->swapOrder($gymClass, $gymClass->previousSibling());

        return redirect()->route('backend.setup.corsi.index', ['gym_id' => $gymClass->gym_id]);
    }

    public function moveDown(GymClass $gymClass): RedirectResponse
    {
        Gate::authorize('manage', $gymClass->gym);

        $this->swapOrder($gymClass, $gymClass->nextSibling());

        return redirect()->route('backend.setup.corsi.index', ['gym_id' => $gymClass->gym_id]);
    }

    private function swapOrder(GymClass $gymClass, ?GymClass $sibling): void
    {
        if ($sibling === null) {
            return;
        }

        $order = $gymClass->order;
        $gymClass->update(['order' => $sibling->order]);
        $sibling->update(['order' => $order]);
    }

    private function resolveGym(Request $request): Gym
    {
        if ($request->user()->isSuperAdmin()) {
            return Gym::findOrFail($request->integer('gym_id') ?: Gym::orderBy('name')->value('id'));
        }

        return $request->user()->gym;
    }
}
