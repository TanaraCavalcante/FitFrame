<?php

namespace App\Http\Controllers\Backend\Concerns;

use App\Models\Gym;
use Illuminate\Http\Request;

/**
 * Risolve la Gym su cui operare in ogni pagina "Setup": il super_admin la sceglie
 * tramite `?gym_id=` (default alla prima in ordine alfabetico), il gym_admin opera
 * sempre sulla propria.
 */
trait ResolvesGymFromRequest
{
    protected function resolveGym(Request $request): Gym
    {
        if ($request->user()->isSuperAdmin()) {
            return Gym::findOrFail($request->integer('gym_id') ?: Gym::orderBy('name')->value('id'));
        }

        return $request->user()->gym;
    }
}
