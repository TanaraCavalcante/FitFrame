<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Concerns\ResolvesGymFromRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\UpdateHeroRequest;
use App\Models\Gym;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class HeroController extends Controller
{
    use ResolvesGymFromRequest;

    /**
     * Chiavi di `contents` gestite da questa pagina.
     *
     * @var list<string>
     */
    private const CONTENT_KEYS = [
        'hero_kicker',
        'hero_title',
        'hero_subtitle',
        'hero_cta_primary',
        'hero_cta_secondary',
        'hero_stat_number',
        'hero_stat_label',
    ];

    /**
     * Slot per le 3 immagini dell'hero, ognuno una collection media indipendente.
     *
     * @var list<int>
     */
    private const IMAGE_SLOTS = [1, 2, 3];

    public function edit(Request $request): View
    {
        $gym = $this->resolveGym($request);

        Gate::authorize('manage', $gym);

        return view('backend.setup.hero', [
            'gym' => $gym,
            'gyms' => $request->user()->isSuperAdmin() ? Gym::orderBy('name')->get() : collect(),
            'visualMode' => $gym->content('hero_visual_mode', 'images'),
            'images' => collect(self::IMAGE_SLOTS)->mapWithKeys(fn (int $slot) => [$slot => $gym->getFirstMedia("hero_image_{$slot}")]),
            'video' => $gym->getFirstMedia('hero_video'),
        ]);
    }

    public function update(UpdateHeroRequest $request): RedirectResponse
    {
        $gym = Gym::findOrFail($request->validated('gym_id'));

        foreach (self::CONTENT_KEYS as $key) {
            $value = $request->validated($key);

            if ($value === null || $value === '') {
                $gym->contents()->where('key', $key)->delete();
            } else {
                $gym->contents()->updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        $gym->contents()->updateOrCreate(['key' => 'hero_visual_mode'], ['value' => $request->validated('visual_mode')]);

        try {
            foreach (self::IMAGE_SLOTS as $slot) {
                if ($request->hasFile("image_{$slot}")) {
                    $gym->addMediaFromRequest("image_{$slot}")->toMediaCollection("hero_image_{$slot}");
                } elseif ($request->boolean("remove_image_{$slot}")) {
                    $gym->clearMediaCollection("hero_image_{$slot}");
                }
            }

            if ($request->hasFile('video')) {
                $gym->addMediaFromRequest('video')->toMediaCollection('hero_video');
            } elseif ($request->boolean('remove_video')) {
                $gym->clearMediaCollection('hero_video');
            }
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Impossibile salvare il file: controlla formato e dimensione.');
        }

        return redirect()->route('backend.setup.hero', ['gym_id' => $gym->id])->with('success', 'Hero aggiornato con successo.');
    }
}
