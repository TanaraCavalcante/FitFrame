<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StorePersonalTrainerRequest;
use App\Http\Requests\Backend\UpdatePersonalTrainerRequest;
use App\Models\Gym;
use App\Models\PersonalTrainer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class PersonalTrainerController extends Controller
{
    public function index(Request $request): View
    {
        $gym = $this->resolveGym($request);

        Gate::authorize('manage', $gym);

        return view('backend.setup.team.index', [
            'gym' => $gym,
            'gyms' => $request->user()->isSuperAdmin() ? Gym::orderBy('name')->get() : collect(),
            'trainers' => $gym->personalTrainers()->with('media')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $gym = $this->resolveGym($request);

        Gate::authorize('manage', $gym);

        return view('backend.setup.team.create', [
            'gym' => $gym,
            'trainer' => null,
            'photo' => null,
        ]);
    }

    public function store(StorePersonalTrainerRequest $request): RedirectResponse
    {
        $gym = Gym::findOrFail($request->validated('gym_id'));

        $trainer = PersonalTrainer::create([
            ...$request->safe()->only(['name', 'specialty']),
            'gym_id' => $gym->id,
            'order' => ((int) $gym->personalTrainers()->max('order')) + 1,
        ]);

        try {
            if ($request->hasFile('photo')) {
                $trainer->addMediaFromRequest('photo')->toMediaCollection('photo');
            }
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Impossibile salvare il file: controlla formato e dimensione.');
        }

        return redirect()->route('backend.setup.team.index', ['gym_id' => $gym->id])->with('success', 'Membro del team creato con successo.');
    }

    public function edit(PersonalTrainer $personalTrainer): View
    {
        Gate::authorize('manage', $personalTrainer->gym);

        return view('backend.setup.team.edit', [
            'gym' => $personalTrainer->gym,
            'trainer' => $personalTrainer,
            'photo' => $personalTrainer->getFirstMedia('photo'),
        ]);
    }

    public function update(UpdatePersonalTrainerRequest $request, PersonalTrainer $personalTrainer): RedirectResponse
    {
        $personalTrainer->update($request->safe()->only(['name', 'specialty']));

        try {
            if ($request->hasFile('photo')) {
                $personalTrainer->addMediaFromRequest('photo')->toMediaCollection('photo');
            } elseif ($request->boolean('remove_photo')) {
                $personalTrainer->clearMediaCollection('photo');
            }
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Impossibile salvare il file: controlla formato e dimensione.');
        }

        return redirect()->route('backend.setup.team.index', ['gym_id' => $personalTrainer->gym_id])->with('success', 'Membro del team aggiornato con successo.');
    }

    public function destroy(PersonalTrainer $personalTrainer): RedirectResponse
    {
        Gate::authorize('manage', $personalTrainer->gym);

        $gymId = $personalTrainer->gym_id;
        $personalTrainer->delete();

        return redirect()->route('backend.setup.team.index', ['gym_id' => $gymId])->with('success', 'Membro del team eliminato con successo.');
    }

    public function moveUp(PersonalTrainer $personalTrainer): RedirectResponse
    {
        Gate::authorize('manage', $personalTrainer->gym);

        $this->swapOrder($personalTrainer, $personalTrainer->previousSibling());

        return redirect()->route('backend.setup.team.index', ['gym_id' => $personalTrainer->gym_id]);
    }

    public function moveDown(PersonalTrainer $personalTrainer): RedirectResponse
    {
        Gate::authorize('manage', $personalTrainer->gym);

        $this->swapOrder($personalTrainer, $personalTrainer->nextSibling());

        return redirect()->route('backend.setup.team.index', ['gym_id' => $personalTrainer->gym_id]);
    }

    private function swapOrder(PersonalTrainer $personalTrainer, ?PersonalTrainer $sibling): void
    {
        if ($sibling === null) {
            return;
        }

        $order = $personalTrainer->order;
        $personalTrainer->update(['order' => $sibling->order]);
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
