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
}