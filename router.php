<?php

/**
 * Dev router for PHP's built-in server.
 * Usage: php -S 0.0.0.0:8080 -t public router.php
 *
 * The -t flag makes public/ the document root for static files;
 * this router handles pretty-URL rewrites that Apache's .htaccess
 * handles in production.
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Block direct .php access — only pretty routes are public
if (substr($uri, -4) === '.php') {
    http_response_code(404);
    require __DIR__ . '/public/404.php';
    return;
}

// Static files — let the built-in server handle them directly
if ($uri !== '/' && is_file(__DIR__ . '/public' . $uri)) {
    return false;
}

// Route: homepage
if ($uri === '/') {
    require __DIR__ . '/public/index.php';
    return;
}

// Route: articles
if ($uri === '/articles' || preg_match('#^/articles/.+#', $uri)) {
    require __DIR__ . '/public/articles.php';
    return;
}

// Route: CV
if ($uri === '/cv') {
    require __DIR__ . '/public/cv.php';
    return;
}

// Route: sitemap
if ($uri === '/sitemap.xml') {
    require __DIR__ . '/public/sitemap.php';
    return;
}

// Everything else: 404
http_response_code(404);
require __DIR__ . '/public/404.php';
