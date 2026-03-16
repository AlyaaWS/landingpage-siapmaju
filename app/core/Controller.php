<?php

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $viewFile   = __DIR__ . '/../views/' . $view . '.php';
        $layoutFile = __DIR__ . '/../views/layouts/app.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "View tidak ditemukan: " . htmlspecialchars($view);
            return;
        }

        $useLayout = $data['_layout'] ?? true;

        if ($useLayout && file_exists($layoutFile)) {
            // Layout biasanya akan me-render $viewFile di dalamnya
            require $layoutFile;
        } else {
            require $viewFile;
        }
    }

    /**
     * Load model dari app/models/<Model>.php dan return instance-nya.
     * Contoh: $user = $this->model('User');
     */
    protected function model(string $model)
    {
        $model = trim($model);

        // basic security: cegah path traversal
        if ($model === '' || str_contains($model, '/') || str_contains($model, '\\') || str_contains($model, '..')) {
            throw new Exception("Nama model tidak valid: {$model}");
        }

        $modelFile = __DIR__ . '/../models/' . $model . '.php';

        if (!file_exists($modelFile)) {
            throw new Exception("File model tidak ditemukan: {$modelFile}");
        }

        require_once $modelFile;

        if (!class_exists($model)) {
            throw new Exception("Class model '{$model}' tidak ditemukan di file {$modelFile}");
        }

        return new $model();
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . base_url(ltrim($path, '/')));
        exit;
    }

    /**
     * Resolve the absolute filesystem path to an img sub-directory.
     *
     * Delegates to the global get_img_path() helper (app/helpers/url.php).
     * ROOT_DIR is the web-root on both environments, so images are always
     * at ROOT_DIR/img/<subfolder>/ — no environment detection needed here.
     *
     * @param  string $subfolder  e.g. 'pju' or 'kwh'  (no leading slash)
     * @return string             Absolute path, no trailing slash
     */
    protected function resolveImgDir(string $subfolder = ''): string
    {
        return get_img_path($subfolder);
    }
}