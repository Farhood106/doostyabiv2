<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void { $this->routes['GET'][$path] = $handler; }
    public function post(string $path, array $handler): void { $this->routes['POST'][$path] = $handler; }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        $matched = $this->matchRoute($method, $path);
        if (!$matched) {
            http_response_code(404);
            echo 'Page not found';
            return;
        }

        [$handler, $params] = $matched;
        [$class, $action] = $handler;
        (new $class())->$action(...$params);
    }

    private function matchRoute(string $method, string $path): ?array
    {
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([0-9]+)', $route);
            if (!$pattern) {
                continue;
            }
            if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                array_shift($matches);
                return [$handler, array_map('intval', $matches)];
            }
        }

        return null;
    }
}
