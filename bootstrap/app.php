<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    // Definisce i file delle rotte: web (browser) e console (comandi artisan).
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Rotte dell'area admin (/admin): stesso gruppo 'web' (sessioni/CSRF),
            // ma SENZA ResolveGym — vedi routes/web.php, dove resta applicato
            // solo alla rotta pubblica.
            Route::middleware('web')->group(base_path('routes/backend.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => route('backend.login'));
        $middleware->redirectUsersTo(fn (Request $request) => route('backend.dashboard'));
    })
    // Personalizzazione della gestione delle eccezioni (vuoto = comportamento di default).
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
