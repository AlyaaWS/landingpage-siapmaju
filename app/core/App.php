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

        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        ini_set('log_errors', '1');
        error_reporting(E_ALL);

        if (session_status() === PHP_SESSION_NONE) {
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                     || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                     || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

            // Code-only approach fallback: If Nginx doesn't pass HTTPS headers,
            // we rely on the application config which defines APP_URL with https:// for production.
            if (!$isSecure && defined('APP_URL') && strpos(APP_URL, 'https://') === 0) {
                $isSecure = true;
            }

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

        // ── Security Headers (HIGH-01) ───────────────────────────────────
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
        
        if (!empty($isSecure)) {
            header("Strict-Transport-Security: max-age=31536000");
        }
        
        header("X-Debug-APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT_DEFINED'));
        header("X-Debug-isSecure: " . (!empty($isSecure) ? 'TRUE' : 'FALSE'));
        header("X-Debug-HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NONE'));

        // Tighter CSP without unsafe-eval
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://devlaporpju.slemankab.go.id https://pju.dishubsleman.id https://devadminlaporpju.slemankab.go.id https://adminpju.dishubsleman.id;");

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
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            $allowedOrigins = [
                defined('APP_URL') ? APP_URL : '',
                defined('ADMIN_API_BASE') ? ADMIN_API_BASE : '',
                'https://devlaporpju.slemankab.go.id',
                'https://pju.dishubsleman.id',
                'https://devadminlaporpju.slemankab.go.id',
                'https://adminpju.dishubsleman.id'
            ];
            
            // Allow local dev origins
            if (strpos($origin, 'http://localhost') === 0 || strpos($origin, 'http://127.0.0.1') === 0) {
                $allowedOrigins[] = $origin;
            }

            if (!empty($origin) && in_array($origin, $allowedOrigins)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Credentials: true');
                header('Vary: Origin');
            }
            
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Accept, Accept-Encoding, Accept-Language, Cache-Control, Connection, Content-Length, Host, Origin, Pragma, Referer, X-Requested-With, Request-Method, Request-From, Request-Type, Request-Id, Request-Index, User-Agent, Caller-Id, Request-Method-Index, Request-Session-Index, Request-Session, Request-Session-Type, Akses-Agent, Akses-Ip');

            // Handle preflight OPTIONS requests
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(204);
                return;
            }

            require_once __DIR__ . '/../core/Router.php';
            $router = new Router();
            $routeFile = dirname(dirname(__DIR__)) . '/routes/web.php';
            if (!file_exists($routeFile)) {
                throw new \RuntimeException("Route configuration file not found at: " . $routeFile);
            }
            require_once $routeFile;
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
        render_error_page(404);
    }
}

