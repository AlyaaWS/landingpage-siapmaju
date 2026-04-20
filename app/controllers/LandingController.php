<?php

class LandingController extends Controller {
    
    public function index() {
        
        $dashboard = $this->model('DashboardModel');

        $kpi   = $dashboard->getPerbaikanMetrics();
        $chart = $dashboard->getAsetMetrics();

        $data = array_merge(
            [
                '_layout' => false,
                'judul'   => 'SIAP MAJU - Pengaduan PJU Sleman',
                'styles'  => ['css/landing.css'],
                'scripts' => [],
            ],
            $kpi,
            $chart
        );

        $this->view('landing/index', $data);
    }

    /**
     * Scan page - opens camera and shows scanner UI
     */
    public function scan()
    {
        $this->view('landing/scan', [
            '_layout' => false,
            'judul'   => 'Scan Barcode - SIAP MAJU',
            'styles'  => ['css/landing.css'],
            'scripts' => []
        ]);
    }

    /**
     * Manual PJU input page - alternative to barcode scan
     */
    public function inputPju()
    {
        $this->view('landing/input-pju', [
            '_layout' => false,
            'judul'   => 'Input Data PJU - SIAP MAJU',
            'styles'  => ['css/landing.css'],
            'scripts' => []
        ]);
    }

    /**
     * API: Look up PJU + KWH data by id_pju
     */
    public function apiLookupPju()
    {
        // Capture any stray PHP output (warnings, notices) so it doesn't
        // corrupt the JSON response.
        ob_start();

        header('Content-Type: application/json; charset=utf-8');

        try {
            $idPju = trim($_GET['id_pju'] ?? '');

            if ($idPju === '') {
                ob_end_clean();
                http_response_code(400);
                echo json_encode(['status' => false, 'message' => 'ID PJU harus diisi.']);
                return;
            }

            $dashboard = $this->model('DashboardModel');
            $result    = $dashboard->lookupPju($idPju);

            // Discard any buffered warnings/notices
            ob_end_clean();

            if (!$result) {
                http_response_code(404);
                echo json_encode(['status' => false, 'message' => 'Data PJU tidak ditemukan.']);
                return;
            }

            echo json_encode(['status' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode([
                'status'  => false,
                'message' => 'Terjadi kesalahan server. Silakan coba lagi nanti.'
            ]);
            error_log('apiLookupPju error: ' . $e->getMessage());
        }
    }
}