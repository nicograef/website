<?php require __DIR__ . '/header.php'; ?>

<main>
    <div class="article-list">
        <?php foreach ($articles as $article): ?>
            <a href="/articles/<?= htmlspecialchars($article['slug']) ?>" class="article-link">
                <article class="article-card">
                    <span class="article-meta"><?= date('d. F Y', strtotime($article['date'])) ?></span>
                    <h3 class="article-title"><?= htmlspecialchars($article['title']) ?></h3>
                    <p class="article-description"><?= htmlspecialchars($article['description']) ?></p>
                </article>
            </a>
        <?php endforeach; ?>
    </div>
</main>