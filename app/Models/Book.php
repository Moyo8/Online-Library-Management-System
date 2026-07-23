<?php
namespace App\Models;

use App\Core\Model;

/**
 * Book Model
 */
class Book extends Model {
    /**
     * Get all books with optional filters
     * @param array $filters Optional filters (search, status, category, etc.)
     * @param int $ttl Time to live in seconds for caching (null to disable cache)
     * @return array Books
     */
    public function getAll($filters = [], $ttl = 300) { // Cache for 5 minutes by default
        $sql = "SELECT b.*,
                        (SELECT COUNT(*) FROM transactions t
                         WHERE t.book_id = b.id AND t.return_date IS NULL) as issued_count
                FROM books b";

        $params = [];
        $where = [];

        // Search filter
        if (!empty($filters['search'])) {
            $where[] = "(b.title LIKE ? OR b.author LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Availability filter
        if (!empty($filters['available']) && $filters['available'] === true) {
            $where[] = "b.quantity > (SELECT COUNT(*) FROM transactions t
                         WHERE t.book_id = b.id AND t.return_date IS NULL)";
        }

        // Category filter
        if (!empty($filters['category'])) {
            $where[] = "b.category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY b.title";

        // Add filters to cache key
        $cacheParams = $filters;
        return $this->findAll($sql, $params, $ttl);
    }

    /**
     * Get book by ID
     * @param int $id Book ID
     * @param int $ttl Time to live in seconds for caching (null to disable cache)
     * @return array|false Book data or false if not found
     */
    public function getById($id, $ttl = 600) { // Cache for 10 minutes by default
        $sql = "SELECT b.*,
                        (SELECT COUNT(*) FROM transactions t
                         WHERE t.book_id = b.id AND t.return_date IS NULL) as issued_count
                FROM books b WHERE b.id = ?";
        return $this->find($sql, [$id], $ttl);
    }

    /**
     * Create a new book
     * @param array $data Book data (title, author, isbn, quantity, category, publisher, published_year)
     * @return int|false Book ID on success, false on failure
     */
    public function create($data) {
        // Validate required fields
        if (empty($data['title']) || empty($data['author'])) {
            return false;
        }

        // Check ISBN uniqueness if provided
        if (!empty($data['isbn'])) {
            $existing = $this->find('SELECT id FROM books WHERE isbn = ?', [$data['isbn']]);
            if ($existing) {
                return false; // ISBN already exists
            }
        }

        $sql = "INSERT INTO books (title, author, isbn, quantity, category, publisher, published_year)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $data['title'],
            $data['author'],
            $data['isbn'] ?? null,
            (int)($data['quantity'] ?? 1),
            $data['category'] ?? null,
            $data['publisher'] ?? null,
            !empty($data['published_year']) ? (int)$data['published_year'] : null
        ];

        $this->query($sql, $params);
        $bookId = $this->lastInsertId();

        // Clear relevant caches after creation
        $this->clearCache(); // Clear all book-related caches

        return $bookId;
    }

    /**
     * Update a book
     * @param int $bookId Book ID
     * @param array $data Book data (title, author, isbn, quantity, category, publisher, published_year)
     * @return bool Success status
     */
    public function update($bookId, $data) {
        // Validate required fields
        if (empty($data['title']) || empty($data['author'])) {
            return false;
        }

        // Check ISBN uniqueness if provided (excluding current book)
        if (!empty($data['isbn'])) {
            $existing = $this->find('SELECT id FROM books WHERE isbn = ? AND id != ?', [$data['isbn'], $bookId]);
            if ($existing) {
                return false; // ISBN already exists for another book
            }
        }

        $sql = "UPDATE books SET
                title = ?,
                author = ?,
                isbn = ?,
                quantity = ?,
                category = ?,
                publisher = ?,
                published_year = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        $params = [
            $data['title'],
            $data['author'],
            $data['isbn'] ?? null,
            (int)($data['quantity'] ?? 1),
            $data['category'] ?? null,
            $data['publisher'] ?? null,
            !empty($data['published_year']) ? (int)$data['published_year'] : null,
            $bookId
        ];

        $this->query($sql, $params);
        $affected = $this->rowCount() > 0;

        // Clear relevant caches after update
        if ($affected) {
            $this->clearCache(); // Clear all book-related caches
        }

        return $affected;
    }

    /**
     * Delete a book
     * @param int $id Book ID
     * @return bool Success status
     */
    public function delete($id) {
        // Check if book has any active transactions
        $row = $this->find('SELECT COUNT(*) as cnt FROM transactions WHERE book_id = ? AND return_date IS NULL', [$id]);
        $activeLoans = $row ? (int)$row['cnt'] : 0;

        if ($activeLoans > 0) {
            return false; // Cannot delete book with active loans
        }

        $this->query('DELETE FROM books WHERE id = ?', [$id]);
        $success = $this->rowCount() > 0;

        // Clear relevant caches after deletion
        if ($success) {
            $this->clearCache(); // Clear all book-related caches
        }

        return $success;
    }

    /**
     * Get available copies count for a book
     * @param int $id Book ID
     * @return int Available copies
     */
    public function getAvailableCopies($id) {
        $book = $this->getById($id);
        if (!$book) {
            return 0;
        }

        $issuedCount = $book['issued_count'] ?? 0;
        return max(0, $book['quantity'] - $issuedCount);
    }
}