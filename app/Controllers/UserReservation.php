<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * User Reservation Controller
 */
class UserReservation extends Controller
{
    /**
     * Check if user is logged in and is a regular user
     */
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
            $this->redirect('/home/login');
        }
    }

    /**
     * Show reservation form for a specific book
     */
    public function create()
    {
        $book_id = (int)($_GET['book_id'] ?? 0);

        if ($book_id <= 0) {
            $_SESSION['message'] = 'Please select a book to reserve.';
            $_SESSION['message_type'] = 'info';
            $this->redirect('/search');
            return;
        }

        $message = '';
        $message_type = '';
        $existing_reservation = null;
        $book = null;
        $available_copies = 0;

        // Check if book exists and get details
        $bookModel = $this->loadModel('Book');
        $sql = "SELECT b.*, (SELECT COUNT(*) FROM transactions t WHERE t.book_id = b.id AND t.return_date IS NULL) as issued_count FROM books b WHERE b.id = ?";
        $result = $bookModel->findAll($sql, [$book_id]);
        $book = $result ? $result[0] : null;

        if (!$book) {
            $message = 'Book not found.';
            $message_type = 'danger';
        } else {
            $available_copies = $book['quantity'] - $book['issued_count'];
            // Check if user already has a pending reservation for this book
            $reservationModel = $this->loadModel('Reservation');
            $sql = "SELECT id FROM reservations WHERE user_id = ? AND book_id = ? AND status = 'pending'";
            $result = $reservationModel->findAll($sql, [$this->getUserId(), $book_id]);
            $existing_reservation = $result ? $result[0] : null;

            if ($existing_reservation) {
                $message = 'You already have a pending reservation for this book.';
                $message_type = 'warning';
            } elseif ($available_copies > 0) {
                $message = 'This book is currently available. You can borrow it directly instead of reserving.';
                $message_type = 'info';
            } else {
                // Handle reservation creation
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $reservationModel = $this->loadModel('Reservation');
                    $data = [
                        'user_id' => $this->getUserId(),
                        'book_id' => $book_id,
                        'status' => 'pending'
                    ];
                    $result = $reservationModel->create($data);
                    if ($result) {
                        $message = 'Your reservation has been placed successfully. You will be notified when the book becomes available.';
                        $message_type = 'success';
                        // Redirect to avoid form resubmission
                        $this->redirect('/user/reservation/create?book_id=' . $book_id);
                        return;
                    } else {
                        $message = 'Failed to place reservation. Please try again.';
                        $message_type = 'danger';
                    }
                }
                // If not POST, show confirmation form (handled in view)
            }
        }

        $this->view->assign('book', $book);
        $this->view->assign('available_copies', $available_copies);
        $this->view->assign('existing_reservation', $existing_reservation);
        $this->view->assign('message', $message);
        $this->view->assign('message_type', $message_type);
        $this->view->render('user_reservation/create', 'layouts/user');
    }

    /**
     * Cancel a reservation
     * @param int $id Reservation ID
     */
    public function cancel($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['message'] = 'Invalid reservation ID.';
            $_SESSION['message_type'] = 'danger';
            $this->redirect('/my/books');
            return;
        }

        // Load reservation model
        $reservationModel = $this->loadModel('Reservation');
        $reservation = $reservationModel->getById($id);

        if (!$reservation) {
            $_SESSION['message'] = 'Reservation not found.';
            $_SESSION['message_type'] = 'danger';
            $this->redirect('/my/books');
            return;
        }

        if ($reservation['status'] !== 'pending') {
            $_SESSION['message'] = 'This reservation is not pending and cannot be cancelled.';
            $_SESSION['message_type'] = 'warning';
            $this->redirect('/my/books');
            return;
        }

        // Check ownership: only allow the user who made the reservation to cancel it
        if ($reservation['user_id'] !== $this->getUserId()) {
            $_SESSION['message'] = 'You are not authorized to cancel this reservation.';
            $_SESSION['message_type'] = 'danger';
            $this->redirect('/my/books');
            return;
        }

        // Cancel the reservation
        $result = $reservationModel->cancel($id);

        if ($result) {
            $_SESSION['message'] = 'Reservation cancelled successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to cancel reservation.';
            $_SESSION['message_type'] = 'danger';
        }

        $this->redirect('/my/books');
    }

    /**
     * Get current user ID
     */
    private function getUserId()
    {
        return $_SESSION['user_id'];
    }
}