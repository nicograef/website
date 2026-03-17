<?php

/**
 * Render a template inside the shared layout.
 *
 * Buffers the template output, then includes layout.php with
 * the buffered content as $pageContent alongside the layout variables.
 *
 * @param string $template Absolute path to the template file
 * @param array  $vars     Layout variables ($pageTitle, $pageDescription, etc.)
 *                         and any template-specific variables
 */
function render(string $template, array $vars = []): void
{
    extract($vars);
    ob_start();
    include $template;
    $pageContent = ob_get_clean();
    include __DIR__ . '/../templates/layout.php';
}

/**
 * Render the shared layout with pre-buffered content.
 *
 * Use this when page content is already captured (e.g. inline HTML in index.php).
 *
 * @param array $vars Layout variables including 'pageContent'
 */
function renderLayout(array $vars): void
{
    extract($vars);
    include __DIR__ . '/../templates/layout.php';
}
