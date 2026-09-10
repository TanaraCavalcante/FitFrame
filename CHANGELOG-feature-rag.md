# Changelog - feature/rag

Tutte le modifiche rilevanti al branch `feature/rag` sono documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/it/1.1.0/).

## [2026-09-10] - Piano del chatbot RAG di aiuto contestuale

### Aggiunto
- `docs/plan-chatbot-rag-gestionale.md`: piano di implementazione del chatbot AI di aiuto contestuale nel gestionale — architettura RAG interamente in Laravel 12 (nessun servizio Python/Docker, nessun upgrade a Laravel 13), Groq come provider per chat completions ed embeddings, base di conoscenza in Markdown, ricerca per cosine similarity in PHP, cronologia per utente, widget di chat come Blade component.
