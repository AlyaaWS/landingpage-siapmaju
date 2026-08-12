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
            // Force Secure Cookie, kecuali untuk lingkungan pengembangan lokal (HTTP)
            $isSecure = true;
            if (isset($_SERVER['SERVER_NAME']) && in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1'])) {
                $isSecure = false;
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

        // ── Security Headers untuk Seluruh Respons (HTML & API) ──────────────
        if (!headers_sent()) {
            header("X-Frame-Options: DENY"); // Memblokir embedding via iframe
            header("X-Content-Type-Options: nosniff");
            header("Referrer-Policy: strict-origin-when-cross-origin");
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
            header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
            // CSP Permissive: mengizinkan inline scripts/styles dan evaluasi, tetapi mencegah injeksi dari domain tidak dikenal.
            header("Content-Security-Policy: default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval'; frame-ancestors 'none'");
            
            // Mencoba menghapus identitas server bawaan PHP
            header_remove('X-Powered-By');
            header_remove('Server');
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
            $allowed_origins = [
                'http://pju.dishubsleman.id',
                'https://pju.dishubsleman.id',
                'http://adminpju.dishubsleman.id',
                'https://adminpju.dishubsleman.id',
                'http://devlaporpju.slemankab.go.id',
                'https://devlaporpju.slemankab.go.id',
                'http://devadminlaporpju.slemankab.go.id',
                'https://devadminlaporpju.slemankab.go.id',
                'http://localhost',
                'http://127.0.0.1'
            ];
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            
            if (in_array($origin, $allowed_origins, true)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Credentials: true');
                header('Vary: Origin');
            }
            
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

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
                $potentialMethodRaw = $url[1] ?? ($url[0] ?? null);
                
                // Convert dash format to camelCase for method checking (e.g. input-pju -> inputPju)
                $potentialMethod = $potentialMethodRaw ? lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $potentialMethodRaw)))) : null;

                if ($potentialMethod && method_exists($controllerInstance, $potentialMethod)) {
                    $methodName = $potentialMethod;
                    // Bersihkan array parameter
                    if (isset($url[1])) { unset($url[1]); }
                    elseif (isset($url[0])) { unset($url[0]); }
                } else {
                    // Jika method tidak ditemukan, batasi SPA fallback hanya untuk rute yang dikenali.
                    // Jika ada segmen URL tapi tidak valid, lemparkan 404.
                    if (isset($url[0]) && $url[0] !== '') {
                        $validSpaRoutes = ['scan', 'input-pju']; // Daftar rute SPA yang diizinkan (dari web.php)
                        if ($controllerName !== 'Landing' || !in_array(strtolower($url[0]), $validSpaRoutes)) {
                            render_error_page(404);
                            return;
                        }
                    }
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

