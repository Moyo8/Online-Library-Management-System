<?php
/**
 * Front Controller - Entry point for all requests
 */

// Set proper content type
header('Content-Type: text/html; charset=utf-8');

// Define constants
define('ROOT', __DIR__ . '/');
define('APP', ROOT . 'app/');
define('CONFIG', ROOT . 'config/');
define('BASE_PATH', '/olms');

// Load Composer autoloader if exists
if (file_exists(ROOT . 'vendor/autoload.php')) {
    require_once ROOT . 'vendor/autoload.php';
}

// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}


// Load global helpers (url() etc.)
require_once APP . 'helpers.php';

// Load root config FIRST to initialize PDO connection
require_once ROOT . 'config.php';

// Load core files
require_once APP . 'Core/Router.php';
require_once APP . 'Core/Controller.php';
require_once APP . 'Core/Model.php';
require_once APP . 'Core/View.php';
require_once CONFIG . 'database.php';

// Initialize database instance
Database::getInstance();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Ensure role is set correctly if user is logged in
$uid = $_SESSION['user_id']??null;
if ($uid !== null) {
    $currentRole = $_SESSION['user_role']??'';
    // If role missing or not valid, fetch from DB
    if (!in_array($currentRole, ['admin', 'librarian', 'user'])) {
        try {
            require_once APP . 'Models/User.php';
            $userModel = new \App\Models\User();
            $dbUser = $userModel->getById($uid);
            if ($dbUser) {
                $roleFromDb = $dbUser['role'] ?? 'user';
                if (empty($roleFromDb)) {
                    $roleFromDb = 'user';
                }
                $_SESSION['user_role'] = $roleFromDb;
            } else {
                // User not found, clear session
                $_SESSION = [];
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                        $params["path"], $params["domain"],
                        $params["secure"], $params["httponly"]
                    );
                }
                session_destroy();
            }
        } catch (\Exception $e) {
            error_log('Error correcting session role: '.$e->getMessage());
        }
    }
}

// Load routes and get the router instance
require_once CONFIG . 'routes.php';

// Initialize router and dispatch request
global $router;
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
?>