<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * User Management Controller (Admin only)
 */
class UserManagement extends Controller {
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
     * Show list of users
     */
    public function index() {
        $userModel = $this->loadModel('User');

        // Get search parameter
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 10; // Items per page

        // Build WHERE clause and parameters
        $where = [];
        $params = [];

        // Search filter
        if (!empty($search)) {
            $where[] = "(u.name LIKE ? OR u.email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Role filter
        if (!empty($role)) {
            $where[] = "u.role = ?";
            $params[] = $role;
        }

        // Base SQL
        $sql = "SELECT u.* FROM users u";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY u.id";

        // Get paginated results
        $paginated = $userModel->paginate($sql, $params, $page, $perPage);
        $users = $paginated['data'];

        // Get stats (not paginated)
        $stats = $userModel->getStats();

        $this->view->assign('users', $users);
        $this->view->assign('search', $search);
        $this->view->assign('role', $role);
        $this->view->assign('stats', $stats);
        $this->view->assign('pagination', $paginated);

        $this->view->render('user/index', 'layouts/admin');
    }

    /**
     * Show form to create new user
     */
    public function create() {
        $this->view->render('user/create', 'layouts/admin');
    }

    /**
     * Handle user creation
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? 'user'
            ];

            // Basic validation
            if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                $_SESSION['message'] = 'Please fill in all required fields';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/users/create');
                return;
            }

            // Password strength validation
            if (strlen($data['password']) < 6) {
                $_SESSION['message'] = 'Password must be at least 6 characters long';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/users/create');
                return;
            }

            $userModel = $this->loadModel('User');
            $result = $userModel->create($data);

            if ($result) {
                $_SESSION['message'] = 'User added successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to add user. Email may already exist.';
                $_SESSION['message_type'] = 'danger';
            }

            $this->redirect('/users');
        }
    }

    /**
     * Show form to edit user
     */
    public function edit($id) {
        $userModel = $this->loadModel('User');
        $user = $userModel->getById($id);

        if (!$user) {
            $_SESSION['message'] = 'User not found';
            $_SESSION['message_type'] = 'danger';
            $this->redirect('/users');
            return;
        }

        // Prevent editing own role from admin panel (security)
        if ($id == $_SESSION['user_id']) {
            unset($user['role']); // Don't allow role change for self
        }

        $this->view->assign('user', $user);
        $this->view->render('user/edit', 'layouts/admin');
    }

    /**
     * Handle user update
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? ''
            ];

            // Basic validation
            if (empty($data['name']) || empty($data['email'])) {
                $_SESSION['message'] = 'Please fill in name and email';
                $_SESSION['message_type'] = 'danger';
                $this->redirect("/users/edit/$id");
                return;
            }

            // Prevent changing own role to non-admin (security)
            if ($id == $_SESSION['user_id'] && empty($data['role'])) {
                $data['role'] = 'admin'; // Keep current role if trying to remove admin from self
            }

            $userModel = $this->loadModel('User');
            $result = $userModel->update($id, $data);

            if ($result) {
                $_SESSION['message'] = 'User updated successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to update user. Email may already exist for another user.';
                $_SESSION['message_type'] = 'danger';
            }

            $this->redirect('/users');
        }
    }

    /**
     * Handle user deletion
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Prevent deletion of self
            if ($id == $_SESSION['user_id']) {
                $_SESSION['message'] = 'You cannot delete your own account';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/users');
                return;
            }

            $userModel = $this->loadModel('User');
            $result = $userModel->delete($id);

            if ($result) {
                $_SESSION['message'] = 'User deleted successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to delete user. User may have active loans.';
                $_SESSION['message_type'] = 'danger';
            }

            $this->redirect('/users');
        }
    }

    /**
     * Get user details (API endpoint)
     */
    public function view($id) {
        $userModel = $this->loadModel('User');
        $user = $userModel->getById($id);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }

        // Remove sensitive data
        unset($user['password']);

        header('Content-Type: application/json');
        echo json_encode($user);
    }
}
?>