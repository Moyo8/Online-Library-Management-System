<?php
namespace App\Core;

/**
 * Router class to handle routing
 */
class Router {
    protected $routes = [];

    /**
     * Add a route
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $uri URI pattern
     * @param string $controller Controller@method
     */
    public function add($method, $uri, $controller) {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller
        ];
    }

    /**
     * Get all routes
     * @return array Routes
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Dispatch the request to the appropriate controller
     * @param string $uri Request URI
     * @param string $method Request method
     */
    public function dispatch($uri, $method) {
        // Remove the base path from the URI for route matching
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        if ($basePath !== '' && strncasecmp($uri, $basePath, strlen($basePath)) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        // Remove query string from URI
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Ensure URI starts with /
        if ($uri === '' || $uri === false) {
            $uri = '/';
        } elseif ($uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        // Strip .php extension for legacy URL support
        if (strlen($uri) > 4 && substr($uri, -4) === '.php') {
            $uri = substr($uri, 0, -4);
        }

        foreach ($this->getRoutes() as $route) {
            // Convert URI pattern to regex
            $pattern = '#^' . $route['uri'] . '$#';

            // Check if method and URI match
            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                // Remove the first element which is the full match
                array_shift($matches);

                // Split controller and action
                if (strpos($route['controller'], '@') !== false) {
                    list($controllerName, $action) = explode('@', $route['controller']);
                } else {
                    $controllerName = $route['controller'];
                    $action = 'index'; // Default action
                }

                // Load the controller
                $controllerClass = "App\\Controllers\\{$controllerName}";
                $controllerFile = APP . 'Controllers/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    require_once $controllerFile;

                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        if (method_exists($controller, $action)) {
                            call_user_func_array([$controller, $action], $matches);
                            return;
                        } else {
                            header('HTTP/1.0 500 Internal Server Error');
                            echo "Method {$action} not found in controller {$controllerName}";
                            return;
                        }
                    } else {
                        header('HTTP/1.0 500 Internal Server Error');
                        echo "Controller class {$controllerClass} not found";
                        return;
                    }
                } else {
                    header('HTTP/1.0 500 Internal Server Error');
                    echo "Controller file not found: {$controllerFile}";
                    return;
                }
            }
        }

        // If no route matched, show 404
        header('HTTP/1.0 404 Not Found');
        echo "404 - Page not found (URI: {$uri})";
    }
}