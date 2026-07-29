# Changelog

Tutte le modifiche rilevanti a questo progetto sono documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/it/1.1.0/).

## [1.0.5] - 2026-07-29

### Aggiunto
- Sezione Piani (`#plans`): sfondo con gradiente diagonale animato (`--color-background` → `--color-surface`), rispetta `prefers-reduced-motion`.
- Bordo (`--color-background`) sulle card piano non in evidenza, per delimitarle dal nuovo sfondo animato.

## [1.0.4] - 2026-07-29

### Aggiunto
- Foto del team (Elena Ferraro, Marco Villa, Sara Bianchi) per il tema Zenflow.

## [1.0.3] - 2026-07-29

### Corretto
- `.team-card__overlay`: gradiente sulla card del team troppo chiaro alla base, nome poco leggibile — stop intermedio più solido, gradiente comunque mantenuto.

## [1.0.2] - 2026-07-29

### Aggiunto
- Favicon per-tema in tutte le view (frontend e backend), con fallback automatico al tema `base` se il tema attivo non ha un proprio `favicon.ico`.

### Modificato
- Frontend: `<title>` usa il nome della struttura (`$gym->name`) invece del nome dell'app.
- Backend: `<title>` prefissato con `FitFrame | `, mantenendo il titolo specifico di ogni pagina.

## [1.0.1] - 2026-07-29

### Aggiunto
- `GymSeeder`: contenuto completo per la struttura Zenflow (Yoga/Pilates) — testi Hero/CTA/footer, 4 pratiche (Hatha Yoga, Pilates, Meditazione, Yin Yoga), 3 piani (Essenziale/Equilibrio/Pienezza), 3 istruttori, 3 testimonianze — applicato anche ai dati già esistenti in ambiente locale.

## [1.0.0] - 2026-07-29

Prima release: pannello admin (gestionale) completo per la gestione multi-tenant delle palestre e del contenuto di ogni landing page pubblica.

### Aggiunto

**Autenticazione e autorizzazione**
- Pannello admin su dominio dedicato (`ADMIN_DOMAIN`, configurabile via `.env`), routing separato dal sito pubblico.
- Login/logout manuale con rate limiting, reset password via email (notifiche core Laravel).
- Ruoli `super_admin`/`gym_admin` (colonna `role` + `gym_id` su `User`), Policy dedicate per `Gym` e `User`.
- Impersonation (il super_admin assume l'identità di un gym_admin).

**CRUD di struttura** (solo super_admin)
- Strutture (Gym): crea automaticamente le 6 sezioni riordinabili della home pubblica alla creazione.
- Utenti (gym_admin) e Super Admin, con separazione nome/cognome, toggle mostra/nascondi password, filtri di ricerca lato server.

**Layout backend**
- Sidebar collassabile (hover-preview), tema chiaro/scuro persistito, breadcrumb, avatar/popover utente, tipografia dedicata (Inter).
- Conferma di eliminazione con SweetAlert2 al posto del `confirm()` nativo.

**CRUD di contenuto per sezione** (super_admin o gym_admin scoperto alla propria struttura, selettore struttura per il super_admin in ogni pagina)
- Hero: testo + fino a 3 immagini o 1 video (mutuamente esclusivi), upload via Spatie MediaLibrary.
- Corsi, Piani, Team, Testimonianze: liste riordinabili (su/giù), upload foto nel Team, caratteristiche dinamiche nei Piani, un solo piano "in evidenza" per struttura.
- Galleria: 5 slot immagine indipendenti, indicatore visivo (check/x) di quale slot ricade sul fallback del tema.
- CTA finale + Contatti: titolo/sottotitolo/pulsante della CTA, slogan del footer, dati di contatto (indirizzo, telefono, email, WhatsApp, Instagram, orari).
- Ordina sezioni: riordina le 6 sezioni della home + modifica il titolo di ognuna (tranne la CTA, che ha già un campo proprio).
- Tab in stile underline di Bootstrap (`.nav-underline`) per navigare tra i blocchi di una stessa pagina (Hero, CTA, Ordina sezioni), invece di card separate.

**Frontend pubblico**
- Carousel manuale (frecce) in Corsi/Piani quando il numero di elementi supera quello che entra per riga.
- Carousel automatico in loop (CSS puro, nessuna libreria) in Team/Testimonianze, con soglia responsiva per breakpoint, pausa al passaggio del mouse, disattivato per `prefers-reduced-motion`.
- Fallback all'immagine di default del tema quando non c'è un upload (Galleria, Team).

**Altro**
- Traduzione italiana dei messaggi di validazione di Laravel (`lang/it/validation.php`), mancante da quando il locale è `it`.
- Trait condivisi: `HasOrderedSiblings` (riordino per FK del genitore) e `ResolvesGymFromRequest` (risoluzione della struttura da `?gym_id=` o dalla struttura dell'utente).

### Modificato
- CSS del backend riorganizzato in `variables`/`tipografia`/`generals`/`generics`, ognuno linkato singolarmente con cache-busting proprio.
- Validazione slug delle Strutture: campo "Tema" da select limitata a input libero (`alpha_dash`), con fallback al tema `base` se non esiste ancora.

### Corretto
- N+1 diffusi: `orderBy('order')` mancante su varie relazioni (`gymClasses`, `plans`, `personalTrainers`, `testimonials`, `gymSections`), eager-load di `gym.media` mancante in `ResolveGym`.
- Bug di seed: creare una struttura via admin non generava le righe `gym_sections` di default, lasciando la sua home pubblica priva di tutte le sezioni riordinabili.
- `ParseError` intermittente causato da commenti Blade (`{{-- --}}`) lasciati dentro blocchi `@php...@endphp` (lì dentro Blade non compila nulla, è PHP puro).
- Vari fix di layout responsive (griglie che lasciavano un buco laterale con meno elementi del previsto, marquee Team non a capo correttamente in tablet, badge tagliato dall'`overflow: hidden` nel carousel Piani).

## [Riferimento]

Lo storico dettagliato, commit per commit, di questa release è stato consolidato da `CHANGELOG-feature-backend.md` (rimosso dopo il merge in questa entry).
