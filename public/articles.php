<?php
require_once __DIR__ . '/lib/articles.php';
require_once __DIR__ . '/lib/render.php';

$slug = $_GET['slug'] ?? null;
if (!$slug) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#^/articles/([a-z0-9-]+)/?$#', $path, $matches)) {
        $slug = $matches[1];
    }
}

if ($slug) {
    $article = getArticle($slug);
    if (!$article) {
        http_response_code(404);
        include __DIR__ . '/404.php';
        return;
    }

    $htmlContent = parseArticleMarkdown($slug);
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
        'htmlContent' => $htmlContent,
        'hasCode' => $hasCode,
    ]);
} else {
    $articles = getArticles();

    render(__DIR__ . '/templates/overview.php', [
        'pageTitle' => 'Artikel | Nico Gräf',
        'pageDescription' => 'Artikel über Software-Entwicklung, Architektur und moderne Technologien von Nico Gräf.',
        'pageUrl' => '/articles',
        'pageLang' => 'de',
        'pageStyles' => ['/assets/css/overview.css'],
        'lang' => 'de',
        'currentPage' => 'articles',
        'articles' => $articles,
    ]);
}
