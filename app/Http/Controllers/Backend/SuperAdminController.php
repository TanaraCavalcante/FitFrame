<?php

namespace App\Http\Controllers\Backend;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreSuperAdminRequest;
use App\Http\Requests\Backend\UpdateSuperAdminRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', User::class);

        return view('backend.super-admin.index', [
            'users' => User::where('role', UserRole::SuperAdmin)->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('backend.super-admin.create');
    }

    public function store(StoreSuperAdminRequest $request): RedirectResponse
    {
        User::create([
            ...$request->safe()->only(['name', 'email']),
            'password' => Hash::make($request->validated('password')),
            'role' => UserRole::SuperAdmin,
        ]);

        return redirect()->route('backend.super-admin.index')->with('success', 'Super admin creato con successo.');
    }

    public function edit(User $user): View
    {
        abort_unless($user->role === UserRole::SuperAdmin, 404);
        Gate::authorize('updateSuperAdmin', $user);

        return view('backend.super-admin.edit', ['user' => $user]);
    }

    public function update(UpdateSuperAdminRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::SuperAdmin, 404);

        $data = $request->safe()->only(['name', 'email']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);

        return redirect()->route('backend.super-admin.index')->with('success', 'Super admin aggiornato con successo.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::SuperAdmin, 404);
        Gate::authorize('deleteSuperAdmin', $user);

        $user->delete();

        return redirect()->route('backend.super-admin.index')->with('success', 'Super admin eliminato con successo.');
    }
}
