<?php
namespace App\Models;

use App\Core\Model;
use DateTime;

/**
 * Transaction Model
 */
class Transaction extends Model {
    /**
     * Get all transactions with optional filters
     * @param array $filters Optional filters (user_id, book_id, status, etc.)
     * @return array Transactions
     */
    public function getAll($filters = []) {
        $sql = "SELECT t.*,
                       u.name as user_name, u.email as user_email,
                       b.title as book_title, b.author as book_author
                FROM transactions t
                JOIN users u ON t.user_id = u.id
                JOIN books b ON t.book_id = b.id";
        $params = [];
        $where = [];

        // User filter
        if (!empty($filters['user_id'])) {
            $where[] = "t.user_id = ?";
            $params[] = $filters['user_id'];
        }

        // Book filter
        if (!empty($filters['book_id'])) {
            $where[] = "t.book_id = ?";
            $params[] = $filters['book_id'];
        }

        // Status filter (based on return_date)
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $where[] = "t.return_date IS NULL";
            } elseif ($filters['status'] === 'returned') {
                $where[] = "t.return_date IS NOT NULL";
            } elseif ($filters['status'] === 'overdue') {
                $where[] = "t.return_date IS NULL AND t.due_date < CURDATE()";
            }
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY t.issue_date DESC";

        return $this->findAll($sql, $params);
    }

    /**
     * Get transaction by ID
     * @param int $id Transaction ID
     * @return array|false Transaction data or false if not found
     */
    public function getById($id) {
        $sql = "SELECT t.*,
                       u.name as user_name, u.email as user_email,
                       b.title as book_title, b.author as book_author
                FROM transactions t
                JOIN users u ON t.user_id = u.id
                JOIN books b ON t.book_id = b.id
                WHERE t.id = ?";
        return $this->find($sql, [$id]);
    }

    /**
     * Create a new transaction (issue a book)
     * @param array $data Transaction data (user_id, book_id, issue_date, due_date)
     * @return int|false Transaction ID on success, false on failure
     */
    public function issueBook($data) {
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

        // Check if book exists and is available
        $bookModel = new \App\Models\Book();
        $book = $bookModel->getById($data['book_id']);
        if (!$book) {
            return false;
        }

        // Check availability
        $availableCopies = $bookModel->getAvailableCopies($data['book_id']);
        if ($availableCopies <= 0) {
            return false; // No copies available
        }

        // Set issue date and due date
        $issueDate = !empty($data['issue_date']) ? $data['issue_date'] : date('Y-m-d');
        $dueDate = !empty($data['due_date']) ? $data['due_date'] : date('Y-m-d', strtotime('+14 days')); // Default 2 weeks

        // Fine and fine_paid default to 0
        $fine = 0.00;
        $fine_paid = 0.00;

        $sql = "INSERT INTO transactions (user_id, book_id, issue_date, due_date, fine, fine_paid)
                VALUES (?, ?, ?, ?, ?, ?)";
        $params = [
            $data['user_id'],
            $data['book_id'],
            $issueDate,
            $dueDate,
            $fine,
            $fine_paid
        ];

        $this->query($sql, $params);
        return $this->lastInsertId();
    }

    /**
     * Return a book (update transaction)
     * @param int $id Transaction ID
     * @param array $data Optional data (return_date, fine_paid)
     * @return bool Success status
     */
    public function returnBook($id, $data = []) {
        // Get the transaction
        $transaction = $this->getById($id);
        if (!$transaction) {
            return false;
        }

        // Check if already returned
        if (!empty($transaction['return_date'])) {
            return false; // Already returned
        }

        // Set return date
        $returnDate = !empty($data['return_date']) ? $data['return_date'] : date('Y-m-d');

        // Calculate fine if overdue and not already paid
        $fine = 0.00;
        $fine_paid = !empty($data['fine_paid']) ? (float)$data['fine_paid'] : 0.00;

        if (!empty($data['calculate_fine']) && $data['calculate_fine'] === true) {
            $dueDate = new DateTime($transaction['due_date']);
            $returnDateObj = new DateTime($returnDate);
            $overdueDays = $dueDate->diff($returnDateObj)->days;

            if ($overdueDays > 0) {
                // Fine of $0.10 per day overdue
                $fine = $overdueDays * 0.10;
                // If fine_paid is not set, set it to the calculated fine (assuming payment at return)
                if ($fine_paid == 0.00) {
                    $fine_paid = $fine;
                }
            }
        }

        $sql = "UPDATE transactions SET return_date = ?, fine = ?, fine_paid = ? WHERE id = ?";
        $params = [$returnDate, $fine, $fine_paid, $id];

        $this->query($sql, $params);
        return $this->rowCount() > 0;
    }

    /**
     * Get overdue transactions
     * @return array Overdue transactions
     */
    public function getOverdue() {
        $sql = "SELECT t.*,
                       u.name as user_name, u.email as user_email,
                       b.title as book_title, b.author as book_author
                FROM transactions t
                JOIN users u ON t.user_id = u.id
                JOIN books b ON t.book_id = b.id
                WHERE t.return_date IS NULL AND t.due_date < CURDATE()
                ORDER BY t.due_date";
        return $this->findAll($sql);
    }

    /**
     * Get transactions due today
     * @return array Transactions due today
     */
    public function getDueToday() {
        $sql = "SELECT t.*,
                       u.name as user_name, u.email as user_email,
                       b.title as book_title, b.author as book_author
                FROM transactions t
                JOIN users u ON t.user_id = u.id
                JOIN books b ON t.book_id = b.id
                WHERE t.return_date IS NULL AND t.due_date = CURDATE()
                ORDER BY t.due_date";
        return $this->findAll($sql);
    }

    /**
     * Get transaction statistics
     * @return array Statistics
     */
    public function getStats() {
        $stats = [];

        // Total transactions
        $row = $this->find('SELECT COUNT(*) as cnt FROM transactions');
        $stats['total_transactions'] = $row ? $row['cnt'] : 0;

        // Active loans
        $row = $this->find('SELECT COUNT(*) as cnt FROM transactions WHERE return_date IS NULL');
        $stats['active_loans'] = $row ? $row['cnt'] : 0;

        // Overdue books
        $row = $this->find('SELECT COUNT(*) as cnt FROM transactions WHERE return_date IS NULL AND due_date < CURDATE()');
        $stats['overdue_books'] = $row ? $row['cnt'] : 0;

        // Today's issues
        $row = $this->find('SELECT COUNT(*) as cnt FROM transactions WHERE issue_date = CURDATE()');
        $stats['todays_issues'] = $row ? $row['cnt'] : 0;

        // Today's returns
        $row = $this->find('SELECT COUNT(*) as cnt FROM transactions WHERE return_date = CURDATE()');
        $stats['todays_returns'] = $row ? $row['cnt'] : 0;

        // Total fines (outstanding)
        $row = $this->find('SELECT COALESCE(SUM(fine - fine_paid), 0) as total FROM transactions');
        $stats['total_fines_outstanding'] = $row ? $row['total'] : 0;

        // Total fines collected
        $row = $this->find('SELECT COALESCE(SUM(fine_paid), 0) as total FROM transactions');
        $stats['total_fines_collected'] = $row ? $row['total'] : 0;

        return $stats;
    }
}
?>