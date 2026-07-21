<?php

use App\Http\Middleware\ResolveGym;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    // Definisce i file delle rotte: web (browser) e console (comandi artisan).
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // Configura la pila di middleware globale dell'applicazione (Laravel 12: niente piu Kernel.php).
    ->withMiddleware(function (Middleware $middleware): void {
        // ResolveGym gira su OGNI richiesta web, prima di qualsiasi rotta:
        // risolve la palestra dal dominio e attiva il tema corrispondente.
        $middleware->web(append: [ResolveGym::class]);
    })
    // Personalizzazione della gestione delle eccezioni (vuoto = comportamento di default).
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
