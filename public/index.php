<?php
/**
 * Front Controller - Entry point for all requests
 */

// Set proper content type
header('Content-Type: text/html; charset=utf-8');

// Define constants
define('ROOT', __DIR__ . '/../');
define('APP', ROOT . 'app/');
define('CONFIG', ROOT . 'config/');

// Load the root config.php to set up $pdo and helper functions
require_once ROOT . 'config.php';

// Load Composer autoloader if exists
if (file_exists(ROOT . 'vendor/autoload.php')) {
    require_once ROOT . 'vendor/autoload.php';
}

// Load core files
require_once APP . 'Core/Router.php';
require_once APP . 'Core/Controller.php';
require_once APP . 'Core/Model.php';
require_once APP . 'Core/View.php';
require_once APP . 'helpers.php';

// Load the AI assistant functions
require_once ROOT . 'functions/ai_assistant.php';

// Load the Database class definition
require_once CONFIG . 'database.php';

// Initialize database instance
Database::getInstance();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load routes and get the router instance
require_once CONFIG . 'routes.php';

// Initialize router and dispatch request
global $router;
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
?>