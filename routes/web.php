<?php
$router->get('/', 'LandingController@index');
// Scan barcode page
$router->get('/scan', 'LandingController@scan');