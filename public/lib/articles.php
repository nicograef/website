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

/**
 * Load an article's raw markdown, or null if the .md file is missing
 * (e.g. a slug listed in articles.json without a matching file).
 */
function loadArticleMarkdown(string $slug): ?string
{
    $path = __DIR__ . '/../content/articles/' . $slug . '.md';
    if (!is_file($path)) {
        return null;
    }
    $markdown = file_get_contents($path);
    return $markdown === false ? null : $markdown;
}

function parseArticleMarkdown(string $markdown): string
{
    $parsedown = new Parsedown();
    $html = $parsedown->text($markdown);
    return str_replace('<img ', '<img loading="lazy" ', $html);
}

/**
 * Format an ISO date (YYYY-MM-DD) without locale dependency,
 * e.g. de "13. Juli 2025" / en "July 13, 2025".
 */
function formatArticleDate(string $isoDate, string $lang, bool $withYear = true): string
{
    $months = $lang === 'de'
        ? ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember']
        : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    $timestamp = strtotime($isoDate);
    $day = (int) date('j', $timestamp);
    $month = $months[(int) date('n', $timestamp) - 1];
    $year = date('Y', $timestamp);

    if ($lang === 'de') {
        return $day . '. ' . $month . ($withYear ? ' ' . $year : '');
    }
    return $month . ' ' . $day . ($withYear ? ', ' . $year : '');
}

/**
 * Group articles by year, sorted descending by date
 * (newest year first, newest article first within each year).
 */
function groupArticlesByYear(array $articles): array
{
    usort($articles, function (array $a, array $b): int {
        return strcmp($b['date'], $a['date']);
    });

    $grouped = [];
    foreach ($articles as $article) {
        $year = substr($article['date'], 0, 4);
        $grouped[$year][] = $article;
    }
    return $grouped;
}

/**
 * Estimate reading time from raw markdown: words / 200, rounded, at least 1.
 * Unicode-safe word count (str_word_count breaks on umlauts).
 */
function estimateReadingMinutes(string $markdown): int
{
    $words = preg_split('/\s+/u', trim($markdown), -1, PREG_SPLIT_NO_EMPTY);
    $count = $words === false ? 0 : count($words);
    return max(1, (int) round($count / 200));
}
