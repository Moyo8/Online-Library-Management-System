<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Lib\Auth;
use App\Models\Book;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Reservation;

/**
 * API Controller - RESTful API endpoints
 */
class API extends Controller {
    private $auth;

    public function __construct() {
        parent::__construct();
        $this->auth = new Auth();
    }

    /**
     * Check API token validity
     * @return array|false User data or false if invalid
     */
    private function checkApiAuth() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return false;
        }

        $authHeader = $headers['Authorization'];
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return false;
        }

        $token = $matches[1];
        return $this->auth->validateToken($token);
    }

    /**
     * Send JSON response
     * @param mixed $data Data to send
     * @param int $status HTTP status code
     */
    protected function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    /**
     * Send error response
     * @param string $message Error message
     * @param int $status HTTP status code
     */
    protected function errorResponse($message, $status = 400) {
        $this->jsonResponse(['error' => $message], $status);
    }

    // ========== BOOK API ENDPOINTS ==========

    public function getBooks() {
        $userData = $this->checkApiAuth();
        if (!$userData) {
            $this->errorResponse('Unauthorized', 401);
            return;
        }

        $bookModel = new Book();
        $filters = [];

        // Optional filters from query params
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        if (!empty($_GET['available'])) {
            $filters['available'] = true;
        }

        $books = $bookModel->getAll($filters);
        $this->jsonResponse($books);
    }

    public function getBook($id) {
        $userData = $this->checkApiAuth();
        if (!$userData) {
            $this->errorResponse('Unauthorized', 401);
            return;
        }

        $bookModel = new Book();
        $book = $bookModel->getById($id);

        if (!$book) {
            $this->errorResponse('Book not found', 404);
            return;
        }

        $this->jsonResponse($book);
    }

    public function createBook() {
        $userData = $this->checkApiAuth();
        if (!$userData || $userData['role'] !== 'admin' && $userData['role'] !== 'librarian') {
            $this->errorResponse('Unauthorized', 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->errorResponse('Invalid JSON', 400);
            return;
        }

        $bookModel = new Book();
        $result = $bookModel->create($input);

        if ($result) {
            $this->jsonResponse(['id' => $result, 'message' => 'Book created'], 201);
        } else {
            $this->errorResponse('Failed to create book', 400);
        }
    }

    public function updateBook($id) {
        $userData = $this->checkApiAuth();
        if (!$userData || $userData['role'] !== 'admin' && $userData['role'] !== 'librarian') {
            $this->errorResponse('Unauthorized', 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->errorResponse('Invalid JSON', 400);
            return;
        }

        $bookModel = new Book();
        $result = $bookModel->update($id, $input);

        if ($result) {
            $this->jsonResponse(['message' => 'Book updated']);
        } else {
            $this->errorResponse('Failed to update book', 400);
        }
    }

    public function deleteBook($id) {
        $userData = $this->checkApiAuth();
        if (!$userData || $userData['role'] !== 'admin' && $userData['role'] !== 'librarian') {
            $this->errorResponse('Unauthorized', 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
            return;
        }

        $bookModel = new Book();
        $result = $bookModel->delete($id);

        if ($result) {
            $this->jsonResponse(['message' => 'Book deleted']);
        } else {
            $this->errorResponse('Failed to delete book', 400);
        }
    }

    // ========== USER API ENDPOINTS ==========

    public function getUsers() {
        $userData = $this->checkApiAuth();
        if (!$userData || $userData['role'] !== 'admin') {
            $this->errorResponse('Unauthorized', 401);
            return;
        }

        $userModel = new User();
        $filters = [];

        // Optional filters from query params
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        if (!empty($_GET['role'])) {
            $filters['role'] = $_GET['role'];
        }

        $users = $userModel->getAll($filters);
        $this->jsonResponse($users);
    }

    public function getUser($id) {
        $userData = $this->checkApiAuth();
        if (!$userData) {
            $this->errorResponse('Unauthorized', 401);
            return;
        }

        // Users can only view their own data unless admin
        if ($userData['role'] !== 'admin' && (int)$userData['user_id'] !== (int)$id) {
            $this->errorResponse('Unauthorized', 403);
            return;
        }

        $userModel = new User();
        $user = $userModel->getById($id);

        if (!$user) {
            $this->errorResponse('User not found', 404);
            return;
        }

        // Remove sensitive data
        unset($user['password']);
        $this->jsonResponse($user);
    }

    // ========== TRANSACTION API ENDPOINTS ==========

    public function getTransactions() {
        $userData = $this->checkApiAuth();
        if (!$userData || ($userData['role'] !== 'admin' && $userData['role'] !== 'librarian')) {
            $this->errorResponse('Unauthorized', 401);
            return;
        }

        $transactionModel = new Transaction();
        $filters = [];

        // Optional filters from query params
        if (!empty($_GET['user_id'])) {
            $filters['user_id'] = (int)$_GET['user_id'];
        }
        if (!empty($_GET['book_id'])) {
            $filters['book_id'] = (int)$_GET['book_id'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status']; // active, returned, overdue
        }

        $transactions = $transactionModel->getAll($filters);
        $this->jsonResponse($transactions);
    }

    public function issueBook() {
        $userData = $this->checkApiAuth();
        if (!$userData || ($userData['role'] !== 'admin' && $userData['role'] !== 'librarian')) {
            $this->errorResponse('Unauthorized', 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->errorResponse('Invalid JSON', 400);
            return;
        }

        // Validate required fields
        if (empty($input['user_id']) || empty($input['book_id'])) {
            $this->errorResponse('User ID and Book ID are required', 400);
            return;
        }

        $transactionModel = new Transaction();
        $result = $transactionModel->issueBook($input);

        if ($result) {
            $this->jsonResponse(['id' => $result, 'message' => 'Book issued'], 201);
        } else {
            $this->errorResponse('Failed to issue book', 400);
        }
    }

    public function returnBook($id) {
        $userData = $this->checkApiAuth();
        if (!$userData || ($userData['role'] !== 'admin' && $userData['role'] !== 'librarian')) {
            $this->errorResponse('Unauthorized', 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->errorResponse('Invalid JSON', 400);
            return;
        }

        $transactionModel = new Transaction();
        $result = $transactionModel->returnBook($id, $input);

        if ($result) {
            $this->jsonResponse(['message' => 'Book returned']);
        } else {
            $this->errorResponse('Failed to return book', 400);
        }
    }

    public function getOverdueBooks() {
        $userData = $this->checkApiAuth();
        if (!$userData || ($userData['role'] !== 'admin' && $userData['role'] !== 'librarian')) {
            $this->errorResponse('Unauthorized', 401);
            return;
        }

        $transactionModel = new Transaction();
        $overdue = $transactionModel->getOverdue();
        $this->jsonResponse($overdue);
    }

    // ========== RESERVATION API ENDPOINTS ==========

    public function getReservations() {
        $userData = $this->checkApiAuth();
        if (!$userData || ($userData['role'] !== 'admin' && $userData['role'] !== 'librarian')) {
            $this->errorResponse('Unauthorized', 401);
            return;
        }

        $reservationModel = new Reservation();
        $filters = [];

        // Optional filters from query params
        if (!empty($_GET['user_id'])) {
            $filters['user_id'] = (int)$_GET['user_id'];
        }
        if (!empty($_GET['book_id'])) {
            $filters['book_id'] = (int)$_GET['book_id'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status']; // pending, fulfilled, cancelled
        }

        $reservations = $reservationModel->getAll($filters);
        $this->jsonResponse($reservations);
    }

    public function createReservation() {
        $userData = $this->checkApiAuth();
        if (!$userData) {
            $this->errorResponse('Unauthorized', 401);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->errorResponse('Invalid JSON', 400);
            return;
        }

        // Validate required fields
        if (empty($input['user_id']) || empty($input['book_id'])) {
            $this->errorResponse('User ID and Book ID are required', 400);
            return;
        }

        $reservationModel = new Reservation();
        $result = $reservationModel->create($input);

        if ($result) {
            $this->jsonResponse(['id' => $result, 'message' => 'Reservation created'], 201);
        } else {
            $this->errorResponse('Failed to create reservation', 400);
        }
    }

    public function fulfillReservation($id) {
        $userData = $this->checkApiAuth();
        if (!$userData || ($userData['role'] !== 'admin' && $userData['role'] !== 'librarian')) {
            $this->errorResponse('Unauthorized', 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->errorResponse('Invalid JSON', 400);
            return;
        }

        $reservationModel = new Reservation();
        $result = $reservationModel->fulfill($id, $input);

        if ($result) {
            $this->jsonResponse(['message' => 'Reservation fulfilled']);
        } else {
            $this->errorResponse('Failed to fulfill reservation', 400);
        }
    }

    public function cancelReservation($id) {
        $userData = $this->checkApiAuth();
        if (!$userData || ($userData['role'] !== 'admin' && $userData['role'] !== 'librarian')) {
            $this->errorResponse('Unauthorized', 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
            return;
        }

        $reservationModel = new Reservation();
        $result = $reservationModel->cancel($id);

        if ($result) {
            $this->jsonResponse(['message' => 'Reservation cancelled']);
        } else {
            $this->errorResponse('Failed to cancel reservation', 400);
        }
    }

    // ========== AUTH API ENDPOINTS ==========

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->errorResponse('Invalid JSON', 400);
            return;
        }

        // Validate required fields
        if (empty($input['email']) || empty($input['password'])) {
            $this->errorResponse('Email and password are required', 400);
            return;
        }

        // Authenticate user
        $userModel = new User();
        $user = $userModel->getByEmail($input['email']);

        if (!$user || !password_verify($input['password'], $user['password'])) {
            $this->errorResponse('Invalid credentials', 401);
            return;
        }

        // Generate token
        $token = $this->auth->generateToken([
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);

        // Remove password from user data
        unset($user['password']);

        $this->jsonResponse([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function profile() {
        $userData = $this->checkApiAuth();
        if (!$userData) {
            $this->errorResponse('Unauthorized', 401);
            return;
        }

        $userModel = new User();
        $user = $userModel->getById($userData['user_id']);

        if (!$user) {
            $this->errorResponse('User not found', 404);
            return;
        }

        // Remove sensitive data
        unset($user['password']);
        $this->jsonResponse($user);
    }
}
?>