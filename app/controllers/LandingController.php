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
}