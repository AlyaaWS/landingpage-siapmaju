<?php
// Front controller for the public-facing landing page.
define('ROOT_DIR', __DIR__);

// Enable errors in development to diagnose 500 responses.
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Boot the app
require_once __DIR__ . '/../app/core/App.php';

$app = new App();
$app->run();
