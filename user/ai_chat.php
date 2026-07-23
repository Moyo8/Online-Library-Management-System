<?php
require_once '../config.php';
require_once '../functions/ai_assistant.php';
init_session();

if ($_SESSION['user_role'] !== 'user') {
    header('Location: ../login.php');
    exit;
}

// Initialize chat session
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// Handle new message from user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $user_message = trim($_POST['message']);

    if (!empty($user_message)) {
        // Add user message to chat history
        $_SESSION['chat_history'][] = [
            'type' => 'user',
            'message' => $user_message,
            'time' => date('H:i')
        ];

        // Get AI response
        $ai_response = getClaudeResponse($user_message, 'user');

        // Add AI response to chat history
        $_SESSION['chat_history'][] = [
            'type' => 'ai',
            'message' => $ai_response,
            'time' => date('H:i')
        ];

        // Limit chat history to last 50 messages to prevent session bloat
        if (count($_SESSION['chat_history']) > 50) {
            $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -50);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Assistant - OLMS</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6f42c1;
            --light-bg: #f8f9fa;
            --chat-bg: #ffffff;
            --user-msg-bg: #0d6efd;
            --ai-msg-bg: #e9ecef;
            --border-radius: 18px;
            --max-width: 800px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            padding: 0;
            color: #333;
        }

        .chat-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .chat-header h1 {
            margin: 0 0 10px 0;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .chat-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1rem;
        }

        .chat-container {
            height: 600px;
            overflow-y: auto;
            padding: 20px;
            background-color: var(--light-bg);
            display: flex;
            flex-direction: column;
        }

        .messages-wrapper {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 20px;
            width: 100%;
            max-width: var(--max-width);
            margin: 0 auto;
        }

        .chat-message {
            display: flex;
            margin: 12px 0;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .chat-message.user {
            justify-content: flex-end;
        }

        .chat-message.ai {
            justify-content: flex-start;
        }

        .chat-message .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            margin: 0 12px;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .chat-message.user .avatar {
            background-color: var(--user-msg-bg);
            color: white;
        }

        .chat-message.ai .avatar {
            background-color: var(--secondary-color);
            color: white;
        }

        .chat-message .content {
            max-width: 75%;
            padding: 12px 18px;
            border-radius: var(--border-radius);
            word-wrap: break-word;
            line-height: 1.5;
            position: relative;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .chat-message.user .content {
            background-color: var(--user-msg-bg);
            color: white;
        }

        .chat-message.ai .content {
            background-color: var(--ai-msg-bg);
            color: #333;
            border: 1px solid #dee2e6;
        }

        .chat-message .time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 6px;
            display: block;
        }

        .chat-input-container {
            position: sticky;
            bottom: 20px;
            background-color: white;
            padding: 15px;
            border-radius: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: var(--max-width);
            margin: 0 auto;
            border: 1px solid #eee;
        }

        .chat-form {
            display: flex;
            gap: 10px;
        }

        #chatInput {
            flex: 1;
            padding: 12px 18px;
            border: 2px solid #eee;
            border-radius: 25px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
        }

        #chatInput:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
        }

        .send-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0 24px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            height: 48px;
        }

        .send-btn:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .send-btn:active {
            transform: translateY(0);
        }

        .send-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .placeholder-text {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .placeholder-text h5 {
            margin: 0 0 15px 0;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .suggestions {
            margin-top: 25px;
        }

        .suggestions h6 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 1rem;
        }

        .suggestion-chip {
            background: white;
            border: 2px solid #eee;
            border-radius: 25px;
            padding: 8px 16px;
            margin: 4px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .suggestion-chip:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .suggestion-chip:active {
            transform: translateY(0);
        }

        .loading-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            background-color: var(--ai-msg-bg);
            border-radius: var(--border-radius);
            max-width: 75%;
            margin-left: auto;
        }

        .loading-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            animation: bounce 1.4s infinite ease-in-out;
        }

        .loading-dot:nth-child(1) {
            animation-delay: 0s;
        }

        .loading-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .loading-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }

        .chat-footer {
            text-align: center;
            padding: 15px;
            color: #6c757d;
            font-size: 0.9rem;
            border-top: 1px solid #eee;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .chat-container {
                height: 500px;
                padding: 15px;
            }

            .chat-header h1 {
                font-size: 1.5rem;
            }

            .chat-message .content {
                max-width: 85%;
            }

            .chat-input-container {
                bottom: 15px;
                padding: 12px;
            }
        }

        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background-color: #f8f9fa;
            border-radius: 12px;
            max-width: fit-content;
            margin-left: auto;
        }

        .typing-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #adb5bd;
            animation: typingBounce 1.4s infinite ease-in-out;
        }

        .typing-dot:nth-child(1) { animation-delay: 0s; }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typingBounce {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-4px); }
        }
    </style>
</head>
<body>
    <div class="chat-header">
        <h1><i class="bi bi-robot"></i> Library Assistant</h1>
        <p>Your AI-powered library helper</p>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="chat-container">
                    <div class="messages-wrapper" id="messagesWrapper">
                        <?php if (empty($_SESSION['chat_history'])): ?>
                        <div class="placeholder-text">
                            <h5>Hello! I'm your library assistant. 📚</h5>
                            <p>I can help you find books, check availability, manage reservations, answer questions about our library services, and more.</p>

                            <div class="suggestions mt-4">
                                <h6>Try asking me:</h6>
                                <div class="d-flex flex-wrap gap-2 justify-content-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm suggestion-chip" data-query="What books do you have by F. Scott Fitzgerald?">Books by F. Scott Fitzgerald</button>
                                    <button type="button" class="btn btn-outline-primary btn-sm suggestion-chip" data-query="Is 1984 available?">Is 1984 available?</button>
                                    <button type="button" class="btn btn-outline-primary btn-sm suggestion-chip" data-query="How do I reserve a book?">How to reserve a book</button>
                                    <button type="button" class="btn btn-outline-primary btn-sm suggestion-chip" data-query="What are popular books right now?">Popular books</button>
                                    <button type="button" class="btn btn-outline-primary btn-sm suggestion-chip" data-query="How do I check my borrowed books?">My borrowed books</button>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <?php foreach ($_SESSION['chat_history'] as $message): ?>
                        <div class="chat-message <?= $message['type'] ?>">
                            <div class="avatar">
                                <?= $message['type'] === 'user' ? 'U' : 'AI' ?>
                            </div>
                            <div>
                                <div class="content"><?= nl2br(htmlspecialchars($message['message'])) ?></div>
                                <div class="time"><?= htmlspecialchars($message['time']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="chat-input-container">
                    <form method="POST" action="" id="chatForm" class="chat-form">
                        <input type="text" class="form-control" name="message" id="chatInput" placeholder="Ask me anything about the library..." autocomplete="off" required>
                        <button class="btn btn-primary send-btn" type="submit">
                            <i class="bi bi-send"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="chat-footer">
        <p>Powered by AI • OLMS Library System</p>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-scroll to bottom of chat
        function scrollToBottom() {
            const messagesWrapper = document.getElementById('messagesWrapper');
            if (messagesWrapper) {
                messagesWrapper.scrollTop = messagesWrapper.scrollHeight;
            }
        }

        // Scroll to bottom on page load
        window.addEventListener('DOMContentLoaded', scrollToBottom);

        // Scroll to bottom when new content is added (via MutationObserver)
        const messagesWrapper = document.getElementById('messagesWrapper');
        if (messagesWrapper) {
            const observer = new MutationObserver(() => {
                scrollToBottom();
            });
            observer.observe(messagesWrapper, { childList: true, subtree: true });
        }

        // Focus on input when page loads
        document.getElementById('chatInput').focus();

        // Handle form submission
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('chatInput');
            const message = input.value.trim();

            if (message === '') {
                return;
            }

            // Disable input and button while sending
            input.disabled = true;
            const submitButton = this.querySelector('.send-btn');
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';

            // Add user message to chat
            addMessageToChat(message, 'user');

            // Clear input
            input.value = '';

            // Simulate typing indicator
            showTypingIndicator();

            // Send AJAX request to get AI response
            const formData = new FormData();
            formData.append('message', message);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                // Extract the AI response from the returned HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const aiMessageElements = doc.querySelectorAll('.chat-message.ai .content');
                const aiMessage = aiMessageElements.length > 0 ?
                    aiMessageElements[aiMessageElements.length - 1].textContent.trim() :
                    "Sorry, I couldn't process that request.";

                // Hide typing indicator
                hideTypingIndicator();

                // Add AI response to chat
                addMessageToChat(aiMessage, 'ai');

                // Re-enable input and button
                input.disabled = false;
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="bi bi-send"></i>';
                input.focus();
            })
            .catch(error => {
                console.error('Error:', error);
                hideTypingIndicator();
                addMessageToChat("Sorry, there was an error processing your request. Please try again.", 'ai');

                // Re-enable input and button
                input.disabled = false;
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="bi bi-send"></i>';
                input.focus();
            });
        });

        // Allow Enter key to send message
        document.getElementById('chatInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('chatForm').dispatchEvent(new Event('submit'));
            }
        });

        // Handle suggestion chips
        document.addEventListener('DOMContentLoaded', function() {
            const suggestionChips = document.querySelectorAll('.suggestion-chip');
            suggestionChips.forEach(chip => {
                chip.addEventListener('click', function() {
                    const query = this.getAttribute('data-query');
                    const chatInput = document.getElementById('chatInput');
                    chatInput.value = query;
                    chatInput.focus();
                    // Trigger form submission
                    document.getElementById('chatForm').dispatchEvent(new Event('submit'));
                });
            });
        });

        // Functions to manage chat
        function addMessageToChat(message, type) {
            const messagesWrapper = document.getElementById('messagesWrapper');
            if (!messagesWrapper) return;

            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${type}`;

            const avatarDiv = document.createElement('div');
            avatarDiv.className = 'avatar';
            avatarDiv.textContent = type === 'user' ? 'U' : 'AI';

            const contentDiv = document.createElement('div');

            const contentTextDiv = document.createElement('div');
            contentTextDiv.className = 'content';
            contentTextDiv.innerHTML = nl2br(message); // Convert newlines to <br>

            const timeDiv = document.createElement('div');
            timeDiv.className = 'time';
            timeDiv.textContent = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

            contentDiv.appendChild(contentTextDiv);
            contentDiv.appendChild(timeDiv);

            messageDiv.appendChild(avatarDiv);
            messageDiv.appendChild(contentDiv);

            messagesWrapper.appendChild(messageDiv);

            // Scroll to bottom
            scrollToBottom();
        }

        function showTypingIndicator() {
            const messagesWrapper = document.getElementById('messagesWrapper');
            if (!messagesWrapper) return;

            const typingDiv = document.createElement('div');
            typingDiv.className = 'chat-message ai';
            typingDiv.id = 'typingIndicator';

            const avatarDiv = document.createElement('div');
            avatarDiv.className = 'avatar';
            avatarDiv.textContent = 'AI';

            const contentDiv = document.createElement('div');
            contentDiv.className = 'typing-indicator';

            for (let i = 0; i < 3; i++) {
                const dot = document.createElement('div');
                dot.className = 'typing-dot';
                contentDiv.appendChild(dot);
            }

            typingDiv.appendChild(avatarDiv);
            typingDiv.appendChild(contentDiv);

            messagesWrapper.appendChild(typingDiv);
            scrollToBottom();
        }

        function hideTypingIndicator() {
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        // Helper function to convert newlines to <br> tags
        function nl2br(str) {
            if (typeof str !== 'string') return '';
            return str.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br>' + '$2');
        }
    </script>
</body>
</html>