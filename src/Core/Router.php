<?php

namespace App\Core;

class Router {
    private $routes = [];

    public function add($route, $method, $handler) {
        $routePattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $route);
        $this->routes[] = [$routePattern, $method, $handler];
    }

    public function dispatch() {
        $url = trim($_SERVER['REQUEST_URI'], '/');
        $method = $_SERVER['REQUEST_METHOD'];
        
        foreach ($this->routes as $route) {
            list($routePattern, $routeMethod, $handler) = $route;
            if (preg_match("#^$routePattern$#", $url, $matches) && $routeMethod === $method) {
                array_shift($matches);
                $controller = new $handler[0];
                return call_user_func_array([$controller, $handler[1]], $matches);
            }
        }

        http_response_code(404);
        include __DIR__ . '/../Views/pages/404.php';
        exit();
    }
}

