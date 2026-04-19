<article>
    <a href="/articles">&larr; Alle Artikel</a>
    <?= $htmlContent ?>
</article>

<?php if ($hasCode): ?>
    <script src="/vendor/highlight.js"></script>
    <script>hljs.highlightAll();</script>
<?php endif; ?>
