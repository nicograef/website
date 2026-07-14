<?php
require_once __DIR__ . '/lib/articles.php';
require_once __DIR__ . '/lib/render.php';

$slug = $_GET['slug'] ?? null;
if (!is_string($slug)) {
    $slug = null; // e.g. ?slug[]=… would arrive as an array
}
if (!$slug) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#^/articles/([a-z0-9-]+)/?$#', $path, $matches)) {
        $slug = $matches[1];
    }
}

if ($slug) {
    $article = getArticle($slug);
    $markdown = $article ? loadArticleMarkdown($slug) : null;
    if (!$article || $markdown === null) {
        http_response_code(404);
        include __DIR__ . '/404.php';
        return;
    }

    $readingMinutes = estimateReadingMinutes($markdown);

    $htmlContent = parseArticleMarkdown($markdown);
    // The template renders the title itself — drop the markdown's leading H1.
    $htmlContent = preg_replace('#\A\s*<h1[^>]*>.*?</h1>\s*#s', '', $htmlContent, 1);
    $hasCode = strpos($htmlContent, '<pre><code') !== false;

    $pageStyles = ['/assets/css/article.css'];
    if ($hasCode) {
        $pageStyles[] = '/vendor/highlight.css';
    }

    render(__DIR__ . '/templates/article.php', [
        'pageTitle' => $article['title'] . ' | Nico Gräf',
        'pageDescription' => $article['description'],
        'pageUrl' => '/articles/' . $slug,
        'pageLang' => 'de',
        'pageStyles' => $pageStyles,
        'lang' => 'de',
        'currentPage' => 'articles',
        'article' => $article,
        'readingMinutes' => $readingMinutes,
        'htmlContent' => $htmlContent,
        'hasCode' => $hasCode,
    ]);
} else {
    $articlesByYear = groupArticlesByYear(getArticles());

    render(__DIR__ . '/templates/overview.php', [
        'pageTitle' => 'Artikel | Nico Gräf',
        'pageDescription' => 'Artikel über Softwarearchitektur, Domain Driven Design, Event Sourcing und die Arbeit mit KI-Coding-Agents von Nico Gräf.',
        'pageUrl' => '/articles',
        'pageLang' => 'de',
        'pageStyles' => ['/assets/css/overview.css'],
        'lang' => 'de',
        'currentPage' => 'articles',
        'articlesByYear' => $articlesByYear,
    ]);
}
