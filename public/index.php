<?php
// Define the base path for the application
define('BASE_PATH', dirname(__DIR__)); 

// Enable error reporting for debugging production deployment
ini_set('display_errors', '1');
error_reporting(E_ALL);

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
