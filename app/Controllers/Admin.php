<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Admin Controller
 */
class Admin extends Controller {
    /**
     * Check if user is admin or librarian
     */
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'librarian'])) {
            $this->redirect('/home/login');
        }
    }

    /**
     * Show admin dashboard
     */
    public function dashboard() {
        // Get library statistics
        $stats = $this->getLibraryStats();

        // Calculate reservation rate: pending reservations / total books * 100
        $reservationModel = $this->loadModel('Reservation');
        $pendingReservations = $reservationModel->findAll('SELECT COUNT(*) as count FROM reservations WHERE status = \'pending\'');
        $pendingCount = $pendingReservations ? (int)($pendingReservations[0]['count'] ?? 0) : 0;
        $reservationRate = ($stats['total_books'] ?? 0) > 0 ? round(($pendingCount / $stats['total_books']) * 100, 1) : 0;

        // Calculate satisfaction rate based on on-time returns
        $transactionModel = $this->loadModel('Transaction');
        $onTimeStats = $transactionModel->findAll('
            SELECT
                CASE
                    WHEN total_returns = 0 THEN 100
                    ELSE ROUND((on_time_returns * 100.0) / total_returns, 1)
                END as satisfaction_rate
            FROM (
                SELECT
                    COUNT(*) as total_returns,
                    SUM(CASE WHEN return_date <= due_date THEN 1 ELSE 0 END) as on_time_returns
                FROM transactions
                WHERE return_date IS NOT NULL
            ) as stats
        ');
        $satisfactionRate = $onTimeStats ? (float)($onTimeStats[0]['satisfaction_rate'] ?? 100) : 100;

        $this->view->assign('stats', $stats);
        $this->view->assign('reservation_rate', $reservationRate);
        $this->view->assign('satisfaction_rate', $satisfactionRate);
        $this->view->render('admin/dashboard', 'layouts/admin');
    }


    /**
     * Get library statistics with real data
     */
    private function getLibraryStats() {
        try {
            $stats = [];

            // Total books
            $bookModel = $this->loadModel('Book');
            $result = $bookModel->findAll('SELECT COUNT(*) FROM books');
            $stats['total_books'] = $result[0]['COUNT(*)'];

            // Available books
            $bookModel = $this->loadModel('Book');
            $result = $bookModel->findAll('
                SELECT COUNT(*) FROM books b
                WHERE b.quantity > (SELECT COUNT(*) FROM transactions t WHERE t.book_id = b.id AND t.return_date IS NULL)
            ');
            $stats['available_books'] = $result[0]['COUNT(*)'];

            // Total users
            $userModel = $this->loadModel('User');
            $result = $userModel->findAll('SELECT COUNT(*) FROM users');
            $stats['total_users'] = $result[0]['COUNT(*)'];

            // Active loans
            $transactionModel = $this->loadModel('Transaction');
            $result = $transactionModel->findAll('SELECT COUNT(*) FROM transactions WHERE return_date IS NULL');
            $stats['active_loans'] = $result[0]['COUNT(*)'];

            // Overdue books
            $transactionModel = $this->loadModel('Transaction');
            $result = $transactionModel->findAll('
                SELECT COUNT(*) FROM transactions
                WHERE return_date IS NULL AND due_date < CURDATE()
            ');
            $stats['overdue_books'] = $result[0]['COUNT(*)'];

            // Today's issues
            $transactionModel = $this->loadModel('Transaction');
            $result = $transactionModel->findAll('SELECT COUNT(*) FROM transactions WHERE issue_date = CURDATE()');
            $stats['todays_issues'] = $result[0]['COUNT(*)'];

            // Today's returns
            $transactionModel = $this->loadModel('Transaction');
            $result = $transactionModel->findAll('SELECT COUNT(*) FROM transactions WHERE return_date = CURDATE()');
            $stats['todays_returns'] = $result[0]['COUNT(*)'];

            // Total fines collected
            $transactionModel = $this->loadModel('Transaction');
            $result = $transactionModel->findAll('SELECT COALESCE(SUM(fine_paid), 0) FROM transactions');
            $stats['total_fines'] = $result[0]['COALESCE(SUM(fine_paid), 0)'];

            // Average daily issues (last 7 days)
            $transactionModel = $this->loadModel('Transaction');
            $result = $transactionModel->findAll('
                SELECT COALESCE(AVG(daily_count), 0) as avg_daily_issues
                FROM (
                    SELECT DATE(issue_date) as issue_date, COUNT(*) as daily_count
                    FROM transactions
                    WHERE issue_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                    GROUP BY DATE(issue_date)
                ) as daily_stats
            ');
            $stats['avg_daily_issues'] = $result[0]['avg_daily_issues'];

            // Average daily returns (last 7 days)
            $transactionModel = $this->loadModel('Transaction');
            $result = $transactionModel->findAll('
                SELECT COALESCE(AVG(daily_count), 0) as avg_daily_returns
                FROM (
                    SELECT DATE(return_date) as return_date, COUNT(*) as daily_count
                    FROM transactions
                    WHERE return_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND return_date IS NOT NULL
                    GROUP BY DATE(return_date)
                ) as daily_stats
            ');
            $stats['avg_daily_returns'] = $result[0]['avg_daily_returns'];

            // Occupancy rate (percentage of books currently checked out)
            $bookModel = $this->loadModel('Book');
            $result = $bookModel->findAll('
                SELECT
                    CASE
                        WHEN SUM(quantity) = 0 THEN 0
                        ELSE ROUND((SUM(quantity) - SUM(available)) * 100.0 / SUM(quantity), 2)
                    END as occupancy_rate
                FROM (
                    SELECT b.quantity,
                           (b.quantity - IFNULL(t.issued_count, 0)) as available
                    FROM books b
                    LEFT JOIN (
                        SELECT book_id, COUNT(*) as issued_count
                        FROM transactions
                        WHERE return_date IS NULL
                        GROUP BY book_id
                    ) t ON b.id = t.book_id
                ) as book_stats
            ');
            $stats['occupancy_rate'] = $result[0]['occupancy_rate'];

            // Member growth rate (new members this month vs last month)
            $userModel = $this->loadModel('User');
            $result = $userModel->findAll('
                SELECT
                    CASE
                        WHEN last_month_count = 0 THEN 0
                        ELSE ROUND(((this_month_count - last_month_count) * 100.0) / last_month_count, 2)
                    END as member_growth_rate
                FROM (
                    SELECT
                        (SELECT COUNT(*) FROM users WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) as this_month_count,
                        (SELECT COUNT(*) FROM users WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
                         AND DATE(created_at) < DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) as last_month_count
                ) as stats
            ');
            $stats['member_growth_rate'] = $result[0]['member_growth_rate'];

            // Weekly activity data for chart (last 7 days)
            $transactionModel = $this->loadModel('Transaction');
            $weeklyData = $transactionModel->findAll('
                SELECT
                    DATE(issue_date) as day,
                    COUNT(*) as issues_count
                FROM transactions
                WHERE issue_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(issue_date)
                ORDER BY DAY
            ');

            // Initialize arrays with zeros for each day of the week
            $issuesData = array_fill(0, 7, 0);
            $returnsData = array_fill(0, 7, 0);

            // Fill in actual data
            foreach ($weeklyData as $dayData) {
                $dayOfWeek = (int)date('w', strtotime($dayData['day'])); // 0=Sunday, 6=Saturday
                // Adjust to match our chart labels (Mon=0, Sun=6)
                $chartIndex = ($dayOfWeek + 6) % 7; // Shift so Monday is index 0
                if (isset($issuesData[$chartIndex])) {
                    $issuesData[$chartIndex] = (int)$dayData['issues_count'];
                }
            }

            // Get returns data for the same period
            $weeklyReturnsData = $transactionModel->findAll('
                SELECT
                    DATE(return_date) as day,
                    COUNT(*) as returns_count
                FROM transactions
                WHERE return_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND return_date IS NOT NULL
                GROUP BY DATE(return_date)
                ORDER BY DAY
            ');

            foreach ($weeklyReturnsData as $dayData) {
                $dayOfWeek = (int)date('w', strtotime($dayData['day'])); // 0=Sunday, 6=Saturday
                $chartIndex = ($dayOfWeek + 6) % 7; // Shift so Monday is index 0
                if (isset($returnsData[$chartIndex])) {
                    $returnsData[$chartIndex] = (int)$dayData['returns_count'];
                }
            }

            $stats['weekly_issues_data'] = $issuesData;
            $stats['weekly_returns_data'] = $returnsData;

        } catch (\Exception $e) {
            $stats['error'] = $e->getMessage();
        }

        return $stats;
    }
}
?>