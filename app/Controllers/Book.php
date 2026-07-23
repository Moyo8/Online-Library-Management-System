<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Book Controller
 */
class Book extends Controller {
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
     * Show list of books
     */
    public function index() {
        $bookModel = $this->loadModel('Book');

        // Get search parameter
        $search = $_GET['search'] ?? '';
        $available = isset($_GET['available']) ? true : false;
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 10; // Items per page

        // Build WHERE clause and parameters
        $where = [];
        $params = [];

        // Search filter
        if (!empty($search)) {
            $where[] = "(b.title LIKE ? OR b.author LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Availability filter
        if ($available) {
            $where[] = "b.quantity > (SELECT COUNT(*) FROM transactions t WHERE t.book_id = b.id AND t.return_date IS NULL)";
        }

        // Base SQL
        $sql = "SELECT b.*,
                        (SELECT COUNT(*) FROM transactions t
                         WHERE t.book_id = b.id AND t.return_date IS NULL) as issued_count
                FROM books b";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY b.title";

        // Get paginated results
        $paginated = $bookModel->paginate($sql, $params, $page, $perPage);
        $books = $paginated['data'];

        $this->view->assign('books', $books);
        $this->view->assign('search', $search);
        $this->view->assign('available', $available);
        $this->view->assign('pagination', $paginated);

        $this->view->render('book/index', 'layouts/admin');
    }

    /**
     * Show form to create new book
     */
    public function create() {
        $this->view->render('book/create', 'layouts/admin');
    }

    /**
     * Handle book creation
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'author' => trim($_POST['author'] ?? ''),
                'isbn' => trim($_POST['isbn'] ?? ''),
                'quantity' => (int)($_POST['quantity'] ?? 1),
                'category' => trim($_POST['category'] ?? ''),
                'publisher' => trim($_POST['publisher'] ?? ''),
                'published_year' => !empty($_POST['published_year']) ? (int)$_POST['published_year'] : null
            ];

            $bookModel = $this->loadModel('Book');
            $result = $bookModel->create($data);

            if ($result) {
                $_SESSION['message'] = 'Book added successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to add book. Please check the data and try again.';
                $_SESSION['message_type'] = 'danger';
            }

            $this->redirect('/books');
        }
    }

    /**
     * Show form to edit book
     */
    public function edit($id) {
        $bookModel = $this->loadModel('Book');
        $book = $bookModel->getById($id);

        if (!$book) {
            $_SESSION['message'] = 'Book not found';
            $_SESSION['message_type'] = 'danger';
            $this->redirect('/books');
            return;
        }

        $this->view->assign('book', $book);
        $this->view->render('book/edit', 'layouts/admin');
    }

    /**
     * Handle book update
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'author' => trim($_POST['author'] ?? ''),
                'isbn' => trim($_POST['isbn'] ?? ''),
                'quantity' => (int)($_POST['quantity'] ?? 1),
                'category' => trim($_POST['category'] ?? ''),
                'publisher' => trim($_POST['publisher'] ?? ''),
                'published_year' => !empty($_POST['published_year']) ? (int)$_POST['published_year'] : null
            ];

            $bookModel = $this->loadModel('Book');
            $result = $bookModel->update($id, $data);

            if ($result) {
                $_SESSION['message'] = 'Book updated successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to update book. Please check the data and try again.';
                $_SESSION['message_type'] = 'danger';
            }

            $this->redirect('/books');
        }
    }

    /**
     * Handle book deletion
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bookModel = $this->loadModel('Book');
            $result = $bookModel->delete($id);

            if ($result) {
                $_SESSION['message'] = 'Book deleted successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to delete book. It may have active loans.';
                $_SESSION['message_type'] = 'danger';
            }

            $this->redirect('/books');
        }
    }

    /**
     * Get book details (API endpoint)
     */
    public function view($id) {
        $bookModel = $this->loadModel('Book');
        $book = $bookModel->getById($id);

        if (!$book) {
            http_response_code(404);
            echo json_encode(['error' => 'Book not found']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode($book);
    }
}
?>