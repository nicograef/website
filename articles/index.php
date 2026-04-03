<?php

/**
 * Articles Controller
 * Routes requests to the appropriate view
 */
require __DIR__ . '/../includes/articles.php';

$slug = $_GET['slug'] ?? null;
if (!$slug) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#^/articles/([a-z0-9-]+)/?$#', $path, $matches)) {
        $slug = $matches[1];
    }
}

if ($slug) {
    renderArticle($slug);
} else {
    renderOverview();
}
