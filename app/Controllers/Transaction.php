<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Transaction Controller (Staff only)
 */
class Transaction extends Controller {
    /**
     * Check if user is staff (admin or librarian)
     */
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'librarian'])) {
            $this->redirect('/home/login');
        }
    }

    /**
     * Show list of transactions
     */
    public function index() {
        $transactionModel = $this->loadModel('Transaction');

        // Get filter parameters
        $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : '';
        $book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : '';
        $status = $_GET['status'] ?? ''; // active, returned, overdue
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 10; // Items per page

        // Build WHERE clause and parameters
        $where = [];
        $params = [];

        if (!empty($user_id)) {
            $where[] = "t.user_id = ?";
            $params[] = $user_id;
        }
        if (!empty($book_id)) {
            $where[] = "t.book_id = ?";
            $params[] = $book_id;
        }
        if (!empty($status)) {
            switch ($status) {
                case 'active':
                    $where[] = "t.return_date IS NULL";
                    break;
                case 'returned':
                    $where[] = "t.return_date IS NOT NULL";
                    break;
                case 'overdue':
                    $where[] = "t.return_date IS NULL AND t.due_date < CURDATE()";
                    break;
                default:
                    // Ignore invalid status
                    break;
            }
        }

        // Base SQL
        $sql = "SELECT t.*, b.title as book_title, b.author as book_author,
                       u.name as user_name, u.email as user_email
                FROM transactions t
                JOIN books b ON t.book_id = b.id
                JOIN users u ON t.user_id = u.id";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY t.issue_date DESC";

        // Get paginated results
        $paginated = $transactionModel->paginate($sql, $params, $page, $perPage);
        $transactions = $paginated['data'];

        // Get stats (not paginated)
        $stats = $transactionModel->getStats();

        // Get users and books for filter dropdowns
        $userModel = $this->loadModel('User');
        $users = $userModel->getAll();

        $bookModel = $this->loadModel('Book');
        $books = $bookModel->getAll();

        $this->view->assign('transactions', $transactions);
        $this->view->assign('stats', $stats);
        $this->view->assign('users', $users);
        $this->view->assign('books', $books);
        $this->view->assign('filters', [
            'user_id' => $user_id,
            'book_id' => $book_id,
            'status' => $status
        ]);
        $this->view->assign('pagination', $paginated);

        $this->view->render('transaction/index', 'layouts/admin');
    }

    /**
     * Show form to issue a book
     */
    public function issue() {
        // Get available users and books for dropdowns
        $userModel = $this->loadModel('User');
        $users = $userModel->getAll();

        $bookModel = $this->loadModel('Book');
        $books = $bookModel->getAll();

        $this->view->assign('users', $users);
        $this->view->assign('books', $books);
        $this->view->render('transaction/issue', 'layouts/admin');
    }

    /**
     * Handle book issuance
     */
    public function issueBook() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => (int)($_POST['user_id'] ?? 0),
                'book_id' => (int)($_POST['book_id'] ?? 0),
                'issue_date' => $_POST['issue_date'] ?? '',
                'due_date' => $_POST['due_date'] ?? ''
            ];

            // Validate
            if (empty($data['user_id']) || empty($data['book_id'])) {
                $_SESSION['message'] = 'Please select a user and book';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/transactions/issue');
                return;
            }

            $transactionModel = $this->loadModel('Transaction');
            $result = $transactionModel->issueBook($data);

            if ($result) {
                $_SESSION['message'] = 'Book issued successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to issue book. User may not exist, book may not be available, or invalid dates.';
                $_SESSION['message_type'] = 'danger';
            }

            $this->redirect('/transactions');
        }
    }

    /**
     * Show form to return a book
     */
    public function returnForm($id) {
        $transactionModel = $this->loadModel('Transaction');
        $transaction = $transactionModel->getById($id);

        if (!$transaction) {
            $_SESSION['message'] = 'Transaction not found';
            $_SESSION['message_type'] = 'danger';
            $this->redirect('/transactions');
            return;
        }

        if (!empty($transaction['return_date'])) {
            $_SESSION['message'] = 'This book has already been returned';
            $_SESSION['message_type'] = 'warning';
            $this->redirect('/transactions');
            return;
        }

        $this->view->assign('transaction', $transaction);
        $this->view->render('transaction/return', 'layouts/admin');
    }

    /**
     * Handle book return
     */
    public function returnBook($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'return_date' => $_POST['return_date'] ?? '',
                'fine_paid' => $_POST['fine_paid'] ?? '',
                'calculate_fine' => isset($_POST['calculate_fine']) ? true : false
            ];

            $transactionModel = $this->loadModel('Transaction');
            $result = $transactionModel->returnBook($id, $data);

            if ($result) {
                $_SESSION['message'] = 'Book returned successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to return book';
                $_SESSION['message_type'] = 'danger';
            }

            $this->redirect('/transactions');
        }
    }

    /**
     * Get transaction details (API endpoint)
     */
    public function view($id) {
        $transactionModel = $this->loadModel('Transaction');
        $transaction = $transactionModel->getById($id);

        if (!$transaction) {
            http_response_code(404);
            echo json_encode(['error' => 'Transaction not found']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode($transaction);
    }

    /**
     * Get overdue transactions (API endpoint)
     */
    public function overdue() {
        $transactionModel = $this->loadModel('Transaction');
        $overdue = $transactionModel->getOverdue();

        header('Content-Type: application/json');
        echo json_encode($overdue);
    }
}
?>