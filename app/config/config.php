<?php
define('APP_NAME', 'LANDING PAGE SIAP MAJU');

// ── Environment ───────────────────────────────────────────────────────────────
// BEFORE UPLOADING TO PRODUCTION: change 'development' → 'production'
// This silences PHP error output to end-users (errors still go to server log).
define('APP_ENV', 'production'); // TODO: set to 'production' on live server

// ── Dynamic Base URL ───────────────────────────────────────────────────────
$http_host = $_SERVER['HTTP_HOST'] ?? '';

// Check for SSL from various server headers
$is_secure = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    ($_SERVER['SERVER_PORT'] == 443) ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
     $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
);

$protocol = $is_secure ? 'https' : 'http';

// ── Admin app local folder name ────────────────────────────────────────────
define('LOCAL_ADMIN_FOLDER', 'siap-maju');

// =======================
// Production
// =======================
if (strpos($http_host, 'pju.dishubsleman.id') !== false) {

    $url = 'https://pju.dishubsleman.id';
    $adminApiBase = 'https://adminpju.dishubsleman.id';

// =======================
// Development Kominfo
// =======================
} elseif (strpos($http_host, 'devlaporpju.slemankab.go.id') !== false) {

    $url = 'https://' . $http_host;
    $adminApiBase = 'https://devadminlaporpju.slemankab.go.id';

// =======================
// Local Development
// =======================
} else {

    $scriptFolder = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

    $url = $protocol . '://' . $http_host . $scriptFolder;

    $adminApiBase = $protocol . '://' . $http_host . '/'
        . LOCAL_ADMIN_FOLDER . '/public';
}

define('BASEURL', rtrim($url, '/'));

// Definisikan Konstanta URL
if (!defined('APP_URL')) {
    define('APP_URL', $url);
}

if (!defined('BASEURL')) {
    define('BASEURL', rtrim($url, '/'));
}

if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim($url, '/'));
}

if (!defined('ADMIN_API_BASE')) {
    define('ADMIN_API_BASE', rtrim($adminApiBase, '/'));
}