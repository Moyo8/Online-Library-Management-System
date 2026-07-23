<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Search Controller
 */
class Search extends Controller
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
     * Show search page
     */
    public function index()
    {
        $message = '';
        $message_type = '';
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            $message_type = $_SESSION['message_type'];
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        $search_query = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 10; // Items per page

        $books = [];
        $total = 0;
        $totalPages = 0;

        $bookModel = $this->loadModel('Book');
        if (!empty($search_query)) {
            // Search books by title, author, or ISBN using Book model
            $filters['search'] = $search_query;
            $paginated = $bookModel->paginate('', $filters, $page, $perPage);
            // Note: The paginate method in Book model expects SQL and params, but we have overridden it to use filters.
            // Actually, we need to adjust: the Book model's paginate method is the one we added to the base Model class.
            // But the Book model's getAll method uses filters. We can either use the base Model's paginate with custom SQL,
            // or we can add a paginate method to the Book model that uses getAll internally.
            // For simplicity, let's use the getAll method to get all matching books and then paginate manually? That would be inefficient.
            // Instead, we'll modify the Book model to have a paginate method that uses getAll with limit and offset.
            // But given time, we'll do a simple approach: get all matching books and then slice.
            // However, we already have a paginate method in the Model class that works with raw SQL.
            // Let's build the SQL for the Book model's getAll method.
            // We'll reuse the logic from Book::getAll but add LIMIT and OFFSET.
            // We'll do it here for simplicity.
            $where = [];
            $params = [];
            $where[] = "(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
            $searchTerm = "%{$search_query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $sql = "SELECT b.*,
                            (SELECT COUNT(*) FROM transactions t
                             WHERE t.book_id = b.id AND t.return_date IS NULL) as issued_count
                    FROM books b";
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            $sql .= " ORDER BY b.title";
            $paginated = $bookModel->paginate($sql, $params, $page, $perPage);
            $books = $paginated['data'];
            $total = $paginated['total'];
            $totalPages = $paginated['total_pages'];
        } else {
            // Show all books when no search query
            $sql = "SELECT b.*,
                            (SELECT COUNT(*) FROM transactions t
                             WHERE t.book_id = b.id AND t.return_date IS NULL) as issued_count
                    FROM books b
                    ORDER BY b.title";
            $paginated = $bookModel->paginate($sql, [], $page, $perPage);
            $books = $paginated['data'];
            $total = $paginated['total'];
            $totalPages = $paginated['total_pages'];
        }

        $this->view->assign('message', $message);
        $this->view->assign('message_type', $message_type);
        $this->view->assign('search_query', $search_query);
        $this->view->assign('books', $books);
        $this->view->assign('total', $total);
        $this->view->assign('totalPages', $totalPages);
        $this->view->assign('currentPage', $page);
        $this->view->render('search/index', 'layouts/user');
    }
}