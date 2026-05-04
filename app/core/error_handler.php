<?php

if (!function_exists('landing_error_view_path')) {
    function landing_error_view_path(int $statusCode): ?string
    {
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $candidates = [
            $basePath . '/app/views/errors/' . $statusCode . '.php',
            $basePath . '/app/views/landing/errors/' . $statusCode . '.php',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    function render_error_page(int $statusCode): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $viewPath = landing_error_view_path($statusCode);

        if ($viewPath !== null) {
            require $viewPath;
            return;
        }

        echo $statusCode === 404
            ? '404 - Halaman Tidak Ditemukan'
            : '500 - Terjadi Kesalahan Sistem';
    }

    function handle_uncaught_exception(Throwable $exception): void
    {
        error_log(sprintf(
            '[%s] %s: %s in %s on line %d%s%s',
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            PHP_EOL,
            $exception->getTraceAsString()
        ));

        render_error_page(500);
    }

    function handle_php_error(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    function handle_fatal_error(): void
    {
        $error = error_get_last();
        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

        if ($error === null || !in_array($error['type'], $fatalTypes, true)) {
            return;
        }

        handle_uncaught_exception(
            new ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            )
        );
    }

    function register_global_error_handlers(): void
    {
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        ini_set('log_errors', '1');
        error_reporting(E_ALL);

        set_exception_handler('handle_uncaught_exception');
        set_error_handler('handle_php_error');
        register_shutdown_function('handle_fatal_error');
    }
}