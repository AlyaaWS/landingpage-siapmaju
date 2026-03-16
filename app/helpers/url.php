<?php

/**
 * Return the absolute base URL of the application.
 *
 * Resolution order (first non-empty value wins):
 *  1. APP_URL constant — define this in config/config.php for production.
 *     e.g.:  define('APP_URL', 'https://app.yourdomain.com');
 *     This is the ONLY thing you need to change when going live.
 *
 *  2. Dynamic detection — used on localhost / Ngrok / staging automatically:
 *     - Protocol : HTTP_X_FORWARDED_PROTO (Ngrok, load-balancers, CDN)
 *                  then HTTPS server var, then fall back to http.
 *     - Host     : HTTP_X_FORWARDED_HOST (Ngrok tunnels change the host)
 *                  then HTTP_HOST, then SERVER_NAME.
 *     - Sub-path : dirname(SCRIPT_NAME) gives the subfolder the app lives in
 *                  (e.g. /pju on localhost).  Empty / "/" → no subfolder.
 *
 * Double-subfolder prevention
 * ---------------------------
 * Always pass ONLY the route path to base_url(), never include the subfolder:
 *   CORRECT : base_url('pju/detail?id=1')   → https://host/pju/pju/detail?id=1  ✗ — wait
 *   The subfolder (/pju) comes from dirname(SCRIPT_NAME), NOT from the path arg.
 *   So routes registered as /pju/detail must be passed as 'pju/detail', and the
 *   resulting URL is https://host/<subfolder>/pju/detail — which is correct.
 *
 * @param  string $path  Route path WITHOUT leading slash, e.g. 'pju/detail?id=1'
 * @return string        Fully-qualified URL, no trailing slash.
 */
function base_url(string $path = ''): string
{
    // ── 1. Hard-coded override (production / staging) ─────────────────────
    if (defined('APP_URL') && APP_URL !== '') {
        return rtrim(APP_URL, '/') . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }

    // ── 2. Protocol ───────────────────────────────────────────────────────
    // HTTP_X_FORWARDED_PROTO is set by Ngrok, reverse-proxies, and most CDNs.
    $proto = '';
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        // Header may contain a comma-separated list; take the first value.
        $proto = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]));
    }
    if ($proto !== 'https' && $proto !== 'http') {
        // Fall back to the standard HTTPS detection
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                 ? 'https'
                 : 'http';
    }

    // ── 3. Host ───────────────────────────────────────────────────────────
    // HTTP_X_FORWARDED_HOST carries the public hostname when behind a proxy.
    // Ngrok sets this to <random>.ngrok-free.app, so we must prefer it.
    $host = '';
    if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        // May be comma-separated; take the first (client-facing) host.
        $host = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])[0]);
    }
    if ($host === '') {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    }

    // ── 4. Sub-path (subfolder the app lives in) ──────────────────────────
    // SCRIPT_NAME for a Laragon subfolder install is e.g. /pju/index.php
    // dirname gives /pju.  For a docroot install it gives / which we discard.
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath   = rtrim(dirname($scriptName), '/\\');
    // Normalise: if empty or just "/", there is no subfolder.
    if ($basePath === '/' || $basePath === '\\') {
        $basePath = '';
    }

    // ── 5. Assemble ───────────────────────────────────────────────────────
    $path = ltrim($path, '/');
    return $proto . '://' . $host . $basePath . ($path !== '' ? '/' . $path : '');
}

function asset_url($path)
{
    $url = base_url('assets/' . ltrim($path, '/'));
    $file = (defined('ROOT_DIR') ? ROOT_DIR : __DIR__ . '/../../public') . '/assets/' . ltrim($path, '/');
    if (is_file($file)) {
        $url .= '?v=' . filemtime($file);
    }
    return $url;
}

/**
 * Absolute filesystem path to an image file (or sub-directory).
 *
 * ROOT_DIR is always the web-root — the directory that contains index.php:
 *   Local dev  (Laragon) : …/lpju-sleman-test/public
 *   Production (Hostinger): …/public_html
 *
 * Images therefore always live at ROOT_DIR/img/<subfolder>/<file> on
 * both environments, with no extra logic needed.
 *
 * @param  string $subfolder  e.g. 'perbaikan', 'pju', 'kwh'  (no slashes needed)
 * @param  string $filename   optional filename — when given, path includes the file
 * @return string             Absolute path, no trailing slash
 */
function get_img_path(string $subfolder, string $filename = ''): string
{
    $base = ROOT_DIR . '/img/' . trim($subfolder, '/');
    return $filename !== '' ? $base . '/' . ltrim($filename, '/') : $base;
}

/**
 * Fully-qualified URL to an image file (or sub-directory).
 *
 * Uses base_url() so it automatically picks up APP_URL on production and
 * the dynamic detector (Ngrok / localhost subfolder) in development.
 *
 * @param  string $subfolder  e.g. 'perbaikan', 'pju', 'kwh'
 * @param  string $filename   optional filename (automatically URL-encoded)
 * @return string             Fully-qualified URL
 */
function get_img_url(string $subfolder, string $filename = ''): string
{
    $path = 'img/' . trim($subfolder, '/');
    if ($filename !== '') {
        $path .= '/' . rawurlencode(ltrim($filename, '/'));
    }
    return base_url($path);
}

function request_path(): string
{
    $uriPath  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    if ($basePath && $basePath !== '/' && strpos($uriPath, $basePath) === 0) {
        $uriPath = substr($uriPath, strlen($basePath));
    }

    if (strpos($uriPath, '/index.php') === 0) {
        $uriPath = substr($uriPath, strlen('/index.php'));
    }

    $uriPath = '/' . trim($uriPath, '/');
    return $uriPath === '//' ? '/' : $uriPath;
}

