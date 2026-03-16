<?php
// ── Database credentials ───────────────────────────────────────────────────────
// Read from environment variables first (Apache SetEnv, system env, .htaccess).
// Falls back to development defaults when env vars are not set.
define('DB_HOST',    getenv('DB_HOST')    ?: '127.0.0.1');
define('DB_NAME',    getenv('DB_NAME')    ?: 'pju_sleman');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');
