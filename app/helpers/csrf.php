<?php

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="'
         . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')
         . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['_csrf_token'] ?? '';
    if (empty($token)
        || !hash_equals($_SESSION['_csrf_token'] ?? '', $token)
    ) {
        http_response_code(403);
        echo 'Invalid CSRF token.';
        exit;
    }
    return true;
}
