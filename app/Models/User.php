<?php
namespace App\Models;

use App\Core\Model;

/**
 * User Model
 */
class User extends Model {
    /**
     * Get all users with optional filters
     * @param array $filters Optional filters (search, role, etc.)
     * @param int $ttl Time to live in seconds for caching (null to disable cache)
     * @return array Users
     */
    public function getAll($filters = [], $ttl = 300) { // Cache for 5 minutes by default
        $sql = "SELECT id, name, email, role, created_at FROM users";
        $params = [];
        $where = [];

        // Search filter
        if (!empty($filters['search'])) {
            $where[] = "(name LIKE ? OR email LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Role filter
        if (!empty($filters['role'])) {
            $where[] = "role = ?";
            $params[] = $filters['role'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY name";

        // Add filters to cache key
        return $this->findAll($sql, $params, $ttl);
    }

    /**
     * Get user by ID
     * @param int $id User ID
     * @param int $ttl Time to live in seconds for caching (null to disable cache)
     * @return array|false User data or false if not found
     */
    public function getById($id, $ttl = 600) { // Cache for 10 minutes by default
        $sql = "SELECT id, name, email, role, created_at FROM users WHERE id = ?";
        return $this->find($sql, [$id], $ttl);
    }

    /**
     * Get user by email
     * @param string $email User email
     * @param int $ttl Time to live in seconds for caching (null to disable cache)
     * @return array|false User data or false if not found
     */
    public function getByEmail($email, $ttl = 600) { // Cache for 10 minutes by default
        $sql = "SELECT id, name, email, role, password, created_at FROM users WHERE email = ?";
        return $this->find($sql, [$email], $ttl);
    }

    /**
     * Create a new user
     * @param array $data User data (name, email, password, role)
     * @return int|false User ID on success, false on failure
     */
    public function create($data) {
        // Validate required fields
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return false;
        }

        // Check if email already exists
        $existing = $this->getByEmail($data['email']);
        if ($existing) {
            return false; // Email already exists
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password, role)
                VALUES (?, ?, ?, ?)";
        $params = [
            $data['name'],
            $data['email'],
            $hashedPassword,
            $data['role'] ?? 'user'
        ];

        $this->query($sql, $params);
        $userId = $this->lastInsertId();

        // Clear relevant caches after creation
        $this->clearCache(); // Clear all user-related caches

        return $userId;
    }

    /**
     * Update an existing user
     * @param int $id User ID
     * @param array $data User data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        // Validate required fields
        if (empty($data['name']) || empty($data['email'])) {
            return false;
        }

        // Check if email already exists (excluding current user)
        if (!empty($data['email'])) {
            $existing = $this->find('SELECT id FROM users WHERE email = ? AND id != ?', [$data['email'], $id]);
            if ($existing) {
                return false; // Email already exists for another user
            }
        }

        // Prepare update data
        $updates = [];
        $params = [];

        if (!empty($data['name'])) {
            $updates[] = "name = ?";
            $params[] = $data['name'];
        }

        if (!empty($data['email'])) {
            $updates[] = "email = ?";
            $params[] = $data['email'];
        }

        if (!empty($data['role'])) {
            $updates[] = "role = ?";
            $params[] = $data['role'];
        }

        // If password is provided, hash and update it
        if (!empty($data['password'])) {
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $updates[] = "password = ?";
            $params[] = $hashedPassword;
        }

        if (empty($updates)) {
            return false; // Nothing to update
        }

        $params[] = $id; // For WHERE clause
        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";

        $this->query($sql, $params);
        $success = $this->rowCount() > 0;

        // Clear relevant caches after update
        if ($success) {
            $this->clearCache(); // Clear all user-related caches
        }

        return $success;
    }

    /**
     * Delete a user
     * @param int $id User ID
     * @return bool Success status
     */
    public function delete($id) {
        // Prevent deletion of self (handled in controller)
        // Check if user has any active transactions
        $row = $this->find('SELECT COUNT(*) as cnt FROM transactions WHERE user_id = ? AND return_date IS NULL', [$id]);
        $activeTransactions = $row ? (int)$row['cnt'] : 0;

        if ($activeTransactions > 0) {
            return false; // Cannot delete user with active loans
        }

        try {
            $this->db->beginTransaction();

            // Delete all transactions for this user (returned ones) to avoid foreign key constraint
            $this->query('DELETE FROM transactions WHERE user_id = ?', [$id]);

            // Delete the user
            $this->query('DELETE FROM users WHERE id = ?', [$id]);

            $this->db->commit();

            $success = true;

            // Clear relevant caches after deletion
            if ($success) {
                $this->clearCache(); // Clear all user-related caches
            }

            return $success;
        } catch (\Exception $e) {
            $this->db->rollBack();
            // Optionally log the error
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Get user statistics
     * @param int $ttl Time to live in seconds for caching (null to disable cache)
     * @return array User statistics
     */
    public function getStats($ttl = 300) { // Cache for 5 minutes by default
        $stats = [];

        // Total users
        $row = $this->find('SELECT COUNT(*) as cnt FROM users', [], $ttl);
        $stats['total_users'] = $row ? $row['cnt'] : 0;

        // Users by role
        $stats['users_by_role'] = $this->findAll('SELECT role, COUNT(*) as count FROM users GROUP BY role', [], $ttl);

        // Recent users (last 10)
        $stats['recent_users'] = $this->findAll('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 10', [], $ttl);

        return $stats;
    }
}
?>