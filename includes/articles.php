<?php
require __DIR__ . '/../vendor/Parsedown.php';

// Base paths
define('CONTENT_DIR', __DIR__ . '/../content/articles');
define('TEMPLATES_DIR', __DIR__ . '/../templates');

/**
 * Parse YAML frontmatter from markdown content
 * Returns array with 'meta' (frontmatter data) and 'content' (markdown body)
 */
function parseArticle($markdown)
{
    $meta = [];
    $content = $markdown;

    if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $markdown, $matches)) {
        $frontmatter = $matches[1];
        $content = $matches[2];

        // Simple YAML parsing for key: value pairs and lists
        $currentKey = null;
        foreach (explode("\n", $frontmatter) as $line) {
            if (preg_match('/^(\w+):\s*$/', $line, $m)) {
                // Key with list values following
                $currentKey = $m[1];
                $meta[$currentKey] = [];
            } elseif (preg_match('/^(\w+):\s*(.+)$/', $line, $m)) {
                // Key: value pair
                $meta[$m[1]] = trim($m[2]);
                $currentKey = null;
            } elseif (preg_match('/^\s+-\s*(.+)$/', $line, $m) && $currentKey) {
                // List item
                $meta[$currentKey][] = trim($m[1]);
            }
        }
    }

    return ['meta' => $meta, 'content' => $content];
}

/**
 * Get all articles from markdown files
 */
function getArticles()
{
    $articles = [];
    $files = glob(CONTENT_DIR . '/*.md');

    foreach ($files as $file) {
        $slug = basename($file, '.md');
        $markdown = file_get_contents($file);
        $parsed = parseArticle($markdown);

        $articles[] = [
            'slug' => $slug,
            'title' => $parsed['meta']['title'] ?? $slug,
            'description' => $parsed['meta']['description'] ?? '',
            'date' => $parsed['meta']['date'] ?? '',
            'author' => $parsed['meta']['author'] ?? '',
            'tags' => $parsed['meta']['tags'] ?? [],
        ];
    }

    // Sort by date descending
    usort($articles, fn($a, $b) => strcmp($b['date'], $a['date']));

    return $articles;
}

/**
 * Get slug from GET parameter or parse from URL path (for PHP built-in server without .htaccess)
 */
function getSlugFromRequest()
{
    $slug = $_GET['slug'] ?? null;
    if (!$slug) {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (preg_match('#^/articles/([a-z0-9-]+)/?$#', $path, $matches)) {
            $slug = $matches[1];
        }
    }
    return $slug;
}

/**
 * Render a single article by slug
 */
function renderArticle($slug)
{
    $filePath = CONTENT_DIR . '/' . $slug . '.md';

    if (!file_exists($filePath)) {
        http_response_code(404);
        include __DIR__ . '/../404.php';
        return;
    }

    $markdown = file_get_contents($filePath);
    $parsed = parseArticle($markdown);
    $Parsedown = new Parsedown();

    // Article data
    $title = $parsed['meta']['title'] ?? $slug;
    $description = $parsed['meta']['description'] ?? '';
    $date = $parsed['meta']['date'] ?? '';
    $author = $parsed['meta']['author'] ?? '';
    $tags = $parsed['meta']['tags'] ?? [];
    $htmlContent = $Parsedown->text($parsed['content']);

    // Layout data
    $pageTitle = $title . ' | Nico Gräf';
    $pageDescription = $description;
    $pageUrl = '/articles/' . $slug;
    $pageLang = 'de';
    $extraStyles = ['/assets/css/article.css'];

    include TEMPLATES_DIR . '/articles/article.php';
}

/**
 * Render the articles overview page
 */
function renderOverview()
{
    $articles = getArticles();

    // Layout data
    $pageTitle = 'Artikel | Nico Gräf';
    $pageDescription = 'Artikel über Software-Entwicklung, Architektur und moderne Technologien von Nico Gräf.';
    $pageUrl = '/articles';
    $pageLang = 'de';
    $extraStyles = ['/assets/css/overview.css'];

    include TEMPLATES_DIR . '/articles/overview.php';
}
