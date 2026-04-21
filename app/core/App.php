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

    private function parseUrl()
    {
        $url = $_GET['url'] ?? '';
        $url = filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL);
        return explode('/', $url);
    }

   public function run()
    {
        $url = $this->parseUrl();

        // ── API routes: delegate to Router (routes/web.php) ──────────────
        // URL segments starting with "api" are handled by the explicit Router
        // so we can use clean route definitions like /api/lookup-pju.
        $rawUrl = $_GET['url'] ?? '';
        if (str_starts_with(trim($rawUrl, '/'), 'api')) {
            // ── CORS headers for all API responses ───────────────────────
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');

            // Handle preflight OPTIONS requests
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(204);
                return;
            }

            require_once __DIR__ . '/../core/Router.php';
            $router = new Router();
            require_once BASE_PATH . '/routes/web.php';
            $router->dispatch();
            return;
        }

        // 1. Set Default Controller & Method
        $controllerName = 'Landing'; // <-- Controller utama jika URL kosong
        $methodName = 'index';       // <-- Method utama jika tidak disebutkan

        // 2. Cek apakah index [0] dari URL adalah Controller yang valid
        if (isset($url[0])) {
            $potentialController = ucfirst(strtolower($url[0])); // Standar Linux (Huruf depan besar)
            if (file_exists(__DIR__ . '/../controllers/' . $potentialController . 'Controller.php')) {
                $controllerName = $potentialController;
                unset($url[0]); // Hapus dari array URL jika cocok sebagai Controller
            }
        }

        // 3. Muat file Controller
        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . 'Controller.php';
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerClass = $controllerName . 'Controller';

            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();

                // 4. Cari Method yang diminta (bisa di $url[1] atau $url[0] jika Controller pakai Default)
                // Ini penting agar URL "/scan" bisa masuk ke LandingController->scan()
                $potentialMethod = $url[1] ?? ($url[0] ?? null);

                if ($potentialMethod && method_exists($controllerInstance, $potentialMethod)) {
                    $methodName = $potentialMethod;
                    // Bersihkan array parameter
                    if (isset($url[1])) { unset($url[1]); }
                    elseif (isset($url[0])) { unset($url[0]); }
                }

                // 5. Jalankan Class dan Method beserta sisa parameternya
                $params = $url ? array_values($url) : [];
                call_user_func_array([$controllerInstance, $methodName], $params);
                return; // Selesai
            }
        }

        // 6. Jika tidak ada yang cocok, baru tampilkan 404
        http_response_code(404);
        echo "404 - Halaman Tidak Ditemukan. Pastikan nama Controller sesuai dengan URL.";
    }
}

