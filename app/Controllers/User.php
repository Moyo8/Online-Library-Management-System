<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * User Controller
 */
class User extends Controller {
    /**
     * Check if user is logged in
     */
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/home/login');
        }
    }

    /**
     * Show user dashboard
     */
    public function dashboard() {
        $userId = $_SESSION['user_id'];

        // Get user's dashboard data using proper model methods
        $borrowedCount = $this->getBorrowedCount($userId);
        $reservationsCount = $this->getReservationsCount($userId);
        $overdueCount = $this->getOverdueCount($userId);
        $finesOwed = $this->getFinesOwed($userId);
        $recentTransactions = $this->getRecentTransactions($userId);
        $weeklyActivity = $this->getWeeklyActivity($userId);
        $monthlyBookCount = $this->getMonthlyBookCount($userId);
        $onTimeRate = $this->getOnTimeReturnRate($userId);

        // Calculate reservation pending rate for the summary card
        $reservationModel = $this->loadModel('Reservation');
        $reservationStats = $reservationModel->findAll('
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) as pending
            FROM reservations
            WHERE user_id = ?
        ', [$userId]);

        $reservationPendingRate = 0;
        if ($reservationStats && !empty($reservationStats[0]['total']) && $reservationStats[0]['total'] > 0) {
            $reservationPendingRate = round(($reservationStats[0]['pending'] ?? 0) * 100 / ($reservationStats[0]['total'] ?? 1));
        }

        $this->view->assign('borrowed_books_count', $borrowedCount);
        $this->view->assign('reservations_count', $reservationsCount);
        $this->view->assign('overdue_books_count', $overdueCount);
        $this->view->assign('total_fines_owed', $finesOwed);
        $this->view->assign('recent_transactions_data', $recentTransactions);
        $this->view->assign('weekly_activity_data', $weeklyActivity);
        $this->view->assign('monthly_book_count', $monthlyBookCount);
        $this->view->assign('on_time_rate', $onTimeRate);
        $this->view->assign('reservation_pending_rate', $reservationPendingRate);

        $this->view->render('user/dashboard', 'layouts/user');
    }

    /**
     * Get count of currently borrowed books
     * @param int $userId User ID
     * @return int Borrowed count
     */
    private function getBorrowedCount($userId) {
        $transactionModel = $this->loadModel('Transaction');
        $result = $transactionModel->findAll('SELECT COUNT(*) as cnt FROM transactions WHERE user_id = ? AND return_date IS NULL', [$userId]);
        return $result ? (int)$result[0]['cnt'] : 0;
    }

    /**
     * Get weekly activity data for chart (borrowed/returned books per day)
     * @param int $userId User ID
     * @return array Weekly activity data
     */
    private function getWeeklyActivity($userId) {
        // Initialize arrays with zeros for each day of the week (Mon-Sun)
        $borrowedData = array_fill(0, 7, 0);
        $returnedData = array_fill(0, 7, 0);

        try {
            // Get borrowed books per day (last 7 days)
            $transactionModel = $this->loadModel('Transaction');
            $borrowedWeekly = $transactionModel->findAll('
                SELECT
                    DATE(issue_date) as day,
                    COUNT(*) as borrowed_count
                FROM transactions
                WHERE user_id = ? AND issue_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(issue_date)
                ORDER BY DAY
            ', [$userId]);

            // Fill in borrowed data
            foreach ($borrowedWeekly as $dayData) {
                $dayOfWeek = (int)date('w', strtotime($dayData['day'])); // 0=Sunday, 6=Saturday
                // Adjust to match our chart labels (Mon=0, Sun=6)
                $chartIndex = ($dayOfWeek + 6) % 7; // Shift so Monday is index 0
                if (isset($borrowedData[$chartIndex])) {
                    $borrowedData[$chartIndex] = (int)$dayData['borrowed_count'];
                }
            }

            // Get returned books per day (last 7 days)
            $returnedWeekly = $transactionModel->findAll('
                SELECT
                    DATE(return_date) as day,
                    COUNT(*) as returned_count
                FROM transactions
                WHERE user_id = ? AND return_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND return_date IS NOT NULL
                GROUP BY DATE(return_date)
                ORDER BY DAY
            ', [$userId]);

            // Fill in returned data
            foreach ($returnedWeekly as $dayData) {
                $dayOfWeek = (int)date('w', strtotime($dayData['day'])); // 0=Sunday, 6=Saturday
                $chartIndex = ($dayOfWeek + 6) % 7; // Shift so Monday is index 0
                if (isset($returnedData[$chartIndex])) {
                    $returnedData[$chartIndex] = (int)$dayData['returned_count'];
                }
            }
        } catch (\Exception $e) {
            error_log("Error in getWeeklyActivity: " . $e->getMessage());
        }

        return [
            'borrowed' => $borrowedData,
            'returned' => $returnedData
        ];
    }

    /**
     * Get count of books borrowed by user in current month
     * @param int $userId User ID
     * @return int Monthly book count
     */
    private function getMonthlyBookCount($userId) {
        try {
            $transactionModel = $this->loadModel('Transaction');
            $result = $transactionModel->findAll('
                SELECT COUNT(*) as count
                FROM transactions
                WHERE user_id = ?
                  AND issue_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                  AND return_date IS NOT NULL
            ', [$userId]);

            return $result ? (int)($result[0]['count'] ?? 0) : 0;
        } catch (\Exception $e) {
            error_log("Error in getMonthlyBookCount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate on-time return rate for user
     * @param int $userId User ID
     * @return float On-time return percentage (0-100)
     */
    private function getOnTimeReturnRate($userId) {
        try {
            $transactionModel = $this->loadModel('Transaction');
            $result = $transactionModel->findAll('
                SELECT
                    COUNT(*) as total_returns,
                    SUM(CASE WHEN return_date <= due_date THEN 1 ELSE 0 END) as on_time_returns
                FROM transactions
                WHERE user_id = ? AND return_date IS NOT NULL
            ', [$userId]);

            if ($result && isset($result[0]['total_returns']) && $result[0]['total_returns'] > 0) {
                $total = (int)$result[0]['total_returns'];
                $onTime = (int)$result[0]['on_time_returns'];
                return round(($onTime / $total) * 100, 1);
            }
            return 100.0; // If no returns, assume 100% on-time
        } catch (\Exception $e) {
            error_log("Error in getOnTimeReturnRate: " . $e->getMessage());
            return 100.0;
        }
    }

    /**
     * Get reservations count
     */
    private function getReservationsCount($userId) {
        $reservationModel = $this->loadModel('Reservation');
        $result = $reservationModel->findAll('SELECT COUNT(*) FROM reservations WHERE user_id = ? AND status = ?', [$userId, 'pending']);
        return $result ? (int)$result[0]['COUNT(*)'] : 0;
    }

    /**
     * Get overdue books count
     */
    private function getOverdueCount($userId) {
        $transactionModel = $this->loadModel('Transaction');
        $result = $transactionModel->findAll('SELECT COUNT(*) FROM transactions WHERE user_id = ? AND return_date IS NULL AND due_date < CURDATE()', [$userId]);
        return $result ? (int)$result[0]['COUNT(*)'] : 0;
    }

    /**
     * Get total fines owed
     */
    private function getFinesOwed($userId) {
        $transactionModel = $this->loadModel('Transaction');
        $result = $transactionModel->findAll('SELECT COALESCE(SUM(fine - fine_paid), 0) FROM transactions WHERE user_id = ? AND return_date IS NOT NULL', [$userId]);
        return $result ? (float)$result[0]['COALESCE(SUM(fine - fine_paid), 0)'] : 0.0;
    }

    /**
     * Get recent transactions (last 5)
     */
    private function getRecentTransactions($userId) {
        $transactionModel = $this->loadModel('Transaction');
        $sql = "SELECT t.*, b.title as book_title, b.author as book_author
                FROM transactions t
                JOIN books b ON t.book_id = b.id
                WHERE t.user_id = ?
                ORDER BY t.issue_date DESC
                LIMIT 5";
        return $transactionModel->findAll($sql, [$userId]);
    }

    /**
     * Borrow a book
     */
    public function borrow() {
        $book_id = (int)($_GET['book_id'] ?? 0);
        if ($book_id <= 0) {
            $_SESSION['message'] = 'Invalid book selection.';
            $_SESSION['message_type'] = 'danger';
            $this->redirect('/search');
            return;
        }

        $userId = $_SESSION['user_id'];

        // Load models
        $bookModel = $this->loadModel('Book');
        $book = $bookModel->getById($book_id);
        if (!$book) {
            $_SESSION['message'] = 'Book not found.';
            $_SESSION['message_type'] = 'danger';
            $this->redirect('/search');
            return;
        }

        // Check availability
        $available = $bookModel->getAvailableCopies($book_id);
        if ($available <= 0) {
            $_SESSION['message'] = 'Sorry, this book is currently not available for borrowing.';
            $_SESSION['message_type'] = 'warning';
            $this->redirect('/search');
            return;
        }

        // Issue the book via Transaction model
        $transactionModel = $this->loadModel('Transaction');
        $data = [
            'user_id' => $userId,
            'book_id' => $book_id,
            'issue_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+14 days')) // 2 weeks loan
        ];

        $result = $transactionModel->issueBook($data);
        if ($result) {
            $_SESSION['message'] = 'Book borrowed successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to borrow book. Please try again.';
            $_SESSION['message_type'] = 'danger';
        }

        $this->redirect('/my/books');
    }

    /**
     * Show user's fines details
     */
    public function fines() {
        $userId = $_SESSION['user_id'];

        // Get user's fines with details
        $transactionModel = $this->loadModel('Transaction');
        $fines = $transactionModel->findAll(
            'SELECT t.id, b.title as book_title, b.author as book_author,
                     t.issue_date, t.due_date, t.return_date,
                     t.fine, COALESCE(t.fine_paid, 0) as fine_paid,
                     (t.fine - COALESCE(t.fine_paid, 0)) as balance_due
             FROM transactions t
             JOIN books b ON t.book_id = b.id
             WHERE t.user_id = ? AND t.fine > 0 AND t.return_date IS NOT NULL
             ORDER BY t.return_date DESC',
            [$userId]
        );

        $this->view->assign('fines', $fines);
        $this->view->render('user/fines/index', 'layouts/user');
    }
}
?>