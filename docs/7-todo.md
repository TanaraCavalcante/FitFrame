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

- [ ] Creare/estendere il seeder con le righe `contents` (chiave/valore)
  specifiche per **Zenflow** e **Iron House**, una volta che tutte le
  sections siano state costruite sul tema Pulse.
- [ ] Verificare che ogni chiave usata nelle view abbia un default
  sensato (testo Pulse) prima di aggiungere le righe degli altri gym.
