<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StorePlanRequest;
use App\Http\Requests\Backend\UpdatePlanRequest;
use App\Models\Gym;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(Request $request): View
    {
        $gym = $this->resolveGym($request);

        Gate::authorize('manage', $gym);

        return view('backend.setup.piani.index', [
            'gym' => $gym,
            'gyms' => $request->user()->isSuperAdmin() ? Gym::orderBy('name')->get() : collect(),
            'plans' => $gym->plans()->with('planFeatures')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $gym = $this->resolveGym($request);

        Gate::authorize('manage', $gym);

        return view('backend.setup.piani.create', [
            'gym' => $gym,
            'plan' => null,
        ]);
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $gym = Gym::findOrFail($request->validated('gym_id'));

        if ($request->boolean('highlighted')) {
            $this->resetHighlighted($gym);
        }

        $plan = Plan::create([
            'gym_id' => $gym->id,
            'name' => $request->validated('name'),
            'price' => $request->validated('price'),
            'highlighted' => $request->boolean('highlighted'),
            'order' => ((int) $gym->plans()->max('order')) + 1,
        ]);

        $this->syncFeatures($plan, $request->validated('features'));

        return redirect()->route('backend.setup.piani.index', ['gym_id' => $gym->id])->with('success', 'Piano creato con successo.');
    }

    public function edit(Plan $plan): View
    {
        Gate::authorize('manage', $plan->gym);

        return view('backend.setup.piani.edit', [
            'gym' => $plan->gym,
            'plan' => $plan->load('planFeatures'),
        ]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        if ($request->boolean('highlighted')) {
            $this->resetHighlighted($plan->gym, $plan->id);
        }

        $plan->update([
            'name' => $request->validated('name'),
            'price' => $request->validated('price'),
            'highlighted' => $request->boolean('highlighted'),
        ]);

        $this->syncFeatures($plan, $request->validated('features'));

        return redirect()->route('backend.setup.piani.index', ['gym_id' => $plan->gym_id])->with('success', 'Piano aggiornato con successo.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        Gate::authorize('manage', $plan->gym);

        $gymId = $plan->gym_id;
        $plan->delete();

        return redirect()->route('backend.setup.piani.index', ['gym_id' => $gymId])->with('success', 'Piano eliminato con successo.');
    }

    public function moveUp(Plan $plan): RedirectResponse
    {
        Gate::authorize('manage', $plan->gym);

        $this->swapOrder($plan, $plan->previousSibling());

        return redirect()->route('backend.setup.piani.index', ['gym_id' => $plan->gym_id]);
    }

    public function moveDown(Plan $plan): RedirectResponse
    {
        Gate::authorize('manage', $plan->gym);

        $this->swapOrder($plan, $plan->nextSibling());

        return redirect()->route('backend.setup.piani.index', ['gym_id' => $plan->gym_id]);
    }

    /**
     * Sostituisce tutte le caratteristiche del piano con l'elenco inviato dal form.
     *
     * @param  list<string>  $features
     */
    private function syncFeatures(Plan $plan, array $features): void
    {
        $plan->planFeatures()->delete();

        foreach (array_values($features) as $index => $description) {
            $plan->planFeatures()->create(['description' => $description, 'order' => $index]);
        }
    }

    /**
     * Un solo piano "in evidenza" per palestra: disattiva gli altri.
     */
    private function resetHighlighted(Gym $gym, ?int $exceptPlanId = null): void
    {
        $gym->plans()->where('id', '!=', $exceptPlanId ?? 0)->update(['highlighted' => false]);
    }

    private function swapOrder(Plan $plan, ?Plan $sibling): void
    {
        if ($sibling === null) {
            return;
        }

        $order = $plan->order;
        $plan->update(['order' => $sibling->order]);
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
