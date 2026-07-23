<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Report as ReportModel;
use App\Models\Book;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Reservation;

/**
 * Report Controller (Admin only)
 */
class Report extends Controller {
    /**
     * Check if user is admin
     */
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/home/login');
        }
    }

    /**
     * Show reports dashboard
     */
    public function index() {
        $reportModel = new ReportModel();

        // Get some quick stats for the dashboard
        $bookModel = new Book();
        $userModel = new User();
        $transactionModel = new Transaction();
        $reservationModel = new Reservation();

        $stats = [
            'total_books' => ($bookModel->findAll('SELECT COUNT(*) FROM books'))[0]['COUNT(*)'],
            'total_users' => ($userModel->findAll('SELECT COUNT(*) FROM users'))[0]['COUNT(*)'],
            'active_loans' => ($transactionModel->findAll('SELECT COUNT(*) FROM transactions WHERE return_date IS NULL'))[0]['COUNT(*)'],
            'pending_reservations' => ($reservationModel->findAll('SELECT COUNT(*) FROM reservations WHERE status = \'pending\''))[0]['COUNT(*)'],
            'overdue_books' => ($transactionModel->findAll('SELECT COUNT(*) FROM transactions WHERE return_date IS NULL AND due_date < CURDATE()'))[0]['COUNT(*)']
        ];

        $this->view->assign('stats', $stats);
        $this->view->render('report/index', 'layouts/admin');
    }

    /**
     * Show book circulation report
     */
    public function bookCirculation() {
        $reportModel = new ReportModel();

        // Get date range from request or default to last 30 days
        $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $end_date = $_GET['end_date'] ?? date('Y-m-d');

        $data = $reportModel->bookCirculation($start_date, $end_date);

        $this->view->assign('data', $data);
        $this->view->assign('start_date', $start_date);
        $this->view->assign('end_date', $end_date);
        $this->view->assign('report_type', 'book_circulation');
        $this->view->render('report/view', 'layouts/admin');
    }

    /**
     * Show user activity report
     */
    public function userActivity() {
        $reportModel = new ReportModel();

        // Get date range from request or default to last 30 days
        $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $end_date = $_GET['end_date'] ?? date('Y-m-d');

        $data = $reportModel->userActivity($start_date, $end_date);

        $this->view->assign('data', $data);
        $this->view->assign('start_date', $start_date);
        $this->view->assign('end_date', $end_date);
        $this->view->assign('report_type', 'user_activity');
        $this->view->render('report/view', 'layouts/admin');
    }

    /**
     * Show fines report
     */
    public function fines() {
        $reportModel = new ReportModel();

        // Get date range from request or default to last 30 days
        $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $end_date = $_GET['end_date'] ?? date('Y-m-d');

        $data = $reportModel->finesReport($start_date, $end_date);

        $this->view->assign('data', [$data]); // Wrap in array for consistent handling
        $this->view->assign('start_date', $start_date);
        $this->view->assign('end_date', $end_date);
        $this->view->assign('report_type', 'fines');
        $this->view->render('report/view', 'layouts/admin');
    }

    /**
     * Show overdue books report
     */
    public function overdue() {
        $reportModel = new ReportModel();
        $data = $reportModel->overdueBooks();

        $this->view->assign('data', $data);
        $this->view->assign('report_type', 'overdue');
        $this->view->render('report/view', 'layouts/admin');
    }

    /**
     * Show reservation report
     */
    public function reservation() {
        $reportModel = new ReportModel();
        $data = $reportModel->reservationReport();

        $this->view->assign('data', $data);
        $this->view->assign('report_type', 'reservation');
        $this->view->render('report/view', 'layouts/admin');
    }

    /**
     * Export report to CSV
     */
    public function exportCSV() {
        $reportType = $_GET['type'] ?? 'book_circulation';
        $reportModel = new ReportModel();

        switch ($reportType) {
            case 'book_circulation':
                $data = $reportModel->bookCirculation(
                    $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
                    $_GET['end_date'] ?? date('Y-m-d')
                );
                $headers = ['Book ID', 'Title', 'Author', 'Circulation Count'];
                $filename = 'book_circulation_report.csv';
                break;

            case 'user_activity':
                $data = $reportModel->userActivity(
                    $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
                    $_GET['end_date'] ?? date('Y-m-d')
                );
                $headers = ['User ID', 'Name', 'Email', 'Activity Count'];
                $filename = 'user_activity_report.csv';
                break;

            case 'fines':
                $dataArray = [$reportModel->finesReport(
                    $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
                    $_GET['end_date'] ?? date('Y-m-d')
                )];
                $data = $dataArray;
                $headers = ['Fines Issued', 'Fines Collected', 'Fines Outstanding'];
                $filename = 'fines_report.csv';
                break;

            case 'overdue':
                $data = $reportModel->overdueBooks();
                $headers = [
                    'Transaction ID', 'Book Title', 'Author', 'User Name', 'User Email',
                    'Issue Date', 'Due Date', 'Days Overdue', 'Fine Amount'
                ];
                $filename = 'overdue_books_report.csv';
                break;

            case 'reservation':
                $data = $reportModel->reservationReport();
                $headers = [
                    'Reservation ID', 'Book Title', 'Author', 'User Name', 'User Email',
                    'Reservation Date', 'Status'
                ];
                $filename = 'reservation_report.csv';
                break;

            default:
                $_SESSION['message'] = 'Invalid report type';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/reports');
                return;
        }

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output CSV
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);

        foreach ($data as $row) {
            if (is_object($row)) {
                $row = (array)$row;
            }
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /**
     * Export report to JSON
     */
    public function exportJSON() {
        $reportType = $_GET['type'] ?? 'book_circulation';
        $reportModel = new ReportModel();

        switch ($reportType) {
            case 'book_circulation':
                $data = $reportModel->bookCirculation(
                    $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
                    $_GET['end_date'] ?? date('Y-m-d')
                );
                break;

            case 'user_activity':
                $data = $reportModel->userActivity(
                    $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
                    $_GET['end_date'] ?? date('Y-m-d')
                );
                break;

            case 'fines':
                $data = [$reportModel->finesReport(
                    $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
                    $_GET['end_date'] ?? date('Y-m-d')
                )];
                break;

            case 'overdue':
                $data = $reportModel->overdueBooks();
                break;

            case 'reservation':
                $data = $reportModel->reservationReport();
                break;

            default:
                $this->jsonResponse(['error' => 'Invalid report type'], 400);
                return;
        }

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>