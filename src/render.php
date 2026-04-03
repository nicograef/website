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
    include __DIR__ . '/layout.php';
}
