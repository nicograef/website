<?php
require_once __DIR__ . '/../vendor/Parsedown.php';
require_once __DIR__ . '/render.php';

// Base path
define('CONTENT_DIR', __DIR__ . '/../content/articles');

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
    if (!is_file($filePath)) {
        http_response_code(404);
        include __DIR__ . '/../404.php';
        return;
    }
    $markdown = file_get_contents($filePath);
    $Parsedown = new Parsedown();
    $htmlContent = $Parsedown->text($markdown);
    $hasCode = str_contains($htmlContent, '<pre><code');

    render(__DIR__ . '/article.php', [
        'pageTitle' => $article['title'] . ' | Nico Gräf',
        'pageDescription' => $article['description'],
        'pageUrl' => '/articles/' . $slug,
        'pageLang' => 'de',
        'pageStyles' => ['/assets/css/article.css'],
        'htmlContent' => $htmlContent,
        'hasCode' => $hasCode,
    ]);
}

/**
 * Render the articles overview page.
 */
function renderOverview(): void
{
    $articles = getArticles();

    render(__DIR__ . '/overview.php', [
        'pageTitle' => 'Artikel | Nico Gräf',
        'pageDescription' => 'Artikel über Software-Entwicklung, Architektur und moderne Technologien von Nico Gräf.',
        'pageUrl' => '/articles',
        'pageLang' => 'de',
        'articles' => $articles,
    ]);
}
