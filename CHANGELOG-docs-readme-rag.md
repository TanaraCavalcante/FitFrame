## [2026-09-23] - Documentazione dell'assistente RAG (README + docs/8)

### Aggiunto
- `docs/8-assistente-rag.md`: documentazione completa dell'assistente RAG lato FitFrame — divisione tra i due repository, flusso di una domanda, file coinvolti, cronologia, rate limiting, widget, configurazione, avvio in locale, test, problemi comuni e limiti noti.
- Sezione "Assistente RAG (fitframe-rag)" nel README: architettura a due repository, flusso della richiesta, configurazione `RAG_SERVICE_URL`/`RAG_SERVICE_TOKEN`, avvio in locale e comportamento di fallback quando il servizio non è raggiungibile.
- Riferimento al servizio esterno `fitframe-rag` in "Stack" e all'assistente di aiuto in "Funzionalità".

### Modificato
- README: indice, note sui test (`Http::fake()`), "Struttura del progetto" (`RagServiceClient`, `ChatMessage`) e "Documentazione" (link a `docs/8-assistente-rag.md` e al piano del chatbot).
- `docs/plan-chatbot-rag-gestionale.md` spostato in `docs/superpowers/plans/` insieme agli altri piani; link aggiornati.
