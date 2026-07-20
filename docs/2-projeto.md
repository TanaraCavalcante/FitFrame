# 2. Progetto

## Cos'è FitFrame

FitFrame è un'applicazione Laravel che genera **landing page per
palestre** (academie). Non è una landing page singola, ma una
piattaforma che ospita **più palestre diverse** nella stessa
applicazione, ognuna con il proprio dominio, colore, logo e contenuto —
ma tutte costruite sopra la **stessa struttura di pagina**.

L'idea centrale: un solo "tema principale" (la struttura HTML/Blade,
le sezioni, il layout) da cui ogni palestra eredita tutto, cambiando
solo ciò che la rende unica (colore, logo, immagini, testi, ordine
delle sezioni).

## Struttura della pagina

La landing page di ogni palestra è composta da 9 sezioni, sempre nello
stesso ordine di base (header e footer fissi, le altre riordinabili —
vedi [4. Ordine delle sezioni](4-ordem-secoes.md)):

1. **Header/Navbar** — logo, menu (Início · Modalidades · Planos ·
   Equipe · Contato), CTA "Matricule-se", sticky allo scroll
2. **Hero** — immagine di sfondo, headline, sottotitolo, doppia CTA
3. **Modalidades/Aulas** — grid di card con le attività offerte
4. **Planos/Preços** — 3 card di piano, quello centrale in evidenza
5. **Estrutura/Galeria** — foto dello spazio/attrezzatura
6. **Equipe/Personal trainers** — card con foto, nome, specialità
7. **Depoimentos** — testimonianze degli allievi
8. **CTA finale + Contato** — form di contatto (visivo, non ancora
   funzionante) + mappa/orari
9. **Footer** — link, social, orari, indirizzo

## Come funziona nella pratica

- Ogni palestra ha un proprio dominio (es. `academiaa.test` in
  locale, un dominio reale in produzione)
- L'applicazione riconosce quale palestra servire in base al dominio
  della richiesta (vedi [3. Multi-tenant](3-multi-tenant.md))
- Le stesse 9 view Blade sono usate per tutte le palestre — nessuna
  view duplicata per tema
- Colore primario e logo restano in file di configurazione PHP
  (versionati su git); tutto il resto del contenuto (piani, staff,
  testimonianze, contatti, testi) vive nel database, in tabelle
  relazionali dedicate per sezione

## Fasi del progetto

**Fase attuale (questa spec):** pagine statiche — il contenuto esiste
già nel database (popolato via seeder), nessuna scrittura da parte
dell'utente finale, nessun pannello admin.

**Fase futura:** un backend con accesso admin per modificare il
contenuto di ogni pagina senza toccare codice/seeder (vedi
[5. Backend e amministrazione](5-backend-admin.md)), oltre a rendere
funzionante il form di contatto.
