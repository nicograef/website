<?php

/**
 * Articles Controller
 * Routes requests to the appropriate view
 */
require 'functions.php';

$slug = getSlugFromRequest();

if ($slug) {
    renderArticle($slug);
} else {
    renderOverview();
}
