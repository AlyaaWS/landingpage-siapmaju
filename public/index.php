<?php

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/app/core/error_handler.php';

register_global_error_handlers();

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
