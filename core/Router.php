<?php

class Router
{
    private $routes = [];
    private $notFound;

    public function get($pattern, $callback)
    {
        $this->routes['GET'][$pattern] = $callback;
    }

    public function post($pattern, $callback)
    {
        $this->routes['POST'][$pattern] = $callback;
    }

    public function notFound($callback)
    {
        $this->notFound = $callback;
    }

    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Remove base path and query string
        $config = require __DIR__ . '/../config/config.php';
        $basePath = $config['base_path'];

        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = strtok($uri, '?');
        $uri = $uri ?: '/';

        if (!isset($this->routes[$method])) {
            $this->handleNotFound();
            return;
        }

        foreach ($this->routes[$method] as $pattern => $callback) {
            $params = $this->match($pattern, $uri);
            if ($params !== false) {
                call_user_func_array($callback, $params);
                return;
            }
        }

        $this->handleNotFound();
    }

    private function match($pattern, $uri)
    {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            // Extract named parameters
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return array_values($params);
        }

        return false;
    }

    private function handleNotFound()
    {
        http_response_code(404);
        if ($this->notFound) {
            call_user_func($this->notFound);
        } else {
            echo "404 - Page Not Found";
        }
    }
}
