<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Concerns\ResolvesGymFromRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreTestimonialRequest;
use App\Http\Requests\Backend\UpdateTestimonialRequest;
use App\Models\Gym;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    use ResolvesGymFromRequest;

    public function index(Request $request): View
    {
        $gym = $this->resolveGym($request);

        Gate::authorize('manage', $gym);

        return view('backend.setup.testimonianze.index', [
            'gym' => $gym,
            'gyms' => $request->user()->isSuperAdmin() ? Gym::orderBy('name')->get() : collect(),
            'testimonials' => $gym->testimonials,
        ]);
    }

    public function create(Request $request): View
    {
        $gym = $this->resolveGym($request);

        Gate::authorize('manage', $gym);

        return view('backend.setup.testimonianze.create', [
            'gym' => $gym,
            'testimonial' => null,
        ]);
    }

    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        $gym = Gym::findOrFail($request->validated('gym_id'));

        Testimonial::create([
            ...$request->safe()->only(['author_name', 'text', 'member_since']),
            'gym_id' => $gym->id,
            'order' => ((int) $gym->testimonials()->max('order')) + 1,
        ]);

        return redirect()->route('backend.setup.testimonianze.index', ['gym_id' => $gym->id])->with('success', 'Testimonianza creata con successo.');
    }

    public function edit(Testimonial $testimonial): View
    {
        Gate::authorize('manage', $testimonial->gym);

        return view('backend.setup.testimonianze.edit', [
            'gym' => $testimonial->gym,
            'testimonial' => $testimonial,
        ]);
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($request->validated());

        return redirect()->route('backend.setup.testimonianze.index', ['gym_id' => $testimonial->gym_id])->with('success', 'Testimonianza aggiornata con successo.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        Gate::authorize('manage', $testimonial->gym);

        $gymId = $testimonial->gym_id;
        $testimonial->delete();

        return redirect()->route('backend.setup.testimonianze.index', ['gym_id' => $gymId])->with('success', 'Testimonianza eliminata con successo.');
    }

    public function moveUp(Testimonial $testimonial): RedirectResponse
    {
        Gate::authorize('manage', $testimonial->gym);

        $this->swapOrder($testimonial, $testimonial->previousSibling());

        return redirect()->route('backend.setup.testimonianze.index', ['gym_id' => $testimonial->gym_id]);
    }

    public function moveDown(Testimonial $testimonial): RedirectResponse
    {
        Gate::authorize('manage', $testimonial->gym);

        $this->swapOrder($testimonial, $testimonial->nextSibling());

        return redirect()->route('backend.setup.testimonianze.index', ['gym_id' => $testimonial->gym_id]);
    }

    private function swapOrder(Testimonial $testimonial, ?Testimonial $sibling): void
    {
        if ($sibling === null) {
            return;
        }

        $order = $testimonial->order;
        $testimonial->update(['order' => $sibling->order]);
        $sibling->update(['order' => $order]);
    }
}
