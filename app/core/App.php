<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Model.php';

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

        spl_autoload_register(function ($class) {
            $controllerPath = __DIR__ . '/../controllers/' . $class . '.php';
            $modelPath      = __DIR__ . '/../models/' . $class . '.php';
            $middlewarePath = __DIR__ . '/../middlewares/' . $class . '.php';

            if (file_exists($controllerPath)) {
                require_once $controllerPath;
            } elseif (file_exists($modelPath)) {
                require_once $modelPath;
            } elseif (file_exists($middlewarePath)) {
                require_once $middlewarePath;
            }
        });
    }

    public function run()
    {
        $router = new Router();

        require_once __DIR__ . '/../../routes/web.php';

        $router->dispatch();
    }
}

