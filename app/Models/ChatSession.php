<?php
namespace App\Models;

use App\Core\Model;

/**
 * Chat Session Model
 */
class ChatSession extends Model {
    /**
     * Create a new chat session
     * @param int $userId User ID
     * @param string $userRole User role (user/admin/librarian)
     * @param string $title Session title
     * @return int|false Session ID on success, false on failure
     */
    public function createSession($userId, $userRole, $title = 'New Chat') {
        // Validate inputs
        if (empty($userId) || !is_numeric($userId)) {
            return false;
        }

        if (!in_array($userRole, ['user', 'admin', 'librarian'])) {
            return false;
        }

        if (empty($title)) {
            $title = 'New Chat';
        }

        $sql = "INSERT INTO chat_sessions (user_id, user_role, title)
                VALUES (?, ?, ?)";
        $params = [$userId, $userRole, $title];

        try {
            $this->query($sql, $params);
            return $this->lastInsertId();
        } catch (\Exception $e) {
            error_log("Error creating chat session: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent chat sessions for a user
     * @param int $userId User ID
     * @param int $limit Number of sessions to return (default 10)
     * @return array List of chat sessions
     */
    public function getRecentSessions($userId, $limit = 10) {
        if (empty($userId) || !is_numeric($userId)) {
            return [];
        }

        $sql = "SELECT id, user_role, title, created_at, updated_at
                FROM chat_sessions
                WHERE user_id = ?
                ORDER BY updated_at DESC
                LIMIT ?";
        $params = [$userId, $limit];

        try {
            return $this->findAll($sql, $params);
        } catch (\Exception $e) {
            error_log("Error getting recent chat sessions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a chat session by ID
     * @param int $sessionId Session ID
     * @param int $userId User ID (for security check)
     * @return array|null Session data or null if not found/access denied
     */
    public function getSession($sessionId, $userId) {
        if (empty($sessionId) || !is_numeric($sessionId) || empty($userId) || !is_numeric($userId)) {
            return null;
        }

        $sql = "SELECT id, user_id, user_role, title, created_at, updated_at
                FROM chat_sessions
                WHERE id = ? AND user_id = ?";
        $params = [$sessionId, $userId];

        try {
            $result = $this->find($sql, $params);
            return $result ?: null;
        } catch (\Exception $e) {
            error_log("Error getting chat session: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update session title
     * @param int $sessionId Session ID
     * @param int $userId User ID (for security check)
     * @param string $title New title
     * @return bool Success status
     */
    public function updateTitle($sessionId, $userId, $title) {
        if (empty($sessionId) || !is_numeric($sessionId) || empty($userId) || !is_numeric($userId)) {
            return false;
        }

        if (empty($title)) {
            $title = 'Untitled Chat';
        }

        $sql = "UPDATE chat_sessions
                SET title = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND user_id = ?";
        $params = [$title, $sessionId, $userId];

        try {
            $this->query($sql, $params);
            return $this->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error updating chat session title: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a chat session and all its messages
     * @param int $sessionId Session ID
     * @param int $userId User ID (for security check)
     * @return bool Success status
     */
    public function deleteSession($sessionId, $userId) {
        if (empty($sessionId) || !is_numeric($sessionId) || empty($userId) || !is_numeric($userId)) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // Delete messages first (foreign key will handle this too, but being explicit)
            $this->query('DELETE FROM ai_chat_history WHERE session_id = ?', [$sessionId]);

            // Delete session
            $this->query('DELETE FROM chat_sessions WHERE id = ? AND user_id = ?', [$sessionId, $userId]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting chat session: " . $e->getMessage());
            return false;
        }
    }
}