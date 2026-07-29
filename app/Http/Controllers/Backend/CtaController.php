<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Concerns\ResolvesGymFromRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\UpdateCtaRequest;
use App\Models\Gym;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CtaController extends Controller
{
    use ResolvesGymFromRequest;

    /**
     * Chiavi di `contents` gestite da questa pagina.
     *
     * @var list<string>
     */
    private const CONTENT_KEYS = [
        'cta_title',
        'cta_subtitle',
        'cta_button',
        'footer_slogan',
    ];

    public function edit(Request $request): View
    {
        $gym = $this->resolveGym($request);

        Gate::authorize('manage', $gym);

        return view('backend.setup.cta', [
            'gym' => $gym,
            'gyms' => $request->user()->isSuperAdmin() ? Gym::orderBy('name')->get() : collect(),
            'contact' => $gym->contact,
        ]);
    }

    public function update(UpdateCtaRequest $request): RedirectResponse
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

        // La palestra potrebbe non avere ancora una riga Contact (prima configurazione).
        $gym->contact()->updateOrCreate([], [
            'address' => $request->validated('address'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'whatsapp' => $request->validated('whatsapp'),
            'instagram' => $request->validated('instagram'),
            'hours' => $request->validated('hours'),
        ]);

        return redirect()->route('backend.setup.cta', ['gym_id' => $gym->id])->with('success', 'CTA aggiornata con successo.');
    }
}
