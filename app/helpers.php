<?php
/**
 * Global helper functions for the OLMS application
 */

/**
 * Generate a URL with the application base path prepended.
 * Usage: url('/home/login') => '/olms/home/login'
 *
 * @param string $path The path (e.g., '/home/login')
 * @return string The full URL path with base
 */
function url($path = '/') {
    $base = defined('BASE_PATH') ? BASE_PATH : '';
    // If path already starts with the base path, return as-is
    if ($base !== '' && strncasecmp($path, $base, strlen($base)) === 0) {
        return $path;
    }
    // Ensure path starts with /
    if ($path !== '' && $path[0] !== '/') {
        $path = '/' . $path;
    }
    return $base . $path;
}

if (!function_exists('csrf_token')) {
    /**
     * Generate a CSRF token
     * @return string
     */
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF hidden input field
     * @return string
     */
    function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
    }
}

if (!function_exists('verify_csrf')) {
    /**
     * Verify CSRF token
     * @return bool
     */
    function verify_csrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                return false;
            }
        }
        return true;
    }
}
