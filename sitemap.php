<?php

/**
 * Dynamic XML Sitemap
 */
header('Content-Type: application/xml; charset=utf-8');

require __DIR__ . '/articles/functions.php';

$baseUrl = 'https://nicograef.com';
$articles = getArticles();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Homepage -->
    <url>
        <loc><?= $baseUrl ?>/</loc>
        <changefreq>monthly</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Articles Overview -->
    <url>
        <loc><?= $baseUrl ?>/articles</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Individual Articles -->
    <?php foreach ($articles as $article): ?>
        <url>
            <loc><?= $baseUrl ?>/articles/<?= htmlspecialchars($article['slug']) ?></loc>
            <?php if ($article['date']): ?>
                <lastmod><?= date('Y-m-d', strtotime($article['date'])) ?></lastmod>
            <?php endif; ?>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>
    <?php endforeach; ?>
</urlset>