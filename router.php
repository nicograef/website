<?php

/**
 * Dev router for PHP's built-in server.
 * Usage: php -S 0.0.0.0:8080 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Static files — let the built-in server handle them directly
$publicPath = __DIR__ . $uri;
if ($uri !== '/' && is_file($publicPath)) {
    return false;
}

// Route: homepage
if ($uri === '/') {
    require __DIR__ . '/index.php';
    return;
}

// Route: articles
if ($uri === '/articles' || preg_match('#^/articles/.+#', $uri)) {
    require __DIR__ . '/articles/index.php';
    return;
}

// Route: CV
if ($uri === '/cv') {
    require __DIR__ . '/cv.php';
    return;
}

// Route: sitemap
if ($uri === '/sitemap.xml') {
    require __DIR__ . '/sitemap.php';
    return;
}

// Everything else: 404
http_response_code(404);
require __DIR__ . '/404.php';
