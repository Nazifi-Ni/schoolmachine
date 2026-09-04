<?php

namespace App\Helpers;

class Router
{
    private $routes = [];

    public function add($method, $route, $controller, $action)
    {
        // Convert route variables like {id} into regex capturing groups
        $routeRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $route);
        // Replace forward slashes so regex works correctly
        $routeRegex = str_replace('/', '\/', $routeRegex);
        $routeRegex = '/^' . $routeRegex . '$/';
        
        $this->routes[] = [
            'method' => $method,
            'route' => $route,
            'routeRegex' => $routeRegex,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function get($route, $controller, $action)
    {
        $this->add('GET', $route, $controller, $action);
    }

    public function post($route, $controller, $action)
    {
        $this->add('POST', $route, $controller, $action);
    }

    public function put($route, $controller, $action)
    {
        $this->add('PUT', $route, $controller, $action);
    }

    public function delete($route, $controller, $action)
    {
        $this->add('DELETE', $route, $controller, $action);
    }

    public function dispatch($uri, $requestMethod)
    {
        // Add CORS headers for API (React frontend)
        header('Access-Control-Allow-Origin: http://localhost:5173'); // Vite default port
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');

        if ($requestMethod === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        // Remove query string from URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        // Handle subdirectories if the app isn't at the domain root
        // Adjust this depending on the server setup, for now we assume it's running on localhost
        // For example if running at http://localhost/school/, we might need to trim '/school'
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/') {
            $uri = str_replace($scriptName, '', $uri);
        }
        
        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['routeRegex'], $uri, $matches)) {
                
                // Extract parameters to pass to the controller action
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }

                $controllerClass = "App\\Controllers\\" . $route['controller'];
                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass();
                    if (method_exists($controllerInstance, $route['action'])) {
                        // Call the action with extracted parameters
                        call_user_func_array([$controllerInstance, $route['action']], $params);
                        return;
                    }
                }
            }
        }

        // Handle 404
        http_response_code(404);
        echo "404 Not Found - $uri";
    }
}
