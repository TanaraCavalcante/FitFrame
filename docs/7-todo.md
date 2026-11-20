# 7. TODO — ordine di costruzione dei contenuti

## Decisione

Si costruisce prima il tema **Pulse** per intero (elements + sections),
popolando i testi liberi (`contents`, chiave/valore) direttamente per
questo gym. Questi testi Pulse fungono anche da **default applicativo**:
sono quelli che compaiono già come fallback nelle view (secondo
parametro di `$gym->content($key, $default)`), quindi qualsiasi gym
senza una riga propria in `contents` per una data chiave eredita
automaticamente il testo di Pulse.

## Da fare in seguito

- [x] Creare/estendere il seeder con le righe `contents` (chiave/valore)
  specifiche per **Zenflow** — fatto in `GymSeeder` (14 chiavi: hero,
  titoli sezione, CTA, footer).
- [ ] **Iron House non ha ancora contenuto proprio nel seeder**: niente
  `contents`, niente `classes`, `plans`, `team`, `testimonials`. I testi
  (`contents`) cadono sul default Pulse via `$gym->content($key, $default)`,
  ma corsi/piani/team/testimonianze sono tabelle dedicate per `gym_id`
  senza alcuna eredità — oggi `iron-house.test` mostra quelle sezioni
  vuote (nessun errore, ma vuote). Da popolare in `GymSeeder` seguendo
  lo stesso schema di Zenflow, con un tono coerente col tema Iron House
  (palestra "hardcore"/powerlifting, a giudicare dal nome).
- [x] Verificare che ogni chiave usata nelle view abbia un default
  sensato (testo Pulse): confermato — ogni `$gym->content('chiave', ...)`
  nelle view di `resources/views/base/` ha già un secondo argomento con
  il testo Pulse come fallback, quindi un gym senza quella riga non
  rompe la pagina.

## Miglioramenti futuri (performance)

- [ ] **Cache della risoluzione del Gym per dominio** (`App\Http\Middleware\ResolveGym`):
  oggi ogni richiesta pubblica rifà la stessa query con più `with()` (contents, contact,
  media, gymClasses, plans, personalTrainers, testimonials) per lo stesso dominio.
  Da valutare quando il progetto avrà traffico reale in produzione, non prima:
  - Cache per dominio (`Cache::remember("gym.domain.{$host}", ...)`), driver già
    configurato (`CACHE_STORE=database`)
  - Invalidazione da gestire esplicitamente ad ogni save che tocca il Gym (contenuto,
    corsi, piani, team, testimonianze, media, ordine sezioni) — probabilmente via
    Observer sui model coinvolti, non dimenticata nei singoli controller
  - Non implementare finché il traffico reale non giustifica il costo di complessità
    dell'invalidazione
