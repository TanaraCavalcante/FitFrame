<?php

namespace App\Http\Controllers\Backend;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreGymAdminRequest;
use App\Http\Requests\Backend\UpdateGymAdminRequest;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class GymAdminController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', User::class);

        return view('backend.utenti.index', [
            'users' => User::with('gym')->where('role', UserRole::GymAdmin)->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('backend.utenti.create', [
            'gyms' => Gym::orderBy('name')->get(),
        ]);
    }

    public function store(StoreGymAdminRequest $request): RedirectResponse
    {
        User::create([
            ...$request->safe()->only(['name', 'email', 'gym_id']),
            'password' => Hash::make($request->validated('password')),
            'role' => UserRole::GymAdmin,
        ]);

        return redirect()->route('backend.utenti.index')->with('success', 'Utente creato con successo.');
    }

    public function edit(User $user): View
    {
        abort_unless($user->role === UserRole::GymAdmin, 404);
        Gate::authorize('update', $user);

        return view('backend.utenti.edit', [
            'user' => $user,
            'gyms' => Gym::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateGymAdminRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::GymAdmin, 404);

        $data = $request->safe()->only(['name', 'email', 'gym_id']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);

        return redirect()->route('backend.utenti.index')->with('success', 'Utente aggiornato con successo.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::GymAdmin, 404);
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()->route('backend.utenti.index')->with('success', 'Utente eliminato con successo.');
    }
}
