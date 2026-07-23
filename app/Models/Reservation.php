<?php
namespace App\Models;

use App\Core\Model;

/**
 * Reservation Model
 */
class Reservation extends Model {
    /**
     * Get all reservations with optional filters
     * @param array $filters Optional filters (user_id, book_id, status, etc.)
     * @return array Reservations
     */
    public function getAll($filters = []) {
        $sql = "SELECT r.*,
                       u.name as user_name, u.email as user_email,
                       b.title as book_title, b.author as book_author
                FROM reservations r
                JOIN users u ON r.user_id = u.id
                JOIN books b ON r.book_id = b.id";
        $params = [];
        $where = [];

        // User filter
        if (!empty($filters['user_id'])) {
            $where[] = "r.user_id = ?";
            $params[] = $filters['user_id'];
        }

        // Book filter
        if (!empty($filters['book_id'])) {
            $where[] = "r.book_id = ?";
            $params[] = $filters['book_id'];
        }

        // Status filter
        if (!empty($filters['status'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY r.reservation_date DESC";

        return $this->findAll($sql, $params);
    }

    /**
     * Get reservation by ID
     * @param int $id Reservation ID
     * @return array|false Reservation data or false if not found
     */
    public function getById($id) {
        $sql = "SELECT r.*,
                       u.name as user_name, u.email as user_email,
                       b.title as book_title, b.author as book_author
                FROM reservations r
                JOIN users u ON r.user_id = u.id
                JOIN books b ON r.book_id = b.id
                WHERE r.id = ?";
        return $this->find($sql, [$id]);
    }

    /**
     * Create a new reservation
     * @param array $data Reservation data (user_id, book_id)
     * @return int|false Reservation ID on success, false on failure
     */
    public function create($data) {
        // Validate required fields
        if (empty($data['user_id']) || empty($data['book_id'])) {
            return false;
        }

        // Check if user exists
        $userModel = new \App\Models\User();
        $user = $userModel->getById($data['user_id']);
        if (!$user) {
            return false;
        }

        // Check if book exists
        $bookModel = new \App\Models\Book();
        $book = $bookModel->getById($data['book_id']);
        if (!$book) {
            return false;
        }

        // Check if user already has a pending reservation for this book
        $existing = $this->find('SELECT id FROM reservations WHERE user_id = ? AND book_id = ? AND status = ?',
                              [$data['user_id'], $data['book_id'], 'pending']);
        if ($existing) {
            return false; // User already has a pending reservation for this book
        }

        // Check if book is available (if available, suggest issuing instead of reserving)
        $availableCopies = $bookModel->getAvailableCopies($data['book_id']);
        // We still allow reservations even if available, as user might want to reserve for future

        $sql = "INSERT INTO reservations (user_id, book_id, reservation_date, status)
                VALUES (?, ?, NOW(), 'pending')";
        $params = [$data['user_id'], $data['book_id']];

        $this->query($sql, $params);
        return $this->lastInsertId();
    }

    /**
     * Fulfill a reservation (mark as fulfilled and optionally issue book)
     * @param int $id Reservation ID
     * @param array $data Optional data (issue_book: true/false, user_id, book_id)
     * @return bool Success status
     */
    public function fulfill($id, $data = []) {
        // Get the reservation
        $reservation = $this->getById($id);
        if (!$reservation) {
            return false;
        }

        // Check if already fulfilled or cancelled
        if ($reservation['status'] !== 'pending') {
            return false; // Not pending
        }

        // Update reservation status
        $sql = "UPDATE reservations SET status = 'fulfilled', fulfilled_at = NOW() WHERE id = ?";
        $this->query($sql, [$id]);

        // Optionally issue the book to the user
        if (!empty($data['issue_book']) && $data['issue_book'] === true) {
            $transactionModel = new \App\Models\Transaction();
            $issueData = [
                'user_id' => $reservation['user_id'],
                'book_id' => $reservation['book_id']
            ];
            $transactionModel->issueBook($issueData);
        }

        return $this->rowCount() > 0;
    }

    /**
     * Cancel a reservation
     * @param int $id Reservation ID
     * @return bool Success status
     */
    public function cancel($id) {
        // Get the reservation
        $reservation = $this->getById($id);
        if (!$reservation) {
            return false;
        }

        // Check if already fulfilled or cancelled
        if ($reservation['status'] !== 'pending') {
            return false; // Not pending
        }

        // Update reservation status
        $sql = "UPDATE reservations SET status = 'cancelled' WHERE id = ?";
        $this->query($sql, [$id]);

        return $this->rowCount() > 0;
    }

    /**
     * Get pending reservations for a book (for waitlist)
     * @param int $book_id Book ID
     * @return array Pending reservations ordered by reservation date
     */
    public function getPendingForBook($book_id) {
        $sql = "SELECT r.*,
                       u.name as user_name, u.email as user_email
                FROM reservations r
                JOIN users u ON r.user_id = u.id
                WHERE r.book_id = ? AND r.status = 'pending'
                ORDER BY r.reservation_date";
        return $this->findAll($sql, [$book_id]);
    }

    /**
     * Get reservation statistics
     * @return array Statistics
     */
    public function getStats() {
        $stats = [];

        // Total reservations
        $row = $this->find('SELECT COUNT(*) as cnt FROM reservations');
        $stats['total_reservations'] = $row ? $row['cnt'] : 0;

        // Pending reservations
        $row = $this->find('SELECT COUNT(*) as cnt FROM reservations WHERE status = ?', ['pending']);
        $stats['pending_reservations'] = $row ? $row['cnt'] : 0;

        // Fulfilled reservations
        $row = $this->find('SELECT COUNT(*) as cnt FROM reservations WHERE status = ?', ['fulfilled']);
        $stats['fulfilled_reservations'] = $row ? $row['cnt'] : 0;

        // Cancelled reservations
        $row = $this->find('SELECT COUNT(*) as cnt FROM reservations WHERE status = ?', ['cancelled']);
        $stats['cancelled_reservations'] = $row ? $row['cnt'] : 0;

        return $stats;
    }
}
?>