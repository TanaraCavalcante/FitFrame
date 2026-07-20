# 3. Multi-tenant

## Cosa significa qui

Una sola applicazione Laravel serve **più palestre diverse**, ognuna
con il proprio dominio. Non ci sono deploy separati né codice
duplicato: la stessa codebase, lo stesso database, decide quale
palestra mostrare in base al dominio da cui arriva la richiesta.

## Dominio, non sottodominio

Ogni palestra ha un **dominio proprio** (es. `academiaa.com`,
`academiab.com`), non un sottodominio di un dominio padre comune
(che sarebbe `a.fitframe.com`, `b.fitframe.com`). In locale, tramite
Valet, questo si traduce in domini `.test` distinti:

```
valet link academiaa   → academiaa.test
valet link academiab   → academiab.test
valet link academiac   → academiac.test
```

Tutti e tre i link puntano alla stessa cartella di progetto.

## Come l'app riconosce la palestra

1. **Tabella `domains`** (`id`, `gym_id`, `domain`) — registra a quale
   palestra appartiene ogni dominio. Una palestra può avere più di un
   dominio associato (es. con/senza `www`).
2. **Tabella `gyms`** (`id`, `name`, `slug`) — identifica ogni
   palestra.
3. **Middleware `ResolveGym`** — eseguito ad ogni richiesta:
   - legge l'host della richiesta (`request()->getHost()`)
   - cerca il dominio nella tabella `domains`
   - trova il `gym_id` corrispondente e carica il `Gym`
   - chiama `Theme::set($gym->slug)` (pacchetto `igaster/laravel-theme`)
     per attivare il tema corrispondente
   - condivide il `Gym` con le view/servizi (es. `view()->share('gym', $gym)`)

## Gestione tema — `igaster/laravel-theme`

La risoluzione di asset e (potenzialmente) view per tema è delegata al
pacchetto `igaster/laravel-theme`, già validato in produzione nel
progetto UCUE con lo stesso schema tema-principale + temi-figli:

- `config/themes.php` — tema di default, opzioni globali
- Un `theme.json` per tema (in `resources/views/{slug}/theme.json`):
  ```json
  { "name": "academiaa", "extends": "default", "asset-path": "academiaa" }
  ```
- Helper `theme_url('css/variables.css')` — risolve automaticamente
  `public/{slug}/css/variables.css` per il tema attivo, senza dover
  costruire il percorso a mano
- Colore primario, logo e **tipografia** (famiglia del font e pesi —
  regular, medium, bold, ecc.) restano "design token": non nel
  database, ma in un file CSS per tema (vedi sotto) — cambiano
  raramente, versionati su git, senza bisogno di admin/DB per un
  aggiustamento di palette o font

Tutto il resto del contenuto (piani, staff, testimonianze, contatti,
testi delle sezioni) vive nel database, con `gym_id` come foreign key
diretta — vedi la spec di design per lo schema completo delle tabelle.

## Asset per palestra

```
public/{slug}/css/variables.css   (colori --bs-primary + tipografia --bs-font-*, sovrascrive Bootstrap)
public/{slug}/logo.png
public/{slug}/galeria1.jpg ... galeriaN.jpg
public/{slug}/team/{file}.jpg
```

I nomi dei file immagine sono **identici tra i temi** (es.
`galeria1.jpg` in ogni cartella) — cambia solo la cartella `{slug}`
(risolta da `theme_url()`) usata per comporre il percorso. Questo evita
di dover mantenere una lista di immagini per palestra nel codice.

## Riferimento

Per lo schema completo del database e la struttura delle view, vedi
`docs/superpowers/specs/2026-07-20-multi-tenant-gym-landing-design.md`.
