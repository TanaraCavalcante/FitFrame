<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Mostra il form di login dell'area admin.
     */
    public function create(): View
    {
        return view('backend.auth.login');
    }

    /**
     * Autentica l'utente e rigenera la sessione per prevenire session fixation.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Le credenziali fornite non corrispondono ai nostri archivi.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('backend.dashboard'));
    }

    /**
     * Termina la sessione dell'utente autenticato.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('backend.login');
    }
}
