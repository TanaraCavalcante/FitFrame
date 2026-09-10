# Changelog - feature/rag

Tutte le modifiche rilevanti al branch `feature/rag` sono documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/it/1.1.0/).

## [2026-09-10] - Revisione del piano: architettura a due componenti

### Modificato
- `docs/plan-chatbot-rag-gestionale.md`: rivista l'architettura da "tutto in Laravel" a due repository separati — `fitframe-help-rag` (nuovo progetto Python, non ancora creato, processo nativo venv) possiede l'intera pipeline RAG (base di conoscenza Markdown, chunking, embeddings locali, FAISS, chiamata a Groq per la risposta); FitFrame resta responsabile solo di autenticazione, rotta `POST backend/chat` con throttle, client HTTP verso il servizio Python, persistenza di `chat_messages` e widget. Verificato (analizzando il progetto locale `ai-chat`) che Groq copre la chat completion ma non gli embeddings, da cui la revisione. Aggiunte note esplicite su: checkpoint di conferma design prima della fase frontend, nessuna condivisione di file Markdown o di database tra i due repository (solo contratto HTTP).

## [2026-09-10] - Piano del chatbot RAG di aiuto contestuale

### Aggiunto
- `docs/plan-chatbot-rag-gestionale.md`: piano di implementazione del chatbot AI di aiuto contestuale nel gestionale — architettura RAG interamente in Laravel 12 (nessun servizio Python/Docker, nessun upgrade a Laravel 13), Groq come provider per chat completions ed embeddings, base di conoscenza in Markdown, ricerca per cosine similarity in PHP, cronologia per utente, widget di chat come Blade component.
