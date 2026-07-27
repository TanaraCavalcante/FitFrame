<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\UpdateGalleryRequest;
use App\Models\Gym;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class GalleryController extends Controller
{
    /**
     * Slot per le 5 foto della galleria, ognuno una collection media indipendente.
     *
     * @var list<int>
     */
    private const IMAGE_SLOTS = [1, 2, 3, 4, 5];

    public function edit(Request $request): View
    {
        $gym = $this->resolveGym($request);

        Gate::authorize('manage', $gym);

        return view('backend.setup.gallery', [
            'gym' => $gym,
            'gyms' => $request->user()->isSuperAdmin() ? Gym::orderBy('name')->get() : collect(),
            'images' => collect(self::IMAGE_SLOTS)->mapWithKeys(fn (int $slot) => [$slot => $gym->getFirstMedia("gallery_image_{$slot}")]),
        ]);
    }

    public function update(UpdateGalleryRequest $request): RedirectResponse
    {
        $gym = Gym::findOrFail($request->validated('gym_id'));

        try {
            foreach (self::IMAGE_SLOTS as $slot) {
                if ($request->hasFile("image_{$slot}")) {
                    $gym->addMediaFromRequest("image_{$slot}")->toMediaCollection("gallery_image_{$slot}");
                } elseif ($request->boolean("remove_image_{$slot}")) {
                    $gym->clearMediaCollection("gallery_image_{$slot}");
                }
            }
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Impossibile salvare il file: controlla formato e dimensione.');
        }

        return redirect()->route('backend.setup.gallery', ['gym_id' => $gym->id])->with('success', 'Galleria aggiornata con successo.');
    }

    private function resolveGym(Request $request): Gym
    {
        if ($request->user()->isSuperAdmin()) {
            return Gym::findOrFail($request->integer('gym_id') ?: Gym::orderBy('name')->value('id'));
        }

        return $request->user()->gym;
    }
}
