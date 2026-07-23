<?php
/**
 * User AI Chat View
 * Renders as a content fragment inside layouts/user.php
 */
?>

<style>
/* ========================================
   AI CHAT — Scoped Styles (User)
   Uses OLMS design system variables
   ======================================== */

.ai-chat-wrapper {
    display: flex;
    gap: 1.5rem;
    height: calc(100vh - 240px);
    min-height: 500px;
}

/* --- Chat Sessions Sidebar --- */
.ai-sessions-panel {
    width: 280px;
    flex-shrink: 0;
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid var(--gray-200);
}

[data-theme="dark"] .ai-sessions-panel {
    background: var(--gray-800);
    border-color: var(--gray-700);
}

.ai-sessions-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
}

[data-theme="dark"] .ai-sessions-header {
    background: linear-gradient(135deg, var(--gray-700) 0%, var(--gray-800) 100%);
    border-bottom-color: var(--gray-700);
}

.ai-sessions-header h6 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--gray-700);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

[data-theme="dark"] .ai-sessions-header h6 {
    color: var(--gray-300);
}

.ai-new-chat-btn {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    border: none;
    padding: 0.4rem 0.85rem;
    border-radius: var(--radius-xl);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.35rem;
    text-decoration: none;
}

.ai-new-chat-btn:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
    color: white;
    text-decoration: none;
}

.ai-sessions-list {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem;
}

.ai-sessions-list::-webkit-scrollbar { width: 4px; }
.ai-sessions-list::-webkit-scrollbar-track { background: transparent; }
.ai-sessions-list::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 4px; }
[data-theme="dark"] .ai-sessions-list::-webkit-scrollbar-thumb { background: var(--gray-600); }

.ai-session-item {
    padding: 0.75rem 1rem;
    border-radius: var(--radius-md);
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: var(--transition-fast);
    margin-bottom: 0.25rem;
    text-decoration: none;
    color: inherit;
    border: 1px solid transparent;
}

.ai-session-item:hover {
    background: var(--gray-100);
    border-color: var(--gray-200);
    text-decoration: none;
    color: inherit;
}

[data-theme="dark"] .ai-session-item:hover {
    background: var(--gray-700);
    border-color: var(--gray-600);
}

.ai-session-item.active {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    border-color: transparent;
    box-shadow: var(--shadow-sm);
}

.ai-session-item.active .ai-session-meta {
    color: rgba(255,255,255,0.7);
}

.ai-session-info {
    flex: 1;
    min-width: 0;
    overflow: hidden;
}

.ai-session-title {
    font-weight: 600;
    font-size: 0.875rem;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}

.ai-session-meta {
    font-size: 0.7rem;
    color: var(--gray-500);
    display: flex;
    gap: 0.5rem;
}

[data-theme="dark"] .ai-session-meta {
    color: var(--gray-400);
}

.ai-session-delete {
    background: transparent;
    border: none;
    color: var(--danger);
    padding: 0.25rem;
    border-radius: var(--radius-sm);
    cursor: pointer;
    opacity: 0;
    transition: var(--transition-fast);
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    flex-shrink: 0;
}

.ai-session-item:hover .ai-session-delete,
.ai-session-item.active .ai-session-delete {
    opacity: 0.7;
}

.ai-session-delete:hover {
    opacity: 1 !important;
    background: rgba(239, 68, 68, 0.1);
}

.ai-session-item.active .ai-session-delete {
    color: white;
}

.ai-session-item.active .ai-session-delete:hover {
    background: rgba(255,255,255,0.15);
}

.ai-sessions-empty {
    padding: 2rem 1rem;
    text-align: center;
    color: var(--gray-400);
    font-style: italic;
    font-size: 0.85rem;
}

/* --- Main Chat Panel --- */
.ai-chat-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    border: 1px solid var(--gray-200);
    min-width: 0;
}

[data-theme="dark"] .ai-chat-panel {
    background: var(--gray-800);
    border-color: var(--gray-700);
}

.ai-chat-topbar {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
}

[data-theme="dark"] .ai-chat-topbar {
    border-bottom-color: var(--gray-700);
}

.ai-chat-topbar-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.ai-chat-topbar-info h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: white;
}

.ai-chat-topbar-info small {
    font-size: 0.75rem;
    opacity: 0.8;
}

/* --- Messages Area --- */
.ai-messages-area {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    background: var(--gray-50);
}

[data-theme="dark"] .ai-messages-area {
    background: var(--gray-900);
}

.ai-messages-area::-webkit-scrollbar { width: 6px; }
.ai-messages-area::-webkit-scrollbar-track { background: transparent; }
.ai-messages-area::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 4px; }
[data-theme="dark"] .ai-messages-area::-webkit-scrollbar-thumb { background: var(--gray-600); }

.ai-msg {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    animation: aiMsgFadeIn 0.35s ease-out;
}

.ai-msg.user-msg {
    flex-direction: row-reverse;
}

@keyframes aiMsgFadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

.ai-msg-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    flex-shrink: 0;
    box-shadow: var(--shadow-sm);
}

.ai-msg.user-msg .ai-msg-avatar {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
}

.ai-msg.ai-msg-bot .ai-msg-avatar {
    background: linear-gradient(135deg, var(--secondary) 0%, #f472b6 100%);
    color: white;
}

.ai-msg-body {
    max-width: 72%;
    min-width: 0;
}

.ai-msg-bubble {
    padding: 0.85rem 1.15rem;
    border-radius: var(--radius-lg);
    line-height: 1.6;
    word-wrap: break-word;
    overflow-wrap: break-word;
    position: relative;
    font-size: 0.925rem;
}

.ai-msg.user-msg .ai-msg-bubble {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    border-bottom-right-radius: var(--radius-sm);
    box-shadow: var(--shadow-sm);
}

.ai-msg.ai-msg-bot .ai-msg-bubble {
    background: white;
    color: var(--gray-800);
    border: 1px solid var(--gray-200);
    border-bottom-left-radius: var(--radius-sm);
    box-shadow: var(--shadow-sm);
}

[data-theme="dark"] .ai-msg.ai-msg-bot .ai-msg-bubble {
    background: var(--gray-800);
    border-color: var(--gray-700);
    color: var(--gray-200);
}

.ai-msg-time {
    font-size: 0.7rem;
    color: var(--gray-400);
    margin-top: 0.35rem;
    display: block;
}

.ai-msg.user-msg .ai-msg-time {
    text-align: right;
}

/* --- Typing Indicator --- */
.ai-typing {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.ai-typing-dots {
    display: flex;
    gap: 4px;
    padding: 0.85rem 1.15rem;
    background: white;
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    box-shadow: var(--shadow-sm);
}

[data-theme="dark"] .ai-typing-dots {
    background: var(--gray-800);
    border-color: var(--gray-700);
}

.ai-typing-dots span {
    width: 7px;
    height: 7px;
    background: var(--gray-400);
    border-radius: 50%;
    animation: aiTypingBounce 1.4s infinite ease-in-out;
}

.ai-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.ai-typing-dots span:nth-child(3) { animation-delay: 0.4s; }

@keyframes aiTypingBounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-5px); }
}

/* --- Welcome / Placeholder --- */
.ai-welcome {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
}

.ai-welcome-icon {
    font-size: 3.5rem;
    margin-bottom: 1.25rem;
    opacity: 0.85;
    animation: aiWelcomePulse 3s infinite ease-in-out;
}

@keyframes aiWelcomePulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.ai-welcome h4 {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
}

[data-theme="dark"] .ai-welcome h4 {
    color: var(--gray-200);
}

.ai-welcome p {
    color: var(--gray-500);
    max-width: 420px;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.ai-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
    justify-content: center;
    max-width: 520px;
}

.ai-suggestion-chip {
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-xl);
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    cursor: pointer;
    transition: var(--transition);
    color: var(--gray-700);
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

[data-theme="dark"] .ai-suggestion-chip {
    background: var(--gray-800);
    border-color: var(--gray-700);
    color: var(--gray-300);
}

.ai-suggestion-chip:hover {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

/* --- Input Bar --- */
.ai-input-bar {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gray-200);
    background: white;
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

[data-theme="dark"] .ai-input-bar {
    background: var(--gray-800);
    border-top-color: var(--gray-700);
}

.ai-input-bar input[type="text"] {
    flex: 1;
    padding: 0.85rem 1.25rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-xl);
    font-size: 0.95rem;
    outline: none;
    transition: var(--transition);
    background: var(--gray-50);
    color: var(--gray-900);
}

[data-theme="dark"] .ai-input-bar input[type="text"] {
    background: var(--gray-700);
    border-color: var(--gray-600);
    color: var(--gray-100);
}

.ai-input-bar input[type="text"]:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(91, 33, 182, 0.15);
    background: white;
}

[data-theme="dark"] .ai-input-bar input[type="text"]:focus {
    background: var(--gray-800);
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
}

.ai-input-bar input[type="text"]::placeholder {
    color: var(--gray-400);
}

.ai-send-btn {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    border: none;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
    box-shadow: var(--shadow-sm);
}

.ai-send-btn:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: var(--shadow-md);
}

.ai-send-btn:active {
    transform: translateY(0) scale(0.98);
}

.ai-send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.ai-send-btn .spinner-border {
    width: 1.1rem;
    height: 1.1rem;
    border-width: 2px;
}

/* --- Responsive --- */
@media (max-width: 900px) {
    .ai-chat-wrapper {
        flex-direction: column;
        height: auto;
        min-height: calc(100vh - 200px);
    }
    .ai-sessions-panel {
        width: 100%;
        max-height: 180px;
    }
    .ai-chat-panel {
        min-height: 400px;
    }
}

@media (max-width: 576px) {
    .ai-chat-wrapper {
        gap: 0.75rem;
    }
    .ai-messages-area {
        padding: 1rem;
    }
    .ai-input-bar {
        padding: 0.75rem 1rem;
    }
    .ai-msg-body {
        max-width: 85%;
    }
}
</style>

<!-- Page Header -->
<div class="mb-4 dashboard-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-2 fw-bold">🤖 AI Assistant</h1>
            <p class="text-muted mb-0">Get book recommendations, reading insights, and library help.</p>
        </div>
    </div>
</div>

<!-- Chat Interface -->
<div class="ai-chat-wrapper">
    <!-- Sessions Sidebar -->
    <div class="ai-sessions-panel">
        <div class="ai-sessions-header">
            <h6>💬 Chats</h6>
            <a href="<?= url('/user/ai/new') ?>" class="ai-new-chat-btn">
                ＋ New
            </a>
        </div>
        <div class="ai-sessions-list">
            <?php if (empty($recent_sessions)): ?>
                <div class="ai-sessions-empty">No conversations yet</div>
            <?php else: ?>
                <?php foreach ($recent_sessions as $sess): ?>
                    <a href="<?= url('/user/ai?session_id=' . $sess['id']) ?>"
                       class="ai-session-item <?= ($sess['id'] == $current_session_id) ? 'active' : '' ?>">
                        <div class="ai-session-info">
                            <span class="ai-session-title"><?= htmlspecialchars($sess['title']) ?></span>
                            <span class="ai-session-meta">
                                <span><?= date('M j', strtotime($sess['updated_at'])) ?></span>
                                <span><?= $sess['message_count'] ?> msgs</span>
                            </span>
                        </div>
                        <form method="POST" action="<?= url('/user/ai/delete') ?>"
                              onclick="event.stopPropagation();"
                              onsubmit="return confirm('Delete this conversation?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="session_id" value="<?= $sess['id'] ?>">
                            <button type="submit" class="ai-session-delete" title="Delete">🗑</button>
                        </form>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Chat Panel -->
    <div class="ai-chat-panel">
        <div class="ai-chat-topbar">
            <div class="ai-chat-topbar-icon">🤖</div>
            <div class="ai-chat-topbar-info">
                <h5><?= htmlspecialchars($session_title ?? 'New Chat') ?></h5>
                <small>Your personal library assistant</small>
            </div>
        </div>

        <div class="ai-messages-area" id="aiMessagesArea">
            <?php if (empty($chat_history)): ?>
                <div class="ai-welcome">
                    <div class="ai-welcome-icon">🤖</div>
                    <h4>AI Assistant</h4>
                    <p>I can help you discover books, get reading recommendations, check your library status, and answer questions about the library.</p>
                    <div class="ai-suggestions">
                        <span class="ai-suggestion-chip" data-suggestion="Recommend me a good fiction book">📖 Book recommendations</span>
                        <span class="ai-suggestion-chip" data-suggestion="What books are available right now?">📚 Available books</span>
                        <span class="ai-suggestion-chip" data-suggestion="Do I have any overdue books?">⏰ Overdue check</span>
                        <span class="ai-suggestion-chip" data-suggestion="What are the most popular genres?">🏷️ Popular genres</span>
                        <span class="ai-suggestion-chip" data-suggestion="How does the reservation system work?">❓ Library help</span>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($chat_history as $msg): ?>
                    <div class="ai-msg <?= $msg['type'] === 'user' ? 'user-msg' : 'ai-msg-bot' ?>">
                        <div class="ai-msg-avatar">
                            <?= $msg['type'] === 'user' ? '👤' : '🤖' ?>
                        </div>
                        <div class="ai-msg-body">
                            <div class="ai-msg-bubble"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                            <span class="ai-msg-time"><?= $msg['time'] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="ai-input-bar">
            <form method="POST" action="<?= url('/user/ai?session_id=' . $current_session_id) ?>" id="aiChatForm" style="display:flex;gap:0.75rem;width:100%;align-items:center;">
                <?= csrf_field() ?>
                <input type="text" name="message" id="aiChatInput"
                       placeholder="Ask me anything about the library..."
                       autocomplete="off" required>
                <button type="submit" class="ai-send-btn" id="aiSendBtn" title="Send">
                    <span class="send-icon">➤</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-scroll messages to bottom
    const messagesArea = document.getElementById('aiMessagesArea');
    if (messagesArea) {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }

    // Suggestion chips — fill input and submit
    document.querySelectorAll('.ai-suggestion-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
            const input = document.getElementById('aiChatInput');
            if (input) {
                input.value = this.getAttribute('data-suggestion');
                input.focus();
            }
        });
    });

    // Form submit — disable button and show spinner
    const form = document.getElementById('aiChatForm');
    const sendBtn = document.getElementById('aiSendBtn');
    if (form && sendBtn) {
        form.addEventListener('submit', function(e) {
            const input = document.getElementById('aiChatInput');
            if (!input || !input.value.trim()) {
                e.preventDefault();
                return;
            }
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        });
    }
});
</script>