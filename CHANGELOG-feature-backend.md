## [2026-07-29] - Titoli delle sezioni in "Ordina sezioni"

### Aggiunto
- Nuova tab "Titoli" nella pagina "Ordina sezioni": un campo titolo per ognuna delle 5 sezioni gestibili (Corsi, Piani, Galleria, Team, Testimonianze) — prima non erano modificabili da nessuna parte. Il titolo del CTA finale resta gestito nella sua pagina dedicata, per non duplicare lo stesso campo in due posti.
- `GymSection::titleContentKeyFor()`/`defaultTitle()`: mappa sezione → content key e testo di default, usata sia dal controller che dalla view.
- 4 test aggiuntivi su `GymSectionControllerTest` (persistenza titoli, rimozione con campo vuoto, autorizzazione).

### Modificato
- Pagina "Ordina sezioni" riorganizzata in due tab in stile underline ("Ordina"/"Titoli", stesso pattern di Hero e CTA) invece di due card sempre visibili una sotto l'altra.

## [2026-07-28] - Gestione ordine sezioni (ultima pagina Setup)

### Aggiunto
- CRUD "Ordina sezioni" (`backend/setup/order`, rotte `backend.setup.order`/`.move-up`/`.move-down`): lista le 6 sezioni riordinabili di una struttura (Corsi, Piani, Galleria, Team, Testimonianze, CTA finale) con icona ed etichetta, riordino su/giù. Nessun create/edit/delete: le sezioni sono fisse, solo l'ordine è personalizzabile. Hero, intestazione e footer restano fissi e non compaiono qui.
- `GymSection::DEFAULT_ORDER` centralizza l'elenco delle 6 sezioni (era duplicato in `GymSeeder`), più `label()`/`icon()` per la UI admin. Aggiunto `HasOrderedSiblings` per il riordino.
- 7 test per `GymSectionController`.

### Corretto
- `GymController@store` (CRUD Strutture): creare una nuova struttura non generava le righe `gym_sections` di default — la sua pagina pubblica risultava priva di tutte le sezioni riordinabili (Corsi, Piani, Galleria, Team, Testimonianze, CTA finale), mostrando solo Hero/intestazione/footer. Ora seeda `GymSection::DEFAULT_ORDER` alla creazione.
- `Gym::gymSections()` ora ordina esplicitamente per `order` (stessa lacuna già corretta per le altre relazioni riordinabili).

## [2026-07-28] - Allineato .env.example al locale italiano del progetto

### Corretto
- `.env.example` aveva ancora `APP_LOCALE=en`/`APP_FAKER_LOCALE=en_US`, mentre l'app è italiana (vedi `lang/it/`). Allineato a `it`/`it_IT`, mantenendo `APP_FALLBACK_LOCALE=en` (non tutti i file `lang/it/*.php` esistono ancora, es. auth/pagination).

## [2026-07-28] - Gestione CTA finale e Contatti + restyling tab in stile underline

### Aggiunto
- CRUD CTA (`backend/setup/cta`, rotte `backend.setup.cta`/`backend.setup.cta.update`): pagina singola come Hero, gestisce testo della sezione CTA finale (titolo, sottotitolo, testo pulsante), slogan del footer, e tutti i campi Contatti (indirizzo, telefono, email, WhatsApp, Instagram, orari) — `address`/`phone`/`hours` obbligatori (NOT NULL a livello di DB), gli altri opzionali. Usa `$gym->contact()->updateOrCreate(...)` perché la palestra potrebbe non avere ancora una riga Contact.
- 7 test per `CtaController`.

### Modificato
- Le due card di CTA e Hero sono state unite in una sola, con le sezioni "Cta finali"/"Contatti" (e "Testo"/"Visual" in Hero) navigabili tramite tab in stile underline di Bootstrap (`.nav-underline`) invece di due card separate — riusa il meccanismo JS generico dei tab già esistente (`.tab-toggle-btn`) e la classe `.hover-accent` già presente nel CSS del backend. Aggiunta una sola regola CSS (`.nav-underline .nav-link.active`) per colorare il testo della tab attiva.

## [2026-07-28] - Fix marquee Team in tablet + soglia tablet allineata al mobile

### Corretto
- Marquee Team in modalità statica (sotto soglia): il wrap/centering era applicato a `.team-marquee-track`, che ha un solo figlio visibile (`.team-marquee-group`) — un flex-item unico non va mai a capo da solo, quindi le 3 card traboccavano e venivano tagliate dall'`overflow: hidden` del contenitore (visibile su iPad in tablet). Spostato wrap/justify-content sul group, che contiene le card vere.

### Modificato
- Soglia tablet rimossa: ora condivide quella del mobile (gira già a partire da 3 membri, come il mobile), invece di restare statica fino a 3 e girare solo da 4. Solo il desktop mantiene un limite differenziato (statico fino a 4, gira da 5). Rimossa la classe `.team-marquee--static-tablet`, diventata irraggiungibile.

## [2026-07-28] - Carousel automatico per la sezione Testimonianze nel frontend pubblico

### Aggiunto
- Sezione Testimonianze pubblica: oltre 3 testimonianze, striscia a scorrimento automatico e continuo (stesso meccanismo di Team), senza frecce. Sotto la soglia, griglia statica centrata (invariata la logica, solo aggiunto `justify-content-center` per evitare il buco laterale con meno di 3 card).
- `sections/_testimonial-card.blade.php`: card estratta per essere condivisa da griglia e striscia.

### Modificato
- `@keyframes team-marquee-scroll` rinominato in `marquee-scroll`: condiviso da Team e Testimonianze invece di duplicare la stessa regola.

## [2026-07-28] - Gestione Testimonianze

### Aggiunto
- CRUD Testimonianze (`backend/setup/testimonianze`, rotte `backend.setup.testimonianze.*`): index con selettore struttura per il super_admin, create/edit, eliminazione, riordino su/giù. Campi: autore, testo, "cliente da" (opzionale).
- 8 test per `TestimonialController` (autorizzazione, CRUD, ordine).

### Modificato
- `Gym::testimonials()` ora ordina esplicitamente per `order` (stessa lacuna già corretta per `gymClasses`/`plans`/`personalTrainers`). `Testimonial` usa `HasOrderedSiblings` per il riordino.

## [2026-07-27] - Carousel automatico per la sezione Team nel frontend pubblico

### Aggiunto
- Sezione Team pubblica: oltre la soglia per breakpoint (mobile >2, tablet >3, desktop >4, stesso criterio "quante ne entrano per riga" di Corsi/Piani), le card scorrono in loop automatico e continuo (CSS puro, nessuna libreria), senza frecce — pausa al passaggio del mouse, disattivato per chi preferisce `prefers-reduced-motion`.
- `sections/_team-card.blade.php`: card estratta per essere condivisa da griglia statica e striscia automatica.
- Griglia statica (≤ soglia) centrata invece di lasciare un buco quando mancano card per riempire l'ultima riga.

### Corretto
- Altezza della foto in mobile (`.team-card`, `aspect-ratio` più alto sotto i 768px, sembrava schiacciata alla larghezza ridotta).
- `</td>` duplicato nell'index Team che disallineava tutta la tabella (causa della "foto" che sembrava mostrare il nome del file al posto dell'icona).

## [2026-07-27] - Indicatore stato foto nell'index Team

### Corretto
- Colonna "Foto" dell'index Team: `theme_url()` risolveva rispetto al tema globale attivo, che nel backend non corrisponde alla struttura mostrata (nessun `ResolveGym` qui) — per le strutture diverse da quella con tema attivo l'immagine di fallback risultava rotta (mostrava il nome del file al posto della foto).
- Sostituita con tre stati calcolati esplicitamente sul tema della struttura in questione (stessa logica di `ResolveGym`): foto caricata → miniatura reale; nessuna foto ma default del tema presente su disco → icona verde di spunta; nessuna delle due → icona rossa "x".

## [2026-07-27] - Gestione Team (personal trainer)

### Aggiunto
- CRUD Team (`backend/setup/team`, rotte `backend.setup.team.*`): index con selettore struttura per il super_admin, create/edit, eliminazione, riordino su/giù, upload foto singola per membro.
- 10 test per `PersonalTrainerController` (autorizzazione, CRUD, ordine, upload/rimozione foto).

### Modificato
- `PersonalTrainer`: sostituita la colonna `photo_path` (path statico del tema) con una media collection `photo` (Spatie MediaLibrary, singleFile) — coerente con Hero e Galleria. Aggiunto `HasOrderedSiblings` per il riordino.
- `Gym::personalTrainers()` ora ordina esplicitamente per `order` (stessa lacuna già corretta per `gymClasses`/`plans`).
- Sezione Team pubblica (`sections/team.blade.php`): ogni foto legge prima la media collection del backend, ricadendo sulla foto di default del tema (convenzione nome-slug, come nel seeder demo) se non caricata.
- `ResolveGym`: eager-load di `gym.personalTrainers.media` per evitare N+1 sulla home pubblica.

### Corretto
- Migration `drop_photo_path_from_personal_trainers_table`: rimossa la colonna ormai sostituita dalla media collection.

## [2026-07-27] - Messaggi di validazione in italiano + evidenza slot 1 Galleria

### Aggiunto
- `lang/it/validation.php`: traduzione completa dei messaggi di validazione Laravel (regole + attributi), mancante da quando il locale è `it` — tutti i form del backend (Corsi, Piani, Hero, Galleria) ora mostrano gli errori in italiano invece del default inglese del framework.
- Sottotitolo informativo (`text-primary`) sotto il primo slot immagine della Galleria: chiarisce che è l'immagine in evidenza nella griglia bento del sito pubblico.

## [2026-07-27] - Fix ParseError nella sezione Galleria pubblica

### Corretto
- `sections/gallery.blade.php`: commento Blade `{{-- --}}` lasciato dentro un blocco `@php...@endphp` (lì dentro è PHP puro, non compilato da Blade) causava `ParseError` su ogni caricamento della home pubblica. Spostato fuori dal blocco.

## [2026-07-27] - Gestione Galleria (foto struttura)

### Aggiunto
- CRUD Galleria (`backend/setup/gallery`, rotte `backend.setup.gallery`/`backend.setup.gallery.update`): 5 slot immagine indipendenti, upload/rimozione singola come nell'Hero, select struttura per il super_admin sotto le CTA (stesso pattern di Corsi/Piani).
- 5 test per `GalleryController` (autorizzazione, dropdown super_admin, upload multi-slot, rimozione slot).

### Modificato
- Sezione Galleria pubblica (`sections/gallery.blade.php`): ogni foto ora legge prima la media collection `gallery_image_{n}` della struttura, ricadendo sulla foto di default del tema solo per gli slot lasciati vuoti.
- `Gym::registerMediaCollections()`: aggiunte le 5 collection `gallery_image_1`...`gallery_image_5` (singleFile), accanto a quelle già esistenti dell'hero.

## [2026-07-27] - Carousel per la sezione Piani nel frontend pubblico

### Aggiunto
- Sezione Piani pubblica: carousel a striscia scorrevole (stessa struttura di Corsi) quando la struttura ha più di 3 piani, altrimenti griglia statica invariata.
- Partial `sections/_plan-card.blade.php`: card del piano estratta per essere condivisa da griglia e carousel.

### Modificato
- JS del carousel generalizzato in `public/js/app.js`: da attributi specifici `data-classes-*` a `data-slider-track`/`data-slider-prev`/`data-slider-next` con breakpoint di visibilità passati via `data-slider-breakpoints` (JSON), riusato ora sia da Corsi che da Piani senza duplicare la logica.
- CSS delle frecce spostato da `.classes-carousel` a `.carousel-strip` (classe condivisa) in `public/base/css/general.css`; aggiunto blocco `.plans-viewport`/`.plans-track`/`.plans-card`.

### Corretto
- Badge "Più popolare" (`.plan-card__badge`, `top: -0.75rem`) veniva tagliato da `overflow: hidden` nel carousel: aggiunto `padding-top`/`margin-top` compensativi su `.plans-viewport`.

## [2026-07-27] - Gestione Piani (abbonamenti)

### Aggiunto
- CRUD Piani (`backend/setup/piani`, rotte `backend.setup.piani.*`): index con selettore struttura per il super_admin, create/edit, eliminazione, riordino su/giù.
- Caratteristiche del piano gestite come lista dinamica nel form (bottone "Aggiungi caratteristica", JS vanilla) — al salvataggio sostituiscono tutte quelle esistenti.
- Un solo piano "in evidenza" per struttura: selezionarne uno disattiva automaticamente gli altri.
- Voce "Piani" nel menu Setup dell'aside, dopo "Corsi" (ordine delle sezioni nel frontend pubblico).
- 8 test per `PlanController` (autorizzazione, CRUD, caratteristiche, evidenza esclusiva, riordino).
- Trait `App\Models\Concerns\HasOrderedSiblings`: centralizza la query di elemento precedente/successivo per ordine, usata ora sia da `GymClass` che da `Plan`.

### Corretto
- `Gym::plans()` e `Plan::planFeatures()` ora ordinano esplicitamente per `order` (mancava lato admin, come già successo per `GymClass`; il frontend pubblico restava corretto solo grazie all'eager-load in `ResolveGym`).

## [2026-07-27] - Carousel per la sezione Corsi nel frontend pubblico

### Aggiunto
- Sezione "Corsi" (`sections/classes.blade.php`): oltre 4 corsi attiva uno scorrimento a striscia (custom, senza il Carousel di Bootstrap) che sposta esattamente una card alla volta invece di animare l'intero blocco di 4.
- Frecce prev/next sotto le card, centrate — evita le sovrapposizioni in responsive che si avevano posizionandole lateralmente.
- Partial `sections/_class-card.blade.php` riutilizzato sia dalla griglia statica (≤4 corsi) sia dalla striscia scorrevole.

### Modificato
- Griglia dei corsi (`row-cols-lg-4`) con `justify-content-center`: con meno di 4 corsi le card si centrano invece di lasciare uno spazio vuoto a destra.

## [2026-07-27] - Gestione Corsi (classi)

### Aggiunto
- CRUD Corsi (`backend/setup/corsi`, rotte `backend.setup.corsi.*`): index con selettore struttura per il super_admin, create/edit, eliminazione.
- Riordino manuale (pulsanti su/giù) che scambia la colonna `order` tra corsi adiacenti della stessa palestra; `GymClass::previousSibling()`/`nextSibling()` nel model.
- Voce "Corsi" nel menu Setup dell'aside, subito dopo "Hero" (ordine delle sezioni nel frontend pubblico).
- Conferma di eliminazione con SweetAlert2 (CDN) al posto del `confirm()` nativo — classe riutilizzabile `.confirm-delete-form` in `backend.js`, applicata per ora solo ai Corsi.
- 9 test per `GymClassController` (autorizzazione, CRUD, riordino, ordinamento in lista).

### Corretto
- `Gym::gymClasses()` ora ordina esplicitamente per `order` (mancava lato admin; il frontend pubblico era già corretto tramite l'eager-load in `ResolveGym`).
- Sezione "Corsi" nel frontend pubblico: la griglia usa `justify-content-center`, così con meno di 4 corsi le card si centrano invece di lasciare uno spazio vuoto a destra.

## [2026-07-27] - Riorganizzazione header pagina Hero

### Modificato
- Header della pagina Hero: toggle "Testo"/"Visual" e selettore struttura spostati sotto la riga di titolo/azioni, in una riga propria dopo il separatore.

## [2026-07-27] - Gestione contenuti Hero (testo, immagini, video)

### Aggiunto
- Pagina `backend/setup/hero.blade.php` (rotte `backend.setup.hero`/`.update`): tab "Testo"/"Visual" nel pannello admin, selettore struttura per il super_admin.
- `Gym` implementa `HasMedia`/`InteractsWithMedia` (spatie/laravel-medialibrary, installato ma inutilizzato finora): collection `hero_image_1/2/3` (singleFile ciascuna) e `hero_video` (singleFile).
- Sezione Visual: 3 slot immagine indipendenti con anteprima, upload e pulsante di rimozione per slot; slot video con anteprima, upload e rimozione; radio "Immagini"/"Video" per scegliere quale sia attivo — `hero_visual_mode` salvato in `contents`, senza cancellare l'altro set di file (si può tornare indietro senza ricaricare nulla).
- Validazione client-side del video (formato/dimensione) in `backend.js`, in aggiunta a quella server-side.
- `config/media-library.php` pubblicato, `max_file_size` allineato a 50MB (era 10MB di default: causava un 500 non gestito su upload validi lato Laravel ma rifiutati dal pacchetto).
- Frontend pubblico (`elements/hero.blade.php`): legge `hero_visual_mode` per mostrare immagine (casuale se più di una) o video di sfondo, con fallback al file statico del tema se non configurato.
- Alert di sessione (`success`/`error`) resi `alert-dismissible` con pulsante di chiusura.
- 12 test per `HeroController` (autorizzazione, cambio struttura, testo, upload/rimozione immagini e video, persistenza del `visual_mode`).

## [2026-07-26] - Componente filtro nelle index e utility btn-light/btn-purple

### Aggiunto
- Componente `<x-filter-toggle>` (`resources/views/components/filter-toggle.blade.php`): pulsante "Filtri" collassabile (Bootstrap collapse) con badge del numero di filtri attivi, sopra la tabella.
- Filtri lato server (query string, applicati con `->when()` nel controller): Strutture (ricerca su nome/tema/dominio), Utenti (ricerca + struttura), Super Admin (ricerca).
- Stato vuoto (`@forelse`/`@empty`) nelle tre index quando il filtro non trova risultati, con icona e messaggio "Nessun risultato trovato.".
- Utility `.btn-light-{primary,secondary,success,danger,warning,info}` in `generics.css` (stile Metronic, basate sulle CSS variable native di Bootstrap `--bs-{colore}-rgb`).
- Colore viola "info" di Metronic (`--backend-purple` / `--backend-purple-rgb`, valori esatti presi dal CSS compilato di maelstrom) con `.btn-purple`, `.btn-outline-purple`, `.btn-light-purple`.
- Test dei filtri per i tre controller (ricerca, filtro per struttura).

## [2026-07-26] - Toggle mostra/nascondi password nei form Utenti/Super Admin

### Aggiunto
- Pulsante occhio (mostra/nascondi) sul campo Password in Utenti e Super Admin, via `input-group` + `.password-toggle` in `backend.js` (riutilizzabile per altri campi password futuri).
- `autocomplete="off"`/`"new-password"` su email/password per impedire l'autofill del browser con credenziali salvate.

### Modificato
- Campo Password in creazione non ha più il valore `12345678` precompilato — resta vuoto, il super_admin sceglie la password.

## [2026-07-26] - Separazione nome/cognome per gli utenti

### Aggiunto
- Migrazione `add_surname_to_users_table` (idempotente, default `''`), colonna mirrorata in `User::$attributes`.
- Campo "Cognome" nei form di Utenti e Super Admin, accanto a "Nome" (`col-md-6` ciascuno).

### Modificato
- `User::initials()` ora usa direttamente `name`+`surname` invece di fare parsing di una stringa unica.
- Tutti i punti che mostravano solo `name` (dashboard, avatar/popover dell'aside, tabelle index di Utenti e Super Admin) ora mostrano nome e cognome insieme.
- `UserFactory` genera `name`/`surname` separati (`firstName()`/`lastName()`); `AdminsSeeder` aggiornato di conseguenza.
- Le 4 Form Request di Utenti/Super Admin validano `surname` come `required`.

## [2026-07-26] - Form Utenti/Super Admin: stesso layout a card di Strutture

### Modificato
- Create/edit di Utenti e Super Admin nel layout a due card (header con CTA "Indietro"/"Salva" — sempre "Salva", anche in creazione), come Strutture.
- Form in grid Bootstrap con `.required` sui campi obbligatori; il campo Password è `required` solo in creazione (in modifica resta facoltativo, "lascia vuoto per non cambiarla").

## [2026-07-26] - Form Strutture: layout a card, grid e slug libero

### Aggiunto
- Create/edit di Strutture nel layout a due card (header con CTA "Indietro"/"Crea"/"Salva", body col form), come le index.
- Form Strutture in grid Bootstrap: Nome `col-12`, Tema e Dominio `col-md-6`.
- Classe `.required` in `generics.css` (asterisco rosso dopo la label, stile Metronic) applicata ai campi obbligatori del form Strutture.

### Modificato
- Campo "Tema" del form Strutture: da `<select>` (limitato ai temi già installati) a input di testo libero, default `base` — permette di assegnare uno slug per un tema non ancora costruito.
- Validazione slug (`Store`/`UpdateGymRequest`): non più `Rule::in(temi installati)`, ora solo `alpha_dash`.
- `ResolveGym`: se il tema dello slug non esiste (`Theme::exists()`), il sito resta sul tema `base` invece di errore/tema mancante.
- `GymController` non passa più `$themes` alle view create/edit (non più necessario).

## [2026-07-26] - Layout a card nelle index di Strutture/Utenti/Super Admin

### Modificato
- Header e tabella delle index di Strutture, Utenti e Super Admin ora dentro due `.card` separate (`border-0`, senza bordo) con spazio tra loro, per un aspetto più pulito.
- Rimosso `table-striped` da tutte le tabelle (rimaneva solo su Super Admin), uniformando a `table-hover`.

## [2026-07-26] - Azioni icon-only nelle tabelle di Strutture/Utenti/Super Admin

### Aggiunto
- Pulsanti azione (Modifica, Elimina, Impersona) uniformati a icon-only (`btn-sm` outline + FontAwesome) con `title` per accessibilità, nelle index di Strutture, Utenti e Super Admin.
- Pulsante "Reset password" (solo super_admin) nell'index di Utenti — solo UI, nessuna rotta/logica ancora.
- Pulsanti "Indietro" / "Nuovo" uniformati nell'header delle tre index.

## [2026-07-26] - Breadcrumb per Strutture/Utenti/Super Admin e fix scroll aside

### Aggiunto
- Breadcrumb dedicato (home → sezione → pagina corrente) su index/create/edit di Strutture, Utenti e Super Admin.
- `.fs-7` in `tipografia.css` (.85rem, via media tra `fs-6` di Bootstrap e `.fs-8`), usata nei breadcrumb.
- Header di sezione dell'aside (`Dashboard`, `Amministrazione`, `Gestione`) ora `position: sticky` dentro `.backend-aside-nav` — restano visibili durante lo scroll del menu, con sfondo opaco per coprire le voci che scorrono sotto.

### Corretto
- `.mx-n3` non esiste in questo bundle Bootstrap (margini negativi disabilitati di default) — il nav dell'aside non copriva più la larghezza piena. Sostituito rimuovendo il padding orizzontale dall'`<aside>` e spostandolo su header/user-menu.
- `.nav` di Bootstrap imposta `flex-wrap: wrap`: con l'altezza dell'aside ora fissa, generava una colonna fantasma quando l'accordion Setup si espandeva. Aggiunto `flex-nowrap`.
- Cache-busting rotto: `backend.css` versionava solo l'aggregatore, non i quattro parziali `@import`ati, quindi le modifiche CSS potevano restare in cache del browser. Rimosso l'aggregatore, ogni parziale è ora linkato singolarmente con `filemtime()` proprio.

## [2026-07-26] - Menu aside con sezioni per ruolo e accordion Setup

### Aggiunto
- Aside organizzato in sezioni: header "Dashboard", header "Amministrazione" (solo super_admin: Strutture, Super Admin, Utenti in ordine alfabetico), header "Gestione" (entrambi i ruoli) con accordion "Setup" (Bootstrap collapse).
- Voci figlie di Setup (Hero, Team, Galleria, Testimonianze, CTA, Ordina sezioni) protette da `Route::has()` — disabilitate con tooltip finché le rotte non esistono.
- `.text-gray-muted` in `generics.css`, agganciata a `var(--bs-secondary-color)` (stessa variabile del sottotitolo dashboard) per i header di sezione.
- `.fs-8` in `tipografia.css` per le label dei header, più piccole della scala `fs-*` di Bootstrap (che si ferma a `fs-6`).

### Modificato
- `.hover-accent` torna al colore pieno del testo a riposo (`--backend-aside-color`) invece del muted — il muted resta riservato ai soli header di sezione.
- `DashboardTest` aggiornato per verificare le voci di menu viste da super_admin e gym_admin.

## [2026-07-25] - Navbar allineata all'aside

### Modificato
- Navbar del pannello admin senza bordo, sfondo/colore uguali all'aside in entrambi i temi (stessa variabile `--backend-aside-bg`).

## [2026-07-25] - Rifinitura avatar/popover utente e pulizia CSS backend

### Aggiunto
- `User::initials()` (iniziali per l'avatar, es. "W Tech Admin" → "WA") con test unitari dedicati.
- `.btn-back-primary`, `.bg-popover`, `.bg-aside-subtle` in `generics.css` — utility riutilizzabili basate sulle variabili tema, applicate a bottone logout e popover utente.
- `--backend-popover-bg` e `--backend-accent-shadow` in `variables.css` (chiaro/scuro) per lo sfondo del popover e l'ombra in accent.

### Modificato
- Popover utente nell'aside (avatar, nome, email/nome struttura) convertito da CSS puro a utility Bootstrap + variabili tema; menu spostato in fondo all'aside.
- Nell'aside, il gym_admin vede il nome della struttura al posto dell'email (l'email resta visibile solo nel popover).
- Rimossi stili CSS puri ormai ridondanti (`.backend-outline-button`, `.backend-icon-button`, bordi/colori statici assorbiti dalle utility) e il bordo destro dell'aside (rimosso in entrambi i temi).
- `AdminsSeeder`: nome del super_admin aggiornato a "Tanara Cavalcante".

## [2026-07-25] - Logo, breadcrumb e stato attivo del menu

### Aggiunto
- Logo del pannello admin (chiaro/scuro, swap automatico via tema) in `public/backend/img/`.
- Breadcrumb nella navbar (`@yield('breadcrumb')` con fallback icona home) al posto del titolo statico.
- `public/css/backend/generics.css` con utility `.hover-accent` (colore accent del tema al hover/focus), sezioni commentate in `generals.css` (Main/Sidebar).

### Modificato
- Voce di menu della pagina corrente resta colorata con l'accent (senza sfondo) invece di sparire — sfondo compare solo su hover/focus, anche sulla voce attiva.

## [2026-07-25] - Riorganizzazione CSS backend e tipografia

### Aggiunto
- Tipografia dedicata al pannello admin: font Inter (Google Fonts) con `tabular-nums` per allineare i numeri in tabelle/report, in `public/css/backend/tipografia.css`.

### Modificato
- `public/css/backend.css` diviso in `backend/variables.css` (design token), `backend/tipografia.css`, `backend/generals.css` (stili componenti) — `backend.css` resta l'unico file caricato dal layout, ora solo un aggregatore `@import`.
- Rimosso `public/css/app.css` (vuoto, inutilizzato) e il relativo `<link>` in `master.blade.php`.

## [2026-07-25] - Pannello admin backend completo

### Aggiunto
- Pannello admin completo su dominio dedicato (`ADMIN_DOMAIN`, configurabile via `.env`): login/logout, reset password via email, CRUD Strutture (Gym), Utenti (gym_admin), Super Admin, impersonation.
- Layout backend con sidebar collassabile (hover-preview), tema chiaro/scuro persistito, componenti `aside`/`navbar` separati.
- `AdminsSeeder` per popolare un super_admin e un gym_admin di prova.

### Modificato
- Rotte del pannello admin spostate da prefisso `/admin` a routing per dominio (`Route::domain(config('app.admin_domain'))`), letto da `.env` invece di essere hardcoded.
- Alert di sessione (`success`/`error`) centralizzati nel layout condiviso invece che ripetuti per singola vista.

### Corretto
- Suite di test aggiornata per il nuovo routing per dominio (rimossi i vecchi path `/admin/...`).

## [2026-07-25] - Design e piano del pannello admin backend

### Aggiunto
- Spec di design del pannello admin (`docs/superpowers/specs/2026-07-25-backend-admin-panel-design.md`): ruoli super_admin/gym_admin, autenticazione, routing separato dal sito pubblico, CRUD Strutture/Utenti, impersonation.
- Piano di implementazione (`docs/superpowers/plans/2026-07-25-backend-admin-panel.md`): 10 task da eseguire con TDD, dalla migrazione `role`/`gym_id` fino ai CRUD completi.
