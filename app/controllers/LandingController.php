<?php

class LandingController extends Controller {
    
    public function index() {
        
        // Memanggil file view dengan mematikan layout admin
        $this->view('landing/index', [
            '_layout' => false, // Ini kunci utamanya biar sidebar gak nyangkut!
            'judul'   => 'SIAP MAJU - Pengaduan PJU Sleman',
            'styles'  => ['css/landing.css'], // Path CSS kamu
            'scripts' => ['js/landing.js']    // Path JS kamu
        ]);
        
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