<?php
namespace App\Controllers;

use App\Core\Controller;

// Ensure AI assistant functions are loaded
require_once ROOT . '/functions/ai_assistant.php';

/**
 * AI Insights Controller (Admin only)
 */
class AiInsights extends Controller
{
    /**
     * Check if user is admin
     */
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/home/login');
        }
    }

    /**
     * Show AI insights page
     */
    public function index()
    {
        // Load models
        $sessionModel = $this->loadModel('ChatSession');
        $historyModel = $this->loadModel('AiChatHistory');

        // Get user ID from session
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role']; // Will be 'admin' due to check above

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

        // Handle new message from admin
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
            $user_message = trim($_POST['message']);

            if (!empty($user_message)) {
                // Add user message to chat history (database)
                $historyModel->saveMessage($sessionId, $userId, $userRole, 'user', $user_message);

                // Get AI response with admin context
                $ai_response = \getClaudeResponse($user_message, 'admin');

                // Add AI response to chat history (database)
                $historyModel->saveMessage($sessionId, $userId, $userRole, 'ai', $ai_response);

                // Update session title if it's still default and we have user message
                if ($session && in_array($session['title'], ['New Chat', 'Untitled Chat'])) {
                    // Generate title from first few words of user message (max 50 chars)
                    $title = substr(trim($user_message), 0, 50);
                    if (strlen(trim($user_message)) > 50) {
                        $title .= '...';
                    }
                    if (empty($title)) {
                        $title = 'New Chat';
                    }
                    $sessionModel->updateTitle($sessionId, $userId, $title);
                }

                // Redirect to avoid form resubmission on refresh
                $this->redirect('/ai/insights?session_id=' . $sessionId);
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

        $this->view->assign('chat_history', $formattedHistory);
        $this->view->assign('current_session_id', $sessionId);
        $this->view->assign('recent_sessions', $formattedRecentSessions);
        $this->view->assign('session_title', $session['title'] ?? 'New Chat');

        $this->view->render('ai_insights/index', 'layouts/admin');
    }

    /**
     * Create a new chat session
     */
    public function newSession()
    {
        // Only allow GET requests for new session (simple link)
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->redirect('/ai/insights');
            return;
        }

        // Load model
        $sessionModel = $this->loadModel('ChatSession');

        // Get user ID from session
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role']; // Will be 'admin' due to check above

        // Create new session
        $sessionId = $sessionModel->createSession($userId, $userRole, 'New Chat');

        // Redirect to new session
        if ($sessionId) {
            $this->redirect('/ai/insights?session_id=' . $sessionId);
        } else {
            // Fallback
            $this->redirect('/ai/insights');
        }
    }

    /**
     * Delete a chat session
     */
    public function deleteSession()
    {
        // Only allow POST requests for deletion
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/ai/insights');
            return;
        }

        // Load model
        $sessionModel = $this->loadModel('ChatSession');

        // Get user ID from session
        $userId = $_SESSION['user_id'];

        // Get session ID from POST
        if (!isset($_POST['session_id']) || !is_numeric($_POST['session_id'])) {
            $this->redirect('/ai/insights');
            return;
        }

        $sessionId = (int)$_POST['session_id'];

        // Delete session
        $sessionModel->deleteSession($sessionId, $userId);

        // Redirect to main AI insights page
        $this->redirect('/ai/insights');
    }
}