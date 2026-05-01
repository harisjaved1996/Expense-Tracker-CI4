<div id="chat-sidebar" role="dialog" aria-label="SpendWise AI Chat">
    <div class="chat-header">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-stars" style="color:var(--gold);font-size:1.1rem"></i>
            <span class="fw-600">SpendWise AI</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button id="chat-clear-btn" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.75rem">Clear</button>
            <button id="chat-close-btn" class="btn-icon" aria-label="Close chat">&times;</button>
        </div>
    </div>

    <div id="chat-messages" class="d-flex flex-column"></div>

    <div class="chat-footer">
        <form id="chat-form" class="d-flex gap-2" autocomplete="off">
            <input
                id="chat-input"
                type="text"
                class="form-control form-control-sm"
                placeholder="Ask anything about your expenses…"
                aria-label="Chat message"
            >
            <button type="submit" class="btn btn-gold btn-sm px-3">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
    </div>
</div>
