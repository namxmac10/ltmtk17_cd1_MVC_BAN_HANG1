<?php
namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $pattern, array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, array $handler): void
    {
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[0-9]+)', $pattern);
        $this->routes[] = [$method, '~^' . $regex . '$~D', $handler];
    }

    public function dispatch(string $method, string $path): void
    {
        if ($method === 'POST') {
            Csrf::verify((string) ($_POST['_csrf'] ?? ''));
        }
        foreach ($this->routes as [$expectedMethod, $pattern, $handler]) {
            if ($expectedMethod !== $method || !preg_match($pattern, $path, $matches)) {
                continue;
            }
            $args = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $args[] = (int) $value;
                }
            }
            [$class, $action] = $handler;
            (new $class())->$action(...$args);
            return;
        }
        abort(404);
    }
}
