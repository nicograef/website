<?php

/**
 * Articles Controller
 * Routes requests to the appropriate view
 */
require __DIR__ . '/../includes/articles.php';

$slug = getSlugFromRequest();

if ($slug) {
    renderArticle($slug);
} else {
    renderOverview();
}
