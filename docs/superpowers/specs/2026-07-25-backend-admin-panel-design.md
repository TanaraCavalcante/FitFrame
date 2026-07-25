# Pannello Admin Backend — Design

## Contesto

Finora FitFrame ha solo il frontend pubblico (landing page per palestra,
risolta per dominio). Questa spec introduce la **prima fase del
backend**: autenticazione, ruoli, e gestione delle entità strutturali
(palestre e utenti amministratori). Sviluppata su branch dedicato
`feature/backend` per non toccare il lavoro già fatto sul frontend.

Ambito di questa prima fase: **login + area admin con CRUD di Strutture
(Gym) e Utenti (admin)**. La gestione dei contenuti per-sezione (i 16
content key già mappati: `hero_title`, `gallery_title`, ecc.), l'upload
media dell'hero (immagine/video) e l'abilitazione/riordino delle sezioni
(`gym_sections`) restano fuori da questa spec — verranno progettate
sezione per sezione in spec successive, una volta che questa base è
implementata.

## A) Ruoli e autenticazione

- **Nessun pacchetto di scaffolding** (niente `laravel/breeze`): il
  progetto non ha alcuna toolchain JS (Bootstrap via CDN, senza
  Vite/npm/Tailwind) e questa fase richiede solo login/logout + reset
  password via email — niente registrazione pubblica, niente verifica
  email. Breeze porterebbe Vite+Tailwind+npm senza reale beneficio.
- Login/logout: controller manuale (`Auth::attempt()`), view Bootstrap
  coerenti con lo stile del sito pubblico.
- Reset password: **funzionalità core di Laravel** (`Password::sendResetLink()`
  / `Password::reset()`, notifica `Illuminate\Auth\Notifications\ResetPassword`),
  usa la tabella `password_reset_tokens` già presente dalla migration
  base. Richiede solo configurare `MAIL_MAILER` in `.env` (`log` in
  sviluppo, SMTP reale in produzione) — nessuna dipendenza nuova.
- Un'unica tabella `users`, guard `web` unico (il sito pubblico non ha
  account, quindi non serve un guard separato).
- Nuove colonne su `users`:
  - `role` — enum (`super_admin`, `gym_admin`).
  - `gym_id` — foreign key nullable verso `gyms.id`. `null` **solo** per
    `super_admin`. Nessun vincolo unique: una palestra può avere più
    `gym_admin` (team).
- Creazione utenti: solo il `super_admin` crea account (nessuna
  registrazione pubblica). La password viene impostata direttamente nel
  form di creazione. L'utente potrà cambiarla in seguito da una pagina
  profilo propria, oppure tramite il reset password via email.

## B) Autorizzazione multi-tenant

Niente global scope automatico sui model (troppo "magico" e rischioso
da debuggare) — si usa il pattern idiomatico Laravel: **Policy**.

- `GymPolicy::manage(User $user, Gym $gym): bool` →
  `$user->role === 'super_admin' || $user->gym_id === $gym->id`.
- Ogni controller dell'area "Contenuti" (da progettare dopo) riceve la
  `Gym` via route-model binding e chiama
  `$this->authorize('manage', $gym)`.
- `UserPolicy` analoga per la gestione di Utenti/Super Admin (solo
  `super_admin` può creare/modificare/eliminare altri utenti).

## C) Impersonation

Pacchetto **`lab404/laravel-impersonate`** (già usato in ucue-frontend,
riferimento analizzato). Permette al `super_admin` di "entrare" nella
sessione di un `gym_admin` per verificare/risolvere problemi senza
conoscerne la password. Aggiunto ora come dipendenza, il pulsante
"Impersona" comparirà nella lista Utenti quando implementeremo quella
vista.

## D) Routing

- Nuovo file `routes/backend.php`, registrato in `bootstrap/app.php`
  tramite `withRouting(..., then: fn () => Route::middleware('web')->group(base_path('routes/backend.php')))`
  (prefisso `/admin` o dominio dedicato tipo `gestionefitframe.test` in
  produzione — la scelta del dominio è solo configurazione DNS/vhost,
  non cambia il codice).
- **Correzione rispetto all'assetto attuale:** oggi `ResolveGym` è
  applicato con `$middleware->web(append: [ResolveGym::class])`, cioè
  agganciato al **gruppo** `web` globale — girerebbe quindi anche sulle
  rotte admin (non solo su `routes/web.php`), con un rischio concreto:
  se in futuro l'host admin combaciasse per errore con una riga in
  `domains`, `Theme::set()` attiverebbe il tema sbagliato e condividerebbe
  il `Gym` sbagliato nella sessione admin. Si sposta quindi `ResolveGym`
  da middleware globale a middleware esplicito solo sulla rotta pubblica
  in `routes/web.php` (`Route::middleware(ResolveGym::class)->group(...)`),
  rimuovendo l'`append` da `bootstrap/app.php`. Le rotte admin non lo
  vedono mai, per costruzione, non per comportamento no-op.
- Middleware del gruppo admin: `auth` (guard `web`) + policy per-route
  dove serve.

## E) Menu laterale (aside)

Voci visibili in base al `role`:

| Voce | `super_admin` | `gym_admin` |
|---|---|---|
| Super Admin (CRUD altri super_admin) | ✅ | ❌ |
| Utenti (CRUD gym_admin, assegna `gym_id` da select di Gym esistenti) | ✅ | ❌ |
| Strutture (CRUD Gym + Domain) | ✅ | ❌ |
| Contenuti (da progettare dopo) | ✅ (su qualunque struttura scelta) | ✅ (solo la propria) |

## F) Flussi CRUD

**Creazione Struttura (Gym):**
1. Form: `name`, `slug` (**select**, non testo libero — popolato
   scansionando `resources/views/*/theme.json` esistenti: `base`,
   `pulse`, `iron-house`, `zenflow` — evita mismatch con il tema reale),
   `domain` (singolo campo, un solo dominio per palestra in questa
   fase).
2. Alla submit: crea `Gym` + **1** riga `Domain` collegata (nessuna UI
   multi-dominio per ora, anche se la relazione `hasMany` esistente lo
   permetterebbe in futuro).
3. Non crea automaticamente `gym_sections`/`contents` — resta per la
   spec dei contenuti (o un seeder manuale, da decidere in quella fase).

**Creazione Utente:**
- Form Utenti (da super_admin): `name`, `email`, `password`, `role` fisso
  a `gym_admin`, `gym_id` (select obbligatorio tra le Gym esistenti).
- Form Super Admin (da super_admin): stessi campi ma `role` fisso a
  `super_admin`, **senza** campo `gym_id` (sempre `null`).

## G) Nuove dipendenze (approvate)

- `spatie/laravel-medialibrary` (usata quando arriveremo alla spec
  dell'upload media hero — dichiarata già ora per non dover rinegoziare
  l'approvazione dopo)
- `lab404/laravel-impersonate`

## Fuori ambito in questa fase

- Gestione contenuti per-sezione (i 16 content key)
- Upload media hero (immagine/video, rotazione random tra max 3 immagini)
- Abilitazione/riordino sezioni (`gym_sections`) via UI
- Multi-dominio per palestra
- Invito nuovi utenti via email (creazione resta manuale da parte del super_admin)
- Deploy/DNS reale del dominio admin
