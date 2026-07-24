{{--
    Sezione finale: fascia CTA a tutta larghezza con "mailto:" (oggetto
    precompilato, così si capisce che il lead viene da qui) al posto di
    un form finto senza submit — più semplice e realmente funzionante.
--}}
<section id="contact-cta">
    <div class="bg-fit-primary text-center py-5">
        <div class="container">
            <h2 class="font-heading text-uppercase fw-bold text-white mb-3">{{ $gym->content('cta_title', 'Pronto a iniziare?') }}</h2>
            <p class="text-white mb-4">{{ $gym->content('cta_subtitle', 'Prenota una lezione di prova gratuita e scopri Pulse dal vivo.') }}</p>
            <a href="{{ $gym->contact?->mailtoUrl('Voglio prenotare una lezione gratuita') }}" class="btn btn-lg btn-dark text-uppercase fw-bold">{{ $gym->content('cta_button', 'Prenota ora') }}</a>
        </div>
    </div>
</section>
