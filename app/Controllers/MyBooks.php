<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * MyBooks Controller
 */
class MyBooks extends Controller
{
    /**
     * Check if user is logged in
     */
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/home/login');
        }
    }

    /**
     * Show user's borrowed books and reservations
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];

        // Get user's currently borrowed books
        $borrowedBooks = $this->getBorrowedBooks($userId);

        // Get user's reservation history
        $reservations = $this->getReservations($userId);

        // Handle messages from cancellation
        $message = $_GET['message'] ?? '';
        $messageType = $_GET['type'] ?? 'info';

        $this->view->assign('borrowed_books', $borrowedBooks);
        $this->view->assign('reservations', $reservations);
        $this->view->assign('message', $message);
        $this->view->assign('message_type', $messageType);
        $this->view->render('my_books/index', 'layouts/user');
    }

    /**
     * Get user's currently borrowed books
     */
    private function getBorrowedBooks($userId)
    {
        $transactionModel = $this->loadModel('Transaction');
        $sql = "SELECT t.id AS transaction_id, b.id AS book_id, b.title, b.author, t.issue_date, t.due_date,
                IF(t.due_date < CURDATE(), DATEDIFF(CURDATE(), t.due_date) * 0.10, 0) AS fine
                FROM transactions t
                JOIN books b ON t.book_id = b.id
                WHERE t.user_id = ?
                AND t.return_date IS NULL
                ORDER BY t.issue_date DESC";
        try {
            return $transactionModel->findAll($sql, [$userId]);
        } catch (\Exception $e) {
            error_log('MyBooks::getBorrowedBooks() - Query failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user's reservation history
     */
    private function getReservations($userId)
    {
        $reservationModel = $this->loadModel('Reservation');
        $sql = "SELECT r.*, b.title as book_title
                FROM reservations r
                JOIN books b ON r.book_id = b.id
                WHERE r.user_id = ?
                ORDER BY r.reservation_date DESC";
        return $reservationModel->findAll($sql, [$userId]);
    }

    /**
     * Handle book return for user
     */
    public function returnBook()
    {
        // Only allow POST requests for security
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/my/books');
            return;
        }

        $transactionId = (int)($_POST['transaction_id'] ?? 0);
        if ($transactionId <= 0) {
            $_SESSION['message'] = 'Invalid transaction.';
            $_SESSION['message_type'] = 'danger';
            $this->redirect('/my/books');
            return;
        }

        // Load transaction model
        $transactionModel = $this->loadModel('Transaction');
        $transaction = $transactionModel->getById($transactionId);

        // Validate transaction exists and belongs to current user
        if (!$transaction) {
            $_SESSION['message'] = 'Transaction not found.';
            $_SESSION['message_type'] = 'danger';
            $this->redirect('/my/books');
            return;
        }

        if ($transaction['user_id'] !== $_SESSION['user_id']) {
            $_SESSION['message'] = 'You are not authorized to return this book.';
            $_SESSION['message_type'] = 'danger';
            $this->redirect('/my/books');
            return;
        }

        // Check if already returned
        if (!empty($transaction['return_date'])) {
            $_SESSION['message'] = 'This book has already been returned.';
            $_SESSION['message_type'] = 'warning';
            $this->redirect('/my/books');
            return;
        }

        // Return the book
        $result = $transactionModel->returnBook($transactionId, [
            'return_date' => date('Y-m-d'),
            'calculate_fine' => true
        ]);

        if ($result) {
            $_SESSION['message'] = 'Book returned successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to return book. Please try again.';
            $_SESSION['message_type'] = 'danger';
        }

        $this->redirect('/my/books');
    }
}