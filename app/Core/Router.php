<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<string, array<string, array{handler: array{class-string, string}, middleware: string[]}>> */
    private array $routes = ['GET' => [], 'POST' => []];

    /** @param array{class-string, string} $handler */
    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->routes['GET'][$path] = ['handler' => $handler, 'middleware' => $middleware];
    }

    /** @param array{class-string, string} $handler */
    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->routes['POST'][$path] = ['handler' => $handler, 'middleware' => $middleware];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/', '/') ?: '/';

        foreach ($this->routes[$method] ?? [] as $path => $route) {
            $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path);
            if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                foreach ($route['middleware'] as $mw) {
                    $this->runMiddleware($mw);
                }

                if ($method === 'POST') {
                    Csrf::verify();
                }

                [$class, $action] = $route['handler'];
                $controller = new $class();
                $controller->$action(...array_values($params));
                return;
            }
        }

        http_response_code(404);
        $c = new \App\Core\Controller();
        $c->render('errors/404', ['title' => '404'], Auth::check() ? 'layouts/app' : 'layouts/guest');
    }

    private function runMiddleware(string $mw): void
    {
        if ($mw === 'auth') {
            if (!Auth::check()) {
                redirect('/login');
            }
            return;
        }
        if (str_starts_with($mw, 'role:')) {
            $allowed = explode(',', substr($mw, 5));
            if (!Auth::check() || !in_array(Auth::user()['role'], $allowed, true)) {
                http_response_code(403);
                $c = new \App\Core\Controller();
                $c->render('errors/403', ['title' => '403'], Auth::check() ? 'layouts/app' : 'layouts/guest');
                exit;
            }
        }
    }
}
