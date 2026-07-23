<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Reservation Controller (Staff only)
 */
class Reservation extends Controller {
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
     * Show list of reservations
     */
    public function index() {
        $reservationModel = $this->loadModel('Reservation');

        // Get filter parameters
        $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : '';
        $book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : '';
        $status = $_GET['status'] ?? ''; // pending, fulfilled, cancelled
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 10; // Items per page

        // Build WHERE clause and parameters
        $where = [];
        $params = [];

        if (!empty($user_id)) {
            $where[] = "r.user_id = ?";
            $params[] = $user_id;
        }
        if (!empty($book_id)) {
            $where[] = "r.book_id = ?";
            $params[] = $book_id;
        }
        if (!empty($status)) {
            $where[] = "r.status = ?";
            $params[] = $status;
        }

        // Base SQL
        $sql = "SELECT r.*, u.name as user_name, b.title as book_title
                FROM reservations r
                JOIN users u ON r.user_id = u.id
                JOIN books b ON r.book_id = b.id";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY r.reservation_date DESC";

        // Get paginated results
        $paginated = $reservationModel->paginate($sql, $params, $page, $perPage);
        $reservations = $paginated['data'];

        // Get stats (not paginated)
        $stats = $reservationModel->getStats();

        // Get users and books for filter dropdowns
        $userModel = $this->loadModel('User');
        $users = $userModel->getAll();

        $bookModel = $this->loadModel('Book');
        $books = $bookModel->getAll();

        $this->view->assign('reservations', $reservations);
        $this->view->assign('stats', $stats);
        $this->view->assign('users', $users);
        $this->view->assign('books', $books);
        $this->view->assign('filters', [
            'user_id' => $user_id,
            'book_id' => $book_id,
            'status' => $status
        ]);
        $this->view->assign('pagination', $paginated);

        $this->view->render('reservation/index', 'layouts/admin');
    }

    /**
     * Show form to create a reservation
     */
    public function create() {
        // Get users and books for dropdowns (only show available books for reservation?)
        $userModel = $this->loadModel('User');
        $users = $userModel->getAll();

        $bookModel = $this->loadModel('Book');
        $books = $bookModel->getAll();

        $this->view->assign('users', $users);
        $this->view->assign('books', $books);
        $this->view->render('reservation/create', 'layouts/admin');
    }

    /**
     * Handle reservation creation
     */
    public function createReservation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => (int)($_POST['user_id'] ?? 0),
                'book_id' => (int)($_POST['book_id'] ?? 0)
            ];

            // Validate
            if (empty($data['user_id']) || empty($data['book_id'])) {
                $_SESSION['message'] = 'Please select a user and book';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/reservations/create');
                return;
            }

            $reservationModel = $this->loadModel('Reservation');
            $result = $reservationModel->create($data);

            if ($result) {
                $_SESSION['message'] = 'Reservation created successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to create reservation. User may not exist, book may not be available, or user already has a pending reservation for this book.';
                $_SESSION['message_type'] = 'danger';
            }

            $this->redirect('/reservations');
        }
    }

    /**
     * Show form to fulfill a reservation
     */
    public function fulfill($id) {
        $reservationModel = $this->loadModel('Reservation');
        $reservation = $reservationModel->getById($id);

        if (!$reservation) {
            $_SESSION['message'] = 'Reservation not found';
            $_SESSION['message_type'] = 'danger';
            $this->redirect('/reservations');
            return;
        }

        if ($reservation['status'] !== 'pending') {
            $_SESSION['message'] = 'This reservation is not pending';
            $_SESSION['message_type'] = 'warning';
            $this->redirect('/reservations');
            return;
        }

        $this->view->assign('reservation', $reservation);
        $this->view->render('reservation/fulfill', 'layouts/admin');
    }

    /**
     * Handle reservation fulfillment
     */
    public function fulfillReservation($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'issue_book' => isset($_POST['issue_book']) ? true : false
            ];

            $reservationModel = $this->loadModel('Reservation');
            $result = $reservationModel->fulfill($id, $data);

            if ($result) {
                $_SESSION['message'] = 'Reservation fulfilled successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to fulfill reservation';
                $_SESSION['message_type'] = 'danger';
            }

            $this->redirect('/reservations');
        }
    }

    /**
     * Show form to cancel a reservation
     */
    public function cancel($id) {
        $reservationModel = $this->loadModel('Reservation');
        $reservation = $reservationModel->getById($id);

        if (!$reservation) {
            $_SESSION['message'] = 'Reservation not found';
            $_SESSION['message_type'] = 'danger';
            $this->redirect('/reservations');
            return;
        }

        if ($reservation['status'] !== 'pending') {
            $_SESSION['message'] = 'This reservation is not pending';
            $_SESSION['message_type'] = 'warning';
            $this->redirect('/reservations');
            return;
        }

        $this->view->assign('reservation', $reservation);
        $this->view->render('reservation/cancel', 'layouts/admin');
    }

    /**
     * Handle reservation cancellation
     */
    public function cancelReservation($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reservationModel = $this->loadModel('Reservation');
            $result = $reservationModel->cancel($id);

            if ($result) {
                $_SESSION['message'] = 'Reservation cancelled successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to cancel reservation';
                $_SESSION['message_type'] = 'danger';
            }

            $this->redirect('/reservations');
        }
    }

    /**
     * Get reservation details (API endpoint)
     */
    public function view($id) {
        $reservationModel = $this->loadModel('Reservation');
        $reservation = $reservationModel->getById($id);

        if (!$reservation) {
            http_response_code(404);
            echo json_encode(['error' => 'Reservation not found']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode($reservation);
    }

    /**
     * Get pending reservations for a book (API endpoint)
     */
    public function pendingForBook($book_id) {
        $reservationModel = $this->loadModel('Reservation');
        $pending = $reservationModel->getPendingForBook($book_id);

        header('Content-Type: application/json');
        echo json_encode($pending);
    }
}
?>