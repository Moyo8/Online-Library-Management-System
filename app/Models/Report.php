<?php
namespace App\Models;

use App\Core\Model;

/**
 * Report Model
 */
class Report extends Model {
    /**
     * Generate book circulation report
     * @param string $start_date Start date (Y-m-d)
     * @param string $end_date End date (Y-m-d)
     * @return array Report data
     */
    public function bookCirculation($start_date = null, $end_date = null) {
        if (!$start_date) {
            $start_date = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$end_date) {
            $end_date = date('Y-m-d');
        }

        $sql = "SELECT b.id, b.title, b.author, COUNT(t.id) as circulation_count
                FROM books b
                LEFT JOIN transactions t ON b.id = t.book_id
                    AND t.issue_date >= ? AND t.issue_date <= ?
                GROUP BY b.id
                ORDER BY circulation_count DESC";
        return $this->findAll($sql, [$start_date, $end_date]);
    }

    /**
     * Generate user activity report
     * @param string $start_date Start date (Y-m-d)
     * @param string $end_date End date (Y-m-d)
     * @return array Report data
     */
    public function userActivity($start_date = null, $end_date = null) {
        if (!$start_date) {
            $start_date = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$end_date) {
            $end_date = date('Y-m-d');
        }

        $sql = "SELECT u.id, u.name, u.email, COUNT(t.id) as activity_count
                FROM users u
                LEFT JOIN transactions t ON u.id = t.user_id
                    AND t.issue_date >= ? AND t.issue_date <= ?
                GROUP BY u.id
                ORDER BY activity_count DESC";
        return $this->findAll($sql, [$start_date, $end_date]);
    }

    /**
     * Generate fines report
     * @param string $start_date Start date (Y-m-d)
     * @param string $end_date End date (Y-m-d)
     * @return array Report data
     */
    public function finesReport($start_date = null, $end_date = null) {
        if (!$start_date) {
            $start_date = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$end_date) {
            $end_date = date('Y-m-d');
        }

        $sql = "SELECT
                    SUM(CASE WHEN t.issue_date >= ? AND t.issue_date <= ? THEN t.fine ELSE 0 END) as fines_issued,
                    SUM(CASE WHEN t.issue_date >= ? AND t.issue_date <= ? THEN t.fine_paid ELSE 0 END) as fines_collected,
                    SUM(CASE WHEN t.issue_date >= ? AND t.issue_date <= ? THEN (t.fine - t.fine_paid) ELSE 0 END) as fines_outstanding
                FROM transactions t";
        $params = [$start_date, $end_date, $start_date, $end_date, $start_date, $end_date];
        return $this->find($sql, $params);
    }

    /**
     * Generate overdue books report
     * @return array Report data
     */
    public function overdueBooks() {
        $sql = "SELECT t.id, b.title, b.author, u.name as user_name, u.email as user_email,
                       t.issue_date, t.due_date,
                       DATEDIFF(CURDATE(), t.due_date) as days_overdue,
                       (DATEDIFF(CURDATE(), t.due_date) * 0.10) as fine_amount
                FROM transactions t
                JOIN books b ON t.book_id = b.id
                JOIN users u ON t.user_id = u.id
                WHERE t.return_date IS NULL AND t.due_date < CURDATE()
                ORDER BY days_overdue DESC";
        return $this->findAll($sql);
    }

    /**
     * Generate reservation report
     * @return array Report data
     */
    public function reservationReport() {
        $sql = "SELECT r.id, b.title as book_title, b.author as book_author,
                       u.name as user_name, u.email as user_email,
                       r.reservation_date, r.status
                FROM reservations r
                JOIN books b ON r.book_id = b.id
                JOIN users u ON r.user_id = u.id
                ORDER BY r.reservation_date DESC";
        return $this->findAll($sql);
    }

    /**
     * Export data to CSV
     * @param array $data Data to export
     * @param array $headers Column headers
     * @return string CSV content
     */
    public static function exportToCSV($data, $headers = []) {
        $output = fopen('php://output', 'w');
        if (!empty($headers)) {
            fputcsv($output, $headers);
        }

        if (!empty($data)) {
            // If data is array of objects or associative arrays, we can use the first row to get headers if not provided
            if (empty($headers)) {
                $headers = array_keys((array)$data[0]);
                fputcsv($output, $headers);
            }

            foreach ($data as $row) {
                if (is_object($row)) {
                    $row = (array)$row;
                }
                fputcsv($output, $row);
            }
        }

        fclose($output);
        // Note: In a real controller, we would output the CSV with proper headers.
        // This method is intended to be called in a controller that handles output.
        return ob_get_clean();
    }

    /**
     * Export data to JSON
     * @param array $data Data to export
     * @return string JSON content
     */
    public static function exportToJSON($data) {
        header('Content-Type: application/json');
        return json_encode($data);
    }
}
?>