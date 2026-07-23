<?php
namespace App\Controllers;

use App\Core\Controller;

// Ensure AI assistant functions are loaded
require_once ROOT . '/functions/ai_assistant.php';

/**
 * User AI Assistant Controller
 */
class UserAi extends Controller
{
    /**
     * Check if user is logged in
     */
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/home/login');
        }
    }

    /**
     * Show AI assistant chat interface
     */
    public function index()
    {
        // Load models
        $sessionModel = $this->loadModel('ChatSession');
        $historyModel = $this->loadModel('AiChatHistory');

        // Get user ID from session
        $userId = $_SESSION['user_id'];
        $userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user';

        // Handle session selection or creation
        $sessionId = null;

        // Check if we're loading a specific session
        if (isset($_GET['session_id']) && is_numeric($_GET['session_id'])) {
            $sessionId = (int)$_GET['session_id'];
            $session = $sessionModel->getSession($sessionId, $userId);
            if (!$session) {
                // Invalid session, create new one
                $sessionId = null;
            }
        }

        // If no valid session selected, get/create the most recent session
        if (!$sessionId) {
            $recentSessions = $sessionModel->getRecentSessions($userId, 1);
            if (!empty($recentSessions)) {
                $sessionId = $recentSessions[0]['id'];
            } else {
                // Create new session if none exist
                $sessionId = $sessionModel->createSession($userId, $userRole, 'New Chat');
                // If model creation fails, we'll let it propagate as an error rather than fall back to raw DB
                // The createSession method should handle its own errors
            }
        }

        // Get session info
        $session = $sessionModel->getSession($sessionId, $userId);
        if (!$session) {
            // Session not found or access denied, create new
            $sessionId = $sessionModel->createSession($userId, $userRole, 'New Chat');
            $session = $sessionModel->getSession($sessionId, $userId);
        }

        // Handle POST request (when user sends a message)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
            $message = trim($_POST['message']);
            if (!empty($message)) {
                // Add user message to chat history (database)
                $historyModel->saveMessage($sessionId, $userId, $userRole, 'user', $message);

                // Get AI response
                $ai_response = \getClaudeResponse($message, 'user');

                // Add AI response to chat history (database)
                $historyModel->saveMessage($sessionId, $userId, $userRole, 'ai', $ai_response);

                // Update session title if it's still default and we have user message
                if ($session && in_array($session['title'], ['New Chat', 'Untitled Chat'])) {
                    // Generate title from first few words of user message (max 50 chars)
                    $title = substr(trim($message), 0, 50);
                    if (strlen(trim($message)) > 50) {
                        $title .= '...';
                    }
                    if (empty($title)) {
                        $title = 'New Chat';
                    }
                    $sessionModel->updateTitle($sessionId, $userId, $title);
                }

                // Redirect to avoid form resubmission on refresh
                $this->redirect('/user/ai?session_id=' . $sessionId);
                return; // Important: exit after redirect
            }
        }

        // Get chat history for this session
        $chatHistory = $historyModel->getHistory($sessionId, 50);

        // Format chat history for view
        $formattedHistory = [];
        foreach ($chatHistory as $msg) {
            $formattedHistory[] = [
                'type' => $msg['message_type'],
                'message' => $msg['message'],
                'time' => date('H:i', strtotime($msg['created_at']))
            ];
        }

        // Get recent sessions for sidebar
        $recentSessions = $sessionModel->getRecentSessions($userId, 10);
        $formattedRecentSessions = [];
        foreach ($recentSessions as $sess) {
            $formattedRecentSessions[] = [
                'id' => $sess['id'],
                'title' => $sess['title'],
                'updated_at' => $sess['updated_at'],
                'message_count' => $historyModel->getHistoryCount($sess['id'])
            ];
        }

        // Assign data to view
        $this->view->assign('chat_history', $formattedHistory);
        $this->view->assign('current_session_id', $sessionId);
        $this->view->assign('recent_sessions', $formattedRecentSessions);
        $this->view->assign('session_title', $session['title'] ?? 'New Chat');

        // Render the user AI view
        $this->view->render('user_ai/index', 'layouts/user');
    }

    /**
     * Create a new chat session
     */
    public function newSession()
    {
        // Only allow GET requests for new session (simple link)
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->redirect('/user/ai');
            return;
        }

        // Load model
        $sessionModel = $this->loadModel('ChatSession');

        // Get user ID from session
        $userId = $_SESSION['user_id'];
        $userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user';

        // Create new session
        $sessionId = $sessionModel->createSession($userId, $userRole, 'New Chat');

        // Redirect to new session
        if ($sessionId) {
            $this->redirect('/user/ai?session_id=' . $sessionId);
        } else {
            // Fallback
            $this->redirect('/user/ai');
        }
    }

    /**
     * Delete a chat session
     */
    public function deleteSession()
    {
        // Only allow POST requests for deletion
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/ai');
            return;
        }

        // Load model
        $sessionModel = $this->loadModel('ChatSession');

        // Get user ID from session
        $userId = $_SESSION['user_id'];

        // Get session ID from POST
        if (!isset($_POST['session_id']) || !is_numeric($_POST['session_id'])) {
            $this->redirect('/user/ai');
            return;
        }

        $sessionId = (int)$_POST['session_id'];

        // Delete session
        $sessionModel->deleteSession($sessionId, $userId);

        // Redirect to main AI page (will show most recent session or create new)
        $this->redirect('/user/ai');
    }
}