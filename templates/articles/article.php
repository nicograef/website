<?php
// Single article view template
// Variables provided by renderArticle(): $title, $description, $date, $author, $tags, $htmlContent
// Layout variables: $pageTitle, $pageDescription, $pageUrl, $pageLang, $extraStyles

// Add highlight.js styles (vendor)
$extraStyles[] = '/vendor/highlight.css';

ob_start();
?>

<article id="main-content">
    <a href="/articles" class="back-link">&larr; Alle Artikel</a>
    <?= $htmlContent ?>
</article>

<!-- Syntax Highlighting (highlight.js) -->
<script src="/vendor/highlight.js"></script>
<script>
    hljs.highlightAll();
</script>
<?php
$pageContent = ob_get_clean();
include TEMPLATES_DIR . '/layout.php';
?>