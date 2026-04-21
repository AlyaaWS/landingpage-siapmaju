<?php
$router->get('/', 'LandingController@index');
// Scan barcode page
$router->get('/scan', 'LandingController@scan');
// Manual PJU input page
$router->get('/input-pju', 'LandingController@inputPju');
// API: lookup PJU by id_pju
$router->get('/api/lookup-pju', 'LandingController@apiLookupPju');
// API: proxy cek-status to admin API (avoids CORS)
$router->get('/api/cek-status', 'LandingController@apiCekStatus');