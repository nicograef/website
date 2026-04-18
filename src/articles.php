<?php
require_once __DIR__ . '/../vendor/Parsedown.php';

function getArticles(): array
{
    $json = file_get_contents(__DIR__ . '/../content/articles.json');
    return json_decode($json, true);
}

function getLatestArticles(int $limit): array
{
    return array_slice(getArticles(), 0, $limit);
}

function getArticle(string $slug): ?array
{
    foreach (getArticles() as $article) {
        if ($article['slug'] === $slug) {
            return $article;
        }
    }
    return null;
}

function parseArticleMarkdown(string $slug): string
{
    $path = __DIR__ . '/../content/articles/' . $slug . '.md';
    $markdown = file_get_contents($path);
    $parsedown = new Parsedown();
    return $parsedown->text($markdown);
}
