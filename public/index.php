<?php
// ==========================================
// ERROR LOGGING BOOTSTRAP
// ==========================================
define('LANDING_LOG', __DIR__ . '/landing_error.log');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', LANDING_LOG);

function _landing_log(string $prefix, string $message, string $file = '', int $line = 0): void
{
    $entry = sprintf(
        "[%s] %s: %s%s%s",
        date('Y-m-d H:i:s'),
        $prefix,
        $message,
        $file !== '' ? " in {$file}" : '',
        $line > 0   ? " on line {$line}" : ''
    ) . PHP_EOL;
    error_log($entry, 3, LANDING_LOG);
}

set_exception_handler(function (Throwable $e): void {
    _landing_log(
        get_class($e),
        $e->getMessage() . PHP_EOL . 'Stack trace:' . PHP_EOL . $e->getTraceAsString(),
        $e->getFile(),
        $e->getLine()
    );
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo 'A server error occurred. Please try again later.';
});

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    // Respect the @ error-suppression operator
    if (!(error_reporting() & $errno)) {
        return false;
    }
    $labels = [
        E_ERROR => 'E_ERROR', E_WARNING => 'E_WARNING', E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE', E_DEPRECATED => 'E_DEPRECATED',
        E_USER_ERROR => 'E_USER_ERROR', E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
    ];
    $label = $labels[$errno] ?? "E_UNKNOWN({$errno})";
    _landing_log($label, $errstr, $errfile, $errline);
    // For fatal-equivalent user errors, stop execution
    if (in_array($errno, [E_USER_ERROR], true)) {
        exit(1);
    }
    return true; // prevent PHP's built-in handler from running
});

register_shutdown_function(function (): void {
    $error = error_get_last();
    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if ($error !== null && in_array($error['type'], $fatalTypes, true)) {
        _landing_log('FATAL', $error['message'], $error['file'], $error['line']);
        if (!headers_sent()) {
            http_response_code(500);
        }
        echo 'A server error occurred. Please try again later.';
    }
});

// ==========================================
// APPLICATION BOOTSTRAP
// ==========================================

// Define the base path for the application
define('BASE_PATH', dirname(__DIR__));

// Define the path to the app directory
// Check if 'app' directory exists in BASE_PATH, if not, try current directory
$appPath = BASE_PATH . '/app/core/App.php';

if (!file_exists($appPath)) {
    // Fallback for different hosting structures
    $appPath = __DIR__ . '/app/core/App.php';
}

if (file_exists($appPath)) {
    require_once $appPath;
} else {
    die("Error: Core Application file not found at: " . $appPath);
}

$app = new App();
$app->run();
