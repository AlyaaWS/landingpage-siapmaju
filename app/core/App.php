<?php

// 1. Conditionally load Composer Autoloader (For Local Development)
$composerAutoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// 2. Native Custom Autoloader (For Production without Composer)
spl_autoload_register(function ($className) {
    // Array of directories where MVC classes might be located
    $directories = [
        __DIR__ . '/',                  // app/core/
        __DIR__ . '/../controllers/',   // app/controllers/
        __DIR__ . '/../models/'         // app/models/
    ];

    // Loop through directories to find and load the requested class
    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

class App
{
    public function __construct()
    {
        // Load application config (APP_ENV, APP_NAME, etc.) early so all
        // subsequent code can rely on the constants being defined.
        require_once __DIR__ . '/../config/config.php';

        // ── Error reporting ───────────────────────────────────────────────────
        // Production: define APP_ENV = 'production' in config.php to silence
        // all error output to end-users. Errors are still written to the
        // server log via error_log() so nothing is lost.
        // Development (default): display all errors as normal.
        if (defined('APP_ENV') && APP_ENV === 'production') {
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        } else {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        }

        if (session_status() === PHP_SESSION_NONE) {
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                     || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isSecure,
                'httponly'  => true,
                'samesite'  => 'Lax',
            ]);

            session_start();
        }

        require_once __DIR__ . '/../helpers/url.php';
        require_once __DIR__ . '/../helpers/csrf.php';
    }

    public function run()
    {
        $router = new Router();

        require_once __DIR__ . '/../../routes/web.php';

        $router->dispatch();
    }
}

