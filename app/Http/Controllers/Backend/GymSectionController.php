<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Gym;
use App\Models\GymSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GymSectionController extends Controller
{
    public function index(Request $request): View
    {
        $gym = $this->resolveGym($request);

        Gate::authorize('manage', $gym);

        return view('backend.setup.order', [
            'gym' => $gym,
            'gyms' => $request->user()->isSuperAdmin() ? Gym::orderBy('name')->get() : collect(),
            'gymSections' => $gym->gymSections,
        ]);
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

    private function resolveGym(Request $request): Gym
    {
        if ($request->user()->isSuperAdmin()) {
            return Gym::findOrFail($request->integer('gym_id') ?: Gym::orderBy('name')->value('id'));
        }

        return $request->user()->gym;
    }
}
