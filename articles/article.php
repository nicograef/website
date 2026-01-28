<?php
// Single article view template
// Variables provided by renderArticle(): $title, $description, $date, $author, $tags, $htmlContent
// Layout variables: $pageTitle, $pageDescription, $pageUrl, $pageLang, $extraStyles

// Add highlight.js styles (local)
$extraStyles[] = '/articles/highlight.css';

ob_start();
?>

<article id="main-content">
    <a href="/articles" class="back-link">&larr; Alle Artikel</a>
    <?= $htmlContent ?>
    <?php if ($date || $author): ?>
        <p class="article-meta">
            <?php if ($date): ?>
                Veröffentlicht am <?= date('d.m.Y', strtotime($date)) ?>
            <?php endif; ?>
            <?php if ($author): ?>
                von <?= htmlspecialchars($author) ?>
            <?php endif; ?>
        </p>
    <?php endif; ?>
</article>

<!-- Syntax Highlighting (local) -->
<script src="/articles/highlight.js"></script>
<script>
    hljs.highlightAll();
</script>
<?php
$pageContent = ob_get_clean();
include __DIR__ . '/../layout.php';
?>