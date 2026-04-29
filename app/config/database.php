<?php
// ── Database credentials ───────────────────────────────────────────────────────
// Read from environment variables first (Apache SetEnv, system env, .htaccess).
// Falls back to development defaults when env vars are not set.
define('DB_HOST',    getenv('DB_HOST')    ?: '194.59.164.30');
define('DB_NAME',    getenv('DB_NAME')    ?: 'u385216173_pjusleman');
define('DB_USER',    getenv('DB_USER')    ?: 'u385216173_pjusleman2026');
define('DB_PASS',    getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'pjUUSleman2026$#');
define('DB_CHARSET', 'utf8mb4');
