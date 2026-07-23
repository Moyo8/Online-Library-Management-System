<?php
namespace App\Models;

use App\Core\Model;

/**
 * AI Chat History Model (Session-Based)
 */
class AiChatHistory extends Model {
    /**
     * Save a chat message to history within a session
     * @param int $sessionId Session ID
     * @param int $userId User ID
     * @param string $userRole User role (user/admin/librarian)
     * @param string $messageType Type of message (user/ai)
     * @param string $message The message content
     * @return int|false Message ID on success, false on failure
     */
    public function saveMessage($sessionId, $userId, $userRole, $messageType, $message) {
        // Validate inputs
        if (empty($sessionId) || !is_numeric($sessionId)) {
            return false;
        }

        if (empty($userId) || !is_numeric($userId)) {
            return false;
        }

        if (!in_array($userRole, ['user', 'admin', 'librarian'])) {
            return false;
        }

        if (!in_array($messageType, ['user', 'ai'])) {
            return false;
        }

        if (empty($message)) {
            return false;
        }

        $sql = "INSERT INTO ai_chat_history (session_id, user_id, user_role, message_type, message)
                VALUES (?, ?, ?, ?, ?)";
        $params = [$sessionId, $userId, $userRole, $messageType, $message];

        try {
            $this->query($sql, $params);
            return $this->lastInsertId();
        } catch (\Exception $e) {
            error_log("Error saving AI chat history: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get chat history for a session
     * @param int $sessionId Session ID
     * @param int $limit Number of messages to retrieve (default 50)
     * @param int $offset Offset for pagination
     * @return array Chat history messages (oldest first)
     */
    public function getHistory($sessionId, $limit = 50, $offset = 0) {
        if (empty($sessionId) || !is_numeric($sessionId)) {
            return [];
        }

        $sql = "SELECT id, message_type, message, created_at
                FROM ai_chat_history
                WHERE session_id = ?
                ORDER BY created_at ASC
                LIMIT ? OFFSET ?";
        $params = [$sessionId, $limit, $offset];

        try {
            $result = $this->findAll($sql, $params);
            return $result ?: [];
        } catch (\Exception $e) {
            error_log("Error retrieving AI chat history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get chat history count for a session
     * @param int $sessionId Session ID
     * @return int Total number of messages
     */
    public function getHistoryCount($sessionId) {
        if (empty($sessionId) || !is_numeric($sessionId)) {
            return 0;
        }

        $sql = "SELECT COUNT(*) as count FROM ai_chat_history WHERE session_id = ?";
        $params = [$sessionId];

        try {
            $result = $this->find($sql, $params);
            return $result ? (int)$result['count'] : 0;
        } catch (\Exception $e) {
            error_log("Error counting AI chat history: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Clear chat history for a session
     * @param int $sessionId Session ID
     * @return bool Success status
     */
    public function clearHistory($sessionId) {
        if (empty($sessionId) || !is_numeric($sessionId)) {
            return false;
        }

        $sql = "DELETE FROM ai_chat_history WHERE session_id = ?";
        $params = [$sessionId];

        try {
            $this->query($sql, $params);
            return $this->rowCount() >= 0; // True even if 0 rows deleted
        } catch (\Exception $e) {
            error_log("Error clearing AI chat history: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the latest message from a session (for preview)
     * @param int $sessionId Session ID
     * @return array|null Latest message or null
     */
    public function getLatestMessage($sessionId) {
        if (empty($sessionId) || !is_numeric($sessionId)) {
            return null;
        }

        $sql = "SELECT message_type, message, created_at
                FROM ai_chat_history
                WHERE session_id = ?
                ORDER BY created_at DESC
                LIMIT 1";
        $params = [$sessionId];

        try {
            $result = $this->find($sql, $params);
            return $result ?: null;
        } catch (\Exception $e) {
            error_log("Error getting latest message: " . $e->getMessage());
            return null;
        }
    }
}