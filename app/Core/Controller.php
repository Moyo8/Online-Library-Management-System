<?php
namespace App\Core;

/**
 * Base Controller class
 */
class Controller {
    protected $view;

    public function __construct() {
        $this->view = new View();
    }

    /**
     * Load a model
     * @param string $model Model name
     * @return object Model instance
     */
    protected function loadModel($model) {
        $modelPath = APP . 'Models/' . $model . '.php';
        if (file_exists($modelPath)) {
            require_once $modelPath;
            $class = '\\App\\Models\\' . $model;
            return new $class();
        }
        throw new \Exception("Model not found: $model");
    }

    /**
     * Redirect to another URL
     * Uses the global url() helper to prepend the base path automatically.
     * @param string $url URL to redirect to
     */
    protected function redirect($url) {
        header('Location: ' . url($url));
        exit;
    }

    /**
     * Validate CSRF token for POST requests
     * @return bool True if valid or non-POST request, false if invalid
     */
    protected function validateCsrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                $_SESSION['message'] = 'Invalid form submission. Please try again.';
                $_SESSION['message_type'] = 'danger';
                return false;
            }
        }
        return true;
    }
}
?>