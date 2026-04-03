<?php
require_once 'articles.php';
// $lang is available from the calling context (index.php)
$lang = $lang ?? 'en';

$latestArticles = getLatestArticles(3);
?>

<div class="articles">
    <?php foreach ($latestArticles as $article): ?>
        <article class="article-card">
            <h2 class="article-title"><?= htmlspecialchars($article['title']) ?></h2>
            <?php if (!empty($article['description'])): ?>
                <p class="article-description"><?= htmlspecialchars($article['description']) ?></p>
            <?php endif; ?>
            <a href="/articles/<?= htmlspecialchars($article['slug']) ?>" class="article-link">
                <?= $lang === 'de' ? 'lesen' : 'read' ?> &rarr;
            </a>
        </article>
    <?php endforeach; ?>
</div>