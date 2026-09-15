<div class="chat-widget" id="chat-widget" data-chat-url="{{ route('backend.chat') }}">
    <button type="button" class="chat-widget-fab" id="chat-widget-open" aria-label="Apri assistente di aiuto">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 5h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H9l-4 4v-4H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
        </svg>
    </button>

    <div class="chat-widget-panel" id="chat-widget-panel" role="dialog" aria-label="Assistente FitFrame">
        <div class="chat-widget-header">
            <div class="chat-widget-avatar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 5h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H9l-4 4v-4H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                </svg>
            </div>
            <div class="chat-widget-titles">
                <div class="chat-widget-title">Assistente FitFrame</div>
                <div class="chat-widget-subtitle">Aiuto sull'uso del gestionale</div>
            </div>
            <button type="button" class="chat-widget-close" id="chat-widget-close" aria-label="Chiudi assistente">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        <div class="chat-widget-messages" id="chat-widget-messages">
            <div class="chat-widget-msg is-assistant">
                <div class="chat-widget-msg-avatar">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 5h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H9l-4 4v-4H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                    </svg>
                </div>
                <div class="chat-widget-bubble">Ciao! Sono l'assistente del gestionale FitFrame. Chiedimi pure come
                    usare una funzionalità.</div>
            </div>
        </div>

        <form class="chat-widget-form" id="chat-widget-form">
            <input type="text" class="chat-widget-input" id="chat-widget-input" placeholder="Scrivi una domanda..."
                autocomplete="off" required>
            <button type="submit" class="chat-widget-send" id="chat-widget-send" aria-label="Invia domanda">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 2 11 13" />
                    <path d="M22 2 15 22l-4-9-9-4 20-7Z" />
                </svg>
            </button>
        </form>
    </div>
</div>
