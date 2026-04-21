<?php
define('APP_NAME', 'LANDING PAGE SIAP MAJU');

// ── Environment ───────────────────────────────────────────────────────────────
// BEFORE UPLOADING TO PRODUCTION: change 'development' → 'production'
// This silences PHP error output to end-users (errors still go to server log).
define('APP_ENV', 'production'); // TODO: set to 'production' on live server

// ── Dynamic Base URL ───────────────────────────────────────────────────────
$http_host = $_SERVER['HTTP_HOST'] ?? '';

// Check for SSL from various server headers
$is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
              ($_SERVER['SERVER_PORT'] == 443) || 
              (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

$protocol = $is_secure ? 'https' : 'http';

if (strpos($http_host, 'pju.dishubsleman.id') !== false) {
    // PRODUCTION URL (Direct domain, no subfolders)
    $url = 'https://pju.dishubsleman.id';
    $adminApiBase = 'https://adminpju.dishubsleman.id';
} else {
    // LOCAL / NGROK URL (With subfolders)
    $url = $protocol . '://' . $http_host . '/landingpage-siapmaju/public';
    $adminApiBase = $protocol . '://' . $http_host . '/siap-maju/public';
}

define('BASEURL', rtrim($url, '/'));

// Definisikan Konstanta URL
if (!defined('APP_URL')) { define('APP_URL', $url); }
if (!defined('BASEURL')) { define('BASEURL', $url); }
if (!defined('BASE_URL')) { define('BASE_URL', $url); }

// Admin API base URL for server-side proxy calls
if (!defined('ADMIN_API_BASE')) { define('ADMIN_API_BASE', $adminApiBase); }
