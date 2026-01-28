<?php
// Articles overview template
// Variables provided by renderOverview(): $articles
// Layout variables: $pageTitle, $pageDescription, $pageUrl, $pageLang, $extraStyles

ob_start();
?>
<div class="articles-container" id="main-content">
    <a href="/" class="back-link">&larr; Zum Portfolio</a>
    <h1>Artikel</h1>
    <?php if (empty($articles)): ?>
        <p>Noch keine Artikel vorhanden.</p>
    <?php else: ?>
        <div class="article-list">
            <?php foreach ($articles as $article): ?>
                <article class="article-card">
                    <a href="/articles/<?= htmlspecialchars($article['slug']) ?>" class="article-link">
                        <h2 class="article-title"><?= htmlspecialchars($article['title']) ?></h2>
                    </a>
                    <?php if (!empty($article['description'])): ?>
                        <p class="article-description"><?= htmlspecialchars($article['description']) ?></p>
                    <?php endif; ?>
                    <div class="article-meta">
                        <?php if ($article['date']): ?>
                            <span class="date"><?= date('d. F Y', strtotime($article['date'])) ?></span>
                        <?php endif; ?>
                        <?php if ($article['author']): ?>
                            <span class="author"><?= htmlspecialchars($article['author']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($article['tags'])): ?>
                        <div class="article-tags">
                            <?php foreach ($article['tags'] as $tag): ?>
                                <span class="chip"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php
$pageContent = ob_get_clean();
include TEMPLATES_DIR . '/layout.php';
?>