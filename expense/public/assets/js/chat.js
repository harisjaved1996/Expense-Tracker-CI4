(function () {
    const sidebar     = document.getElementById('chat-sidebar');
    const messages    = document.getElementById('chat-messages');
    const form        = document.getElementById('chat-form');
    const input       = document.getElementById('chat-input');
    const toggleBtn   = document.getElementById('chat-toggle-btn');
    const closeBtn    = document.getElementById('chat-close-btn');
    const clearBtn    = document.getElementById('chat-clear-btn');

    function openSidebar() {
        sidebar.classList.add('open');
        document.body.classList.add('chat-open');
        toggleBtn.classList.add('active');
        toggleBtn.setAttribute('aria-expanded', 'true');
        input.focus();
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        document.body.classList.remove('chat-open');
        toggleBtn.classList.remove('active');
        toggleBtn.setAttribute('aria-expanded', 'false');
    }

    function toggleSidebar() {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    }

    function scrollToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function appendMessage(role, text) {
        const el = document.createElement('div');
        el.className = 'chat-bubble chat-bubble-' + role;
        el.textContent = text;
        messages.appendChild(el);
        scrollToBottom();
        return el;
    }

    function showThinking() {
        const el = document.createElement('div');
        el.className = 'chat-bubble chat-bubble-thinking';
        el.textContent = 'Thinking…';
        messages.appendChild(el);
        scrollToBottom();
        return el;
    }

    async function sendMessage() {
        const text = input.value.trim();
        if (!text) return;

        appendMessage('user', text);
        input.value = '';
        input.disabled = true;

        const thinking = showThinking();

        try {
            const res = await fetch('/chat/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text }),
            });

            const data = await res.json();
            thinking.remove();

            if (data.reply) {
                appendMessage('ai', data.reply);
            } else {
                appendMessage('ai', data.error ?? 'Something went wrong. Please try again.');
            }
        } catch {
            thinking.remove();
            appendMessage('ai', 'Network error. Please check your connection.');
        } finally {
            input.disabled = false;
            input.focus();
        }
    }

    async function clearHistory() {
        try {
            await fetch('/chat/clear', { method: 'DELETE' });
        } catch {
            // silently ignore network errors on clear
        }
        messages.innerHTML = '';
    }

    toggleBtn.addEventListener('click', toggleSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    clearBtn.addEventListener('click', clearHistory);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        sendMessage();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSidebar();
    });
}());
