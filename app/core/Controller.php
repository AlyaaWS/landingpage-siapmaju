<?php

class Controller
{
    public function view($view, $data = []) {
        // Path to the views folder
        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (file_exists($viewFile)) {
            // CRITICAL FIX: Extract array keys into variables for the view
            extract($data);
            require_once $viewFile;
        } else {
            // Stop execution and show EXACTLY what file Linux is looking for
            die("<div style='color:red; font-family:sans-serif; padding:20px; border:1px solid red;'>"
                . "<h3>View File Not Found!</h3>"
                . "<p>Linux is strictly looking for exactly: <strong>" . htmlspecialchars($viewFile) . "</strong></p>"
                . "<p>Please check your <code>app/views/</code> folder and ensure the folder and file names match the uppercase/lowercase letters perfectly.</p>"
                . "</div>");
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