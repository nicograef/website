<?php

declare(strict_types=1);

/**
 * Render a template inside the shared layout.
 *
 * Buffers the template output, then includes layout.php with
 * the buffered content as $pageContent alongside the layout variables.
 *
 * @param string               $template Absolute path to the template file
 * @param array<string, mixed> $vars     Layout variables ($pageTitle, $pageDescription, etc.)
 *                                        and any template-specific variables
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
 * Append a content-based version query to a static asset URL for cache-busting.
 *
 * Browsers cache CSS/JS aggressively (see the immutable headers in .htaccess);
 * a stable filename would keep serving a stale copy after a deploy. The `?v=`
 * hash changes only when the file's bytes change, so the URL — and thus the
 * cache entry — turns over exactly when the asset does. Returns the path
 * unchanged if the file is not found on disk.
 *
 * @param string $path Root-absolute web path, e.g. '/assets/css/home.css'
 */
function asset(string $path): string
{
    $file = __DIR__ . '/..' . $path;
    $hash = is_file($file) ? hash_file('crc32b', $file) : false;

    return $hash === false ? $path : $path . '?v=' . $hash;
}
