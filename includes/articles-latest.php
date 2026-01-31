<?php
require_once 'articles.php';

$allArticles = getArticles();
$articles = array_filter($allArticles, fn($a) => empty($a['draft']));
usort($articles, fn($a, $b) => strcmp($b['date'], $a['date']));
$latestArticles = array_slice($articles, 0, 3);
?>

<div class="articles">
    <?php foreach ($latestArticles as $article): ?>
        <article class="article-card">
            <h2 class="article-title"><?= htmlspecialchars($article['title']) ?></h2>
            <?php if (!empty($article['description'])): ?>
                <p class="article-description"><?= htmlspecialchars($article['description']) ?></p>
            <?php endif; ?>
            <a href="/articles/<?= htmlspecialchars($article['slug']) ?>" class="article-link">
                lesen &rarr;
            </a>
        </article>
        </a>
    <?php endforeach; ?>
</div>