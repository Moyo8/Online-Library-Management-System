<?php
/**
 * Authentication helper functions
 */

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user has admin role
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if user has librarian role
 * @return bool True if librarian, false otherwise
 */
function isLibrarian() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'librarian';
}

/**
 * Check if user is either admin or librarian (staff)
 * @return bool True if staff, false otherwise
 */
function isStaff() {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'librarian']);
}

/**
 * Check if user is a regular user (not staff)
 * @return bool True if regular user, false otherwise
 */
function isRegularUser() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user';
}

/**
 * Validate user credentials
 * @param string $email User email
 * @param string $password User password
 * @param PDO $pdo Database connection
 * @return bool True if credentials are valid, false otherwise
 */
function validateUserCredentials($email, $password, $pdo) {
    try {
        $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Redirect to login if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Redirect to login if not staff (admin or librarian)
 */
function requireStaff() {
    if (!isStaff()) {
        header('Location: login.php');
        exit;
    }
}
?>