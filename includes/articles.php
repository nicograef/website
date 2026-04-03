<?php
require __DIR__ . '/../vendor/Parsedown.php';
require_once __DIR__ . '/render.php';

// Base paths
define('CONTENT_DIR', __DIR__ . '/../content/articles');
define('TEMPLATES_DIR', __DIR__ . '/../templates');

/**
 * Get all published articles from articles.json (sorted newest-first).
 */
function getArticles(): array
{
    $json = file_get_contents(__DIR__ . '/../content/articles.json');
    return json_decode($json, true);
}

/**
 * Get the N most recent articles.
 */
function getLatestArticles(int $limit): array
{
    return array_slice(getArticles(), 0, $limit);
}

/**
 * Render a single article by slug.
 */
function renderArticle(string $slug): void
{
    // Validate slug against the published articles list
    $articles = getArticles();
    $article = null;
    foreach ($articles as $a) {
        if ($a['slug'] === $slug) {
            $article = $a;
            break;
        }
    }

    if (!$article) {
        http_response_code(404);
        include __DIR__ . '/../404.php';
        return;
    }

    $filePath = CONTENT_DIR . '/' . $slug . '.md';
    $markdown = file_get_contents($filePath);
    $Parsedown = new Parsedown();
    $htmlContent = $Parsedown->text($markdown);

    render(TEMPLATES_DIR . '/articles/article.php', [
        'pageTitle' => $article['title'] . ' | Nico Gräf',
        'pageDescription' => $article['description'],
        'pageUrl' => '/articles/' . $slug,
        'pageLang' => 'de',
        'extraStyles' => ['/assets/css/article.css', '/vendor/highlight.css'],
        'htmlContent' => $htmlContent,
    ]);
}

/**
 * Render the articles overview page.
 */
function renderOverview(): void
{
    $articles = getArticles();

    render(TEMPLATES_DIR . '/articles/overview.php', [
        'pageTitle' => 'Artikel | Nico Gräf',
        'pageDescription' => 'Artikel über Software-Entwicklung, Architektur und moderne Technologien von Nico Gräf.',
        'pageUrl' => '/articles',
        'pageLang' => 'de',
        'extraStyles' => ['/assets/css/overview.css'],
        'articles' => $articles,
    ]);
}
