<div class="articles-container">
    <a href="/" class="back-link">&larr; Zum Portfolio</a>
    <h1>Meine Artikel</h1>
    <div class="article-list">
        <?php foreach ($articles as $article): ?>
            <a href="/articles/<?= htmlspecialchars($article['slug']) ?>" class="article-link">
                <article class="article-card">
                    <h2 class="article-title"><?= htmlspecialchars($article['title']) ?></h2>
                    <?php if (!empty($article['description'])): ?>
                        <p class="article-description"><?= htmlspecialchars($article['description']) ?></p>
                    <?php endif; ?>
                    <div class="article-meta">
                        <?php if ($article['date']): ?>
                            <span class="date"><?= date('d. F Y', strtotime($article['date'])) ?></span>
                        <?php endif; ?>
                    </div>
                </article>
            </a>
        <?php endforeach; ?>
    </div>
</div>
