<?php

class Router
{
    private array $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    private array $middlewareMap = [
        'auth'  => AuthMiddleware::class,
        'guest' => AuthMiddleware::class,
    ];

    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->routes['GET'][$this->normalize($path)] = [
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->routes['POST'][$this->normalize($path)] = [
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!isset($this->routes[$method])) {
            http_response_code(405);
            echo "405 - Method not allowed";
            return;
        }

        $uriPath  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $script   = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = rtrim(dirname($script), '/\\'); // contoh: /siap-maju/public

        if ($basePath && $basePath !== '/' && str_starts_with($uriPath, $basePath)) {
            $uriPath = substr($uriPath, strlen($basePath));
        }

        if (str_starts_with($uriPath, '/index.php')) {
            $uriPath = substr($uriPath, strlen('/index.php'));
        }

        $path = $this->normalize($uriPath);

        $route = $this->routes[$method][$path] ?? null;

        if (!$route) {
            http_response_code(404);
            echo "404 - Route not found: " . htmlspecialchars($path);
            return;
        }

        $handler    = $route['handler'] ?? null;
        $middleware = $route['middleware'] ?? [];

        $this->runMiddlewares($middleware);

        if (is_callable($handler)) {
            call_user_func($handler);
            return;
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$controller, $action] = explode('@', $handler, 2);

            $this->loadControllerIfNeeded($controller);

            if (!class_exists($controller)) {
                http_response_code(500);
                echo "Controller tidak ditemukan: " . htmlspecialchars($controller);
                return;
            }

            $obj = new $controller();

            if (!method_exists($obj, $action)) {
                http_response_code(500);
                echo "Method tidak ditemukan: " . htmlspecialchars($controller . '@' . $action);
                return;
            }

            $obj->$action();
            return;
        }

        http_response_code(500);
        echo "Handler route tidak valid.";
    }

    private function runMiddlewares(array $middlewares): void
    {
        foreach ($middlewares as $mw) {
            if (is_string($mw) && isset($this->middlewareMap[$mw])) {
                $mwClass = $this->middlewareMap[$mw];

                if ($mw === 'guest' && method_exists($mwClass, 'guestOnly')) {
                    $mwClass::guestOnly('home');
                    continue;
                }

                if (method_exists($mwClass, 'handle')) {
                    $mwClass::handle();
                    continue;
                }

                http_response_code(500);
                echo "Middleware class tidak punya method yang dibutuhkan.";
                exit;
            }

            if (is_string($mw) && class_exists($mw) && method_exists($mw, 'handle')) {
                $mw::handle();
                continue;
            }

            if (is_callable($mw)) {
                call_user_func($mw);
                continue;
            }

            http_response_code(500);
            echo "Middleware tidak valid.";
            exit;
        }
    }

    private function normalize(string $path): string
    {
        $path = $path ?: '/';
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }

    private function loadControllerIfNeeded(string $controller): void
    {
        if (class_exists($controller)) return;

        $file = __DIR__ . '/../controllers/' . $controller . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}