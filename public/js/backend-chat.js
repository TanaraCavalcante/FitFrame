document.addEventListener('DOMContentLoaded', function () {
    var widget = document.getElementById('chat-widget');

    if (!widget) {
        return;
    }

    var openBtn = document.getElementById('chat-widget-open');
    var closeBtn = document.getElementById('chat-widget-close');
    var form = document.getElementById('chat-widget-form');
    var input = document.getElementById('chat-widget-input');
    var sendBtn = document.getElementById('chat-widget-send');
    var messages = document.getElementById('chat-widget-messages');
    var loadMoreBtn = document.getElementById('chat-widget-load-more');
    var chatUrl = widget.dataset.chatUrl;
    var historyUrl = widget.dataset.historyUrl;
    var oldestMessageId = widget.dataset.oldestMessageId || null;
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    var assistantAvatarSvg = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H9l-4 4v-4H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/></svg>';

    openBtn.addEventListener('click', function () {
        widget.classList.add('is-open');
        input.focus();
        messages.scrollTop = messages.scrollHeight;
    });

    closeBtn.addEventListener('click', function () {
        widget.classList.remove('is-open');
    });

    function appendMessage(role, text) {
        var row = document.createElement('div');
        row.className = 'chat-widget-msg is-' + role;

        if (role !== 'user') {
            var avatar = document.createElement('div');
            avatar.className = 'chat-widget-msg-avatar';
            avatar.innerHTML = assistantAvatarSvg;
            row.appendChild(avatar);
        }

        var bubble = document.createElement('div');
        bubble.className = 'chat-widget-bubble';
        bubble.textContent = text;
        row.appendChild(bubble);

        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;
    }

    function appendTyping() {
        var row = document.createElement('div');
        row.className = 'chat-widget-msg is-assistant';
        row.id = 'chat-widget-typing-row';

        var avatar = document.createElement('div');
        avatar.className = 'chat-widget-msg-avatar';
        avatar.innerHTML = assistantAvatarSvg;

        var bubble = document.createElement('div');
        bubble.className = 'chat-widget-bubble chat-widget-typing';
        bubble.innerHTML = '<span></span><span></span><span></span>';

        row.appendChild(avatar);
        row.appendChild(bubble);
        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;
    }

    function removeTyping() {
        var row = document.getElementById('chat-widget-typing-row');

        if (row) {
            row.remove();
        }
    }

    function buildHistoryPair(item) {
        var pair = document.createElement('div');
        pair.className = 'chat-widget-history-pair';
        pair.dataset.messageId = item.id;

        var userRow = document.createElement('div');
        userRow.className = 'chat-widget-msg is-user';
        var userBubble = document.createElement('div');
        userBubble.className = 'chat-widget-bubble';
        userBubble.textContent = item.question;
        userRow.appendChild(userBubble);

        var assistantRow = document.createElement('div');
        assistantRow.className = 'chat-widget-msg is-assistant';
        var avatar = document.createElement('div');
        avatar.className = 'chat-widget-msg-avatar';
        avatar.innerHTML = assistantAvatarSvg;
        var assistantBubble = document.createElement('div');
        assistantBubble.className = 'chat-widget-bubble';
        assistantBubble.textContent = item.answer;
        assistantRow.appendChild(avatar);
        assistantRow.appendChild(assistantBubble);

        pair.appendChild(userRow);
        pair.appendChild(assistantRow);

        return pair;
    }

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function () {
            if (!oldestMessageId) {
                loadMoreBtn.hidden = true;
                return;
            }

            loadMoreBtn.disabled = true;
            var previousScrollHeight = messages.scrollHeight;

            fetch(historyUrl + '?before_id=' + encodeURIComponent(oldestMessageId), {
                headers: { 'Accept': 'application/json' },
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    var items = data.messages || [];
                    var insertAfter = loadMoreBtn;

                    items.forEach(function (item) {
                        var pairEl = buildHistoryPair(item);
                        insertAfter.insertAdjacentElement('afterend', pairEl);
                        insertAfter = pairEl;
                    });

                    if (items.length > 0) {
                        oldestMessageId = items[0].id;
                    }

                    loadMoreBtn.hidden = !data.has_more;
                    messages.scrollTop = messages.scrollHeight - previousScrollHeight;
                })
                .catch(function () {
                    loadMoreBtn.hidden = true;
                })
                .finally(function () {
                    loadMoreBtn.disabled = false;
                });
        });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        var domanda = input.value.trim();

        if (!domanda) {
            return;
        }

        appendMessage('user', domanda);
        input.value = '';
        input.disabled = true;
        sendBtn.disabled = true;
        appendTyping();

        fetch(chatUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ domanda: domanda }),
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                removeTyping();

                if (result.ok && result.data.risposta) {
                    appendMessage('assistant', result.data.risposta);
                } else {
                    appendMessage('error', result.data.error || 'Assistente temporaneamente non disponibile.');
                }
            })
            .catch(function () {
                removeTyping();
                appendMessage('error', 'Assistente temporaneamente non disponibile.');
            })
            .finally(function () {
                input.disabled = false;
                sendBtn.disabled = false;
                input.focus();
            });
    });
});
