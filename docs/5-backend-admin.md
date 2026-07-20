# 5. Backend e amministrazione

## Visione

È previsto un backend, sempre all'interno dello stesso progetto
Laravel, che dia accesso a un'**area di amministrazione** per
modificare il contenuto di ogni pagina — senza passare da seeder,
Tinker o accesso diretto al database.

**Questa fase non è ancora implementata.** Le fasi descritte nelle doc
[2](2-projeto.md), [3](3-multi-tenant.md) e [4](4-ordem-secoes.md)
riguardano solo la lettura dei dati (pagine statiche popolate via
seeder). Questa doc descrive l'intento per cui lo schema del database
è già stato progettato in modo relazionale — pronto per essere gestito
da un admin, invece che riscritto in futuro.

## Cosa l'admin dovrà permettere di modificare

Per ogni palestra (`gym`), un utente amministratore collegato a quella
palestra dovrà poter modificare:

- **Contatti** (`contacts`) — indirizzo, telefono, whatsapp, instagram, orari
- **Testi delle sezioni** (`contents`) — headline, sottotitoli, testi descrittivi
- **Modalidades/Aulas** (`gym_classes`) — nome, descrizione, icona, ordine
- **Piani** (`plans` + `plan_features`) — nome, prezzo, evidenza, bullet
- **Testimonianze** (`testimonials`) — autore, testo, ordine
- **Staff/Personal trainer** (`personal_trainers`) — nome, specialità,
  foto (upload via UI, non più solo seeder)
- **Ordine delle sezioni** (`gym_sections`) — riordinare le 6 sezioni
  non fisse (vedi [4. Ordine delle sezioni](4-ordem-secoes.md))

## Cosa resta fuori dall'admin (per ora)

- **Colore primario e logo** — restano nel file `config/gyms/{slug}.php`,
  versionato su git (vedi [3. Multi-tenant](3-multi-tenant.md)) — non
  sono previsti nell'admin in questa visione, essendo design token
  di modifica rara
- **Dominio** (`domains`) — associazione dominio → palestra, gestita
  a livello infrastrutturale, non da un admin utente finale
- **Creazione di nuove palestre** — non è ancora definito se l'admin
  permetterà di creare una nuova palestra da zero o se resterà un
  processo manuale (nuova riga in `gyms`, seeder iniziale, dominio
  configurato a mano)

## Isolamento tra palestre

Ogni utente admin deve vedere e modificare **solo i dati della propria
palestra** (`gym_id`) — nessun accesso incrociato tra `academiaa`,
`academiab` e `academiac`. Il meccanismo esatto di autenticazione/
autorizzazione (guard, policy, ruolo per gym) sarà definito in una
prossima spec dedicata a questa fase.

## Form di contatto

Nella stessa fase in cui verrà costruito l'admin, è prevista anche
l'implementazione funzionale del form di contatto della sezione
`contact_cta` (oggi solo visivo) — salvataggio del lead e/o invio email
alla palestra.

## Prossimi passi

Questa doc descrive solo l'intento. L'implementazione effettiva
dell'admin (autenticazione, UI, upload di file, validazione) richiederà
una sua spec di design dedicata, seguendo lo stesso processo di
brainstorming usato per il multi-tenant.
