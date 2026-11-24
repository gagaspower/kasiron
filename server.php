<?php

$publicPath = __DIR__.'/public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

if ($uri === '/build/sw.js') {
    $serviceWorkerPath = $publicPath.'/build/sw.js';

    if (! is_file($serviceWorkerPath)) {
        http_response_code(404);
        exit;
    }

    header('Service-Worker-Allowed: /');
    header('Content-Type: text/javascript; charset=utf-8');
    readfile($serviceWorkerPath);
    exit;
}

if ($uri === '/manifest.webmanifest') {
    header('Content-Type: application/manifest+json; charset=utf-8');
    readfile($publicPath.$uri);
    exit;
}

if ($uri !== '/' && is_file($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
