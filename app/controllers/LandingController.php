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

    /**
     * API Proxy: Forward cek-status request to the Admin API
     * This avoids CORS issues by making the cross-origin call server-side.
     */
    public function apiCekStatus()
    {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $nomorTiket = trim($_GET['nomor_tiket'] ?? '');

            if ($nomorTiket === '') {
                ob_end_clean();
                http_response_code(400);
                echo json_encode(['status' => false, 'message' => 'Nomor tiket harus diisi.']);
                return;
            }

            $adminUrl = ADMIN_API_BASE . '/api/cek-status?nomor_tiket=' . urlencode($nomorTiket);

            error_log('[apiCekStatus] Proxying to: ' . $adminUrl);
            error_log('[apiCekStatus] Request from: ' . ($_SERVER['HTTP_REFERER'] ?? $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown')));

            $ch = curl_init($adminUrl);
            $isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true)
                    || str_starts_with($_SERVER['HTTP_HOST'] ?? '', '192.168');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => !$isLocal,
                CURLOPT_SSL_VERIFYHOST => $isLocal ? 0 : 2,
                CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            ob_end_clean();

            if ($response === false) {
                error_log('[apiCekStatus] cURL failed: ' . $curlErr);
                http_response_code(502);
                echo json_encode(['status' => false, 'message' => 'Gagal menghubungi server admin: ' . $curlErr]);
                return;
            }

            // Validate admin response is JSON before passing through
            $decoded = json_decode($response, true);
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                error_log('[apiCekStatus] Admin returned non-JSON (HTTP ' . $httpCode . '): ' . substr($response, 0, 200));
                http_response_code(502);
                echo json_encode(['status' => false, 'message' => 'Server admin mengembalikan respons yang tidak valid.']);
                return;
            }

            // Enrich response with server-calculated duration
            if (!empty($decoded['status']) && !empty($decoded['data'])) {
                $decoded['data'] = $this->enrichWithDuration($decoded['data']);
            }

            http_response_code($httpCode);
            echo json_encode($decoded);
        } catch (\Throwable $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['status' => false, 'message' => 'Terjadi kesalahan server.']);
            error_log('apiCekStatus error: ' . $e->getMessage());
        }
    }

    /**
     * Calculate work duration from report date to completion date.
     * Queries tanggal_perbaikan directly from DB since admin API doesn't return it.
     */
    private function enrichWithDuration(array $data): array
    {
        $nomorTiket    = $data['nomor_tiket'] ?? null;
        $tanggalLapor  = $data['tanggal'] ?? null;
        $status        = strtolower($data['status_perbaikan'] ?? '');

        if (!$nomorTiket) {
            $data['duration']    = null;
            $data['durasi_ongoing'] = false;
            $data['sla_duration'] = null;
            return $data;
        }

        // Fetch scheduling data (report/scheduled/actual) from our own DB
        $scheduling = ['report_date' => null, 'scheduled_date' => null, 'actual_date' => null];
        try {
            $dashboard = $this->model('DashboardModel');
            $scheduling = $dashboard->getSchedulingData($nomorTiket);
        } catch (\Throwable $e) {
            error_log('[enrichWithDuration] scheduling DB error: ' . $e->getMessage());
        }

        // Expose raw scheduling fields for frontend / diagnostics
        $data['report_date'] = $scheduling['report_date'] ?? null;
        $data['scheduled_date'] = $scheduling['scheduled_date'] ?? null;
        $data['actual_date'] = $scheduling['actual_date'] ?? null;

        // Calculate SLA duration: scheduled_date - actual_date
        $data['sla_duration'] = null;
        if (!empty($scheduling['scheduled_date']) && !empty($scheduling['actual_date'])) {
            try {
                $start = new \DateTime($scheduling['actual_date']);
                $end = new \DateTime($scheduling['scheduled_date']);
                $diff = $start->diff($end);
                $days = (int) $diff->days;
                $hours = $diff->h;
                $mins = $diff->i;

                $parts = [];
                if ($days > 0)                $parts[] = $days . ' hari';
                if ($hours > 0)               $parts[] = $hours . ' jam';
                if ($days === 0 && $mins > 0) $parts[] = $mins . ' menit';
                if (empty($parts))            $parts[] = '< 1 menit';

                $data['sla_duration'] = implode(' ', $parts);
            } catch (\Throwable $e) {
                $data['sla_duration'] = null;
            }
        }

        // Compute duration in backend using DB TIMESTAMPDIFF (tanggal_perbaikan -> waktu_selesai)
        $durasiMenit = null;
        $repairTimes = ['repair_date' => null, 'finish_time' => null];
        try {
            if (!isset($dashboard)) $dashboard = $this->model('DashboardModel');
            $repairTimes = $dashboard->getRepairTimes($nomorTiket);
            $durasiMenit = $dashboard->getDurasiMenit($nomorTiket);
        } catch (\Throwable $e) {
            error_log('[enrichWithDuration] durasi DB error: ' . $e->getMessage());
        }

        // expose raw fields for diagnostics
        $data['repair_date'] = $repairTimes['repair_date'] ?? null;
        $data['finish_time'] = $repairTimes['finish_time'] ?? null;
        $data['durasi_menit'] = $durasiMenit;

        if ($durasiMenit === null) {
            $data['duration'] = null;
            $data['durasi_ongoing'] = true;
            return $data;
        }

        $hours = intdiv((int)$durasiMenit, 60);
        $minutes = (int)$durasiMenit % 60;
        $parts = [];
        if ($hours > 0) $parts[] = $hours . ' jam';
        if ($minutes > 0) $parts[] = $minutes . ' menit';
        if (empty($parts)) $parts[] = '< 1 menit';

        $data['duration'] = implode(' ', $parts);
        $data['durasi_ongoing'] = false;

        return $data;
    }
}