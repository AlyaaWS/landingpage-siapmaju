<?php
define('APP_NAME', 'LANDING PAGE SIAP MAJU');

// ── Environment ───────────────────────────────────────────────────────────────
// BEFORE UPLOADING TO PRODUCTION: change 'development' → 'production'
// This silences PHP error output to end-users (errors still go to server log).
define('APP_ENV', 'production'); // TODO: set to 'production' on live server

// ── Dynamic Base URL ───────────────────────────────────────────────────────
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Deteksi HTTPS tingkat lanjut (Paksa HTTPS jika menggunakan Ngrok)
$is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || 
            (strpos($host, 'ngrok') !== false); // <- Paksa HTTPS untuk ngrok

$protocol = $is_https ? 'https' : 'http';

// Tentukan URL berdasarkan environment
if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false || strpos($host, 'ngrok') !== false) {
    // PASTIKAN nama folder ini sesuai dengan folder project Anda di Laragon
    $url = $protocol . '://' . $host . '/landingpage-siapmaju/public';
} else {
    // URL Production
    $url = 'https://adminpju.dishubsleman.id'; 
}

// Definisikan Konstanta URL
if (!defined('APP_URL')) { define('APP_URL', $url); }
if (!defined('BASEURL')) { define('BASEURL', $url); }
if (!defined('BASE_URL')) { define('BASE_URL', $url); }
