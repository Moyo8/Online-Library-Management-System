<?php
// Database configuration
$host = 'localhost';
$db = 'olms';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

global $pdo;

// Initialize PDO connection with detailed error reporting
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Verify connection was successful
    if (!$pdo) {
        throw new Exception('PDO connection returned null');
    }
} catch (\PDOException $e) {
    // Display detailed error message
    $error_msg = 'Database Connection Error: ' . $e->getMessage();
    
    // Log the error
    error_log($error_msg);
    
    // For development: show the error
    if (defined('DEBUG') && DEBUG) {
        echo '<h1>Database Connection Failed</h1>';
        echo '<p>' . htmlspecialchars($error_msg) . '</p>';
        echo '<p><strong>Connection Details:</strong></p>';
        echo '<ul>';
        echo '<li>Host: ' . htmlspecialchars($host) . '</li>';
        echo '<li>Database: ' . htmlspecialchars($db) . '</li>';
        echo '<li>User: ' . htmlspecialchars($user) . '</li>';
        echo '</ul>';
    }
    
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Helper function to initialize session
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Helper function to redirect with message
function redirect_with_message($url, $message, $type = 'info') {
    header("Location: $url?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit;
}
?>
