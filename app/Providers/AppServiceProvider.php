<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Limita i tentativi di login a 5 al minuto per IP, per contrastare il brute force.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Limita le richieste di reset password a 5 al minuto per IP, per contrastare abusi/enumerazione.
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Limita le domande al chatbot di aiuto a 10 al minuto per utente, per contenere i costi verso il servizio RAG/Groq.
        RateLimiter::for('chat', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()->id);
        });

        // Passa al widget di chat la cronologia recente dell'utente autenticato, per farla sopravvivere al reload della pagina.
        View::composer('backend.layouts.components.chat-widget', function ($view) {
            $view->with('chatHistory', Auth::check()
                ? Auth::user()->chatMessages()->latest()->take(20)->get()->reverse()->values()
                : collect());
        });
    }
}
