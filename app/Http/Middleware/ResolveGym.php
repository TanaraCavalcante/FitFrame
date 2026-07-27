<?php

namespace App\Http\Middleware;

use App\Models\Domain;
use App\Models\Gym;
use Closure;
use Igaster\LaravelTheme\Facades\Theme;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveGym
{
    /**
     * Risolve la palestra (Gym) dal dominio della richiesta e attiva il tema corrispondente.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cerca il dominio della richiesta corrente nella tabella `domains`, con il Gym già caricato (eager load).
        $domain = Domain::with([
            'gym.contents',
            'gym.contact',
            'gym.gymClasses' => fn ($query) => $query->orderBy('order'),
            'gym.plans' => fn ($query) => $query->orderBy('order'),
            'gym.plans.planFeatures' => fn ($query) => $query->orderBy('order'),
            'gym.personalTrainers' => fn ($query) => $query->orderBy('order'),
            'gym.personalTrainers.media',
            'gym.testimonials' => fn ($query) => $query->orderBy('order'),
        ])->where('domain', $request->getHost())->first();

        if ($domain) {
            // Dominio trovato: attiva il tema (igaster/laravel-theme) con lo slug della palestra.
            // Se il tema non esiste ancora (slug libero non ancora implementato), resta "base".
            Theme::set(Theme::exists($domain->gym->slug) ? $domain->gym->slug : 'base');

            // Condivide il Gym con tutte le view, cosi ogni pagina puo accedervi senza passarlo esplicitamente.
            view()->share('gym', $domain->gym);

            // Registra il Gym nel container: i controller possono riceverlo via type-hint (Gym $gym).
            app()->instance(Gym::class, $domain->gym);
        }

        // Dominio non trovato: nessun errore, resta il tema di default (config/themes.php),
        // ma un Gym vuoto va comunque condiviso: le view (header, hero, ecc.) si aspettano
        // sempre $gym disponibile, anche se questa richiesta non appartiene a nessuna palestra.
        if (! $domain) {
            view()->share('gym', new Gym);
        }

        return $next($request);
    }
}
