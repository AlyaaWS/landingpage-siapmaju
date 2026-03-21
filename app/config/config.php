<?php
define('APP_NAME', 'LANDING PAGE SIAP MAJU');

// ── Environment ───────────────────────────────────────────────────────────────
// BEFORE UPLOADING TO PRODUCTION: change 'development' → 'production'
// This silences PHP error output to end-users (errors still go to server log).
define('APP_ENV', 'production'); // TODO: set to 'production' on live server

// ── Production URL override ───────────────────────────────────────────────────
// Leave commented-out on localhost; Ngrok and the dynamic detector handle it.
// On a live server, uncomment and set your domain so every base_url() call
// (including QR codes in PDFs) always encodes the correct public URL.
//
define('APP_URL', 'https://pju.dishubsleman.id');       // root domain
// define('APP_URL', 'https://adminpju.dishubsleman.id');  // subdomain
