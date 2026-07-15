<?php

declare(strict_types=1);

/**
 * Single article template.
 *
 * @var Article $article
 * @var int     $readingMinutes
 * @var string  $htmlContent
 * @var bool    $hasCode
 */
?>
<main class="article-wrap">
    <div class="glow"></div>
    <article class="article">
        <a class="backlink" href="/articles">&larr; Alle Artikel</a>
        <p class="article-meta">
            <span><?= htmlspecialchars(formatArticleDate($article['date'], 'de')) ?></span>
            <span aria-hidden="true">&middot;</span>
            <span><?= htmlspecialchars((string) $readingMinutes) ?> Min. Lesezeit</span>
        </p>
        <h1><?= htmlspecialchars($article['title']) ?></h1>
        <p class="lead"><?= htmlspecialchars($article['description']) ?></p>

        <div class="prose">
            <?= $htmlContent ?>
        </div>

        <div class="author-card card">
            <img src="/assets/img/nico-social.jpg" alt="Nico Gräf" width="64" height="64">
            <div class="author-card-text">
                <p class="author-card-name">Nico Gräf</p>
                <p class="author-card-bio">Senior Software Engineer aus Freiburg. Baut aktuell <a href="https://jotti.rocks" target="_blank" rel="noopener noreferrer">jotti</a>, ein Kassensystem für Vereine.</p>
            </div>
            <a class="author-card-link" href="/articles">Alle Artikel &rarr;</a>
        </div>
    </article>
</main>

<?php if ($hasCode): ?>
    <script src="<?= htmlspecialchars(asset('/vendor/highlight.js')) ?>"></script>
    <script>
        hljs.highlightAll();
    </script>
<?php endif; ?>
