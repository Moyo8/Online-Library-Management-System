<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Home Controller
 */
class Home extends Controller {
    /**
     * Show homepage
     */
    public function index() {
        // Redirect to login if not logged in
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/home/login');
            return;
        }

        // Redirect based on role
        if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'librarian') {
            $this->redirect('/admin/dashboard');
        } else {
            $this->redirect('/user/dashboard');
        }
    }

    /**
     * Show login page
     */
    public function login() {
        $this->view->render('auth/login', 'layouts/default');
    }

    /**
     * Show registration page
     */
    public function register() {
        $this->view->render('auth/register', 'layouts/default');
    }

    /**
     * Handle registration form submission
     */
    public function registerPost() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Basic validation
            if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
                $_SESSION['message'] = 'Please fill in all fields';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/home/register');
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['message'] = 'Invalid email format';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/home/register');
                return;
            }

            if (strlen($password) < 6) {
                $_SESSION['message'] = 'Password must be at least 6 characters';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/home/register');
                return;
            }

            if ($password !== $confirm_password) {
                $_SESSION['message'] = 'Passwords do not match';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/home/register');
                return;
            }

            // Check if email already exists
            $userModel = $this->loadModel('User');
            $existingUser = $userModel->getByEmail($email);

            if ($existingUser) {
                $_SESSION['message'] = 'Email already registered';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/home/register');
                return;
            }

            // Create user (model handles password hashing)
            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'user' // Always register as regular user
            ];

            $result = $userModel->create($userData);

            if ($result) {
                $_SESSION['message'] = 'Registration successful! Please login.';
                $_SESSION['message_type'] = 'success';
                $this->redirect('/home/login');
            } else {
                $_SESSION['message'] = 'Registration failed. Please try again.';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/home/register');
            }
        }
    }

    /**
     * Handle login form submission
     */
    public function loginPost() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['login_error'] = 'Please fill in all fields';
                $this->redirect('/home/login');
                return;
            }

            // Validate credentials using User model
            $userModel = $this->loadModel('User');
            $user = $userModel->getByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $role = $user['role'];
                if (empty($role)) {
                    $role = 'user';
                }
                $_SESSION['user_role'] = $role;

                // Redirect based on role
                $redirect = in_array($role, ['admin', 'librarian']) ? '/admin/dashboard' : '/user/dashboard';
                $this->redirect($redirect);
            } else {
                $_SESSION['login_error'] = 'Invalid email or password';
                $this->redirect('/home/login');
            }
        }
    }

    /**
     * Handle logout
     */
    public function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        // Show logout page
        $this->view->render('auth/logout', 'layouts/default');
    }
}