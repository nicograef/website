<main>
    <div class="blog-head-wrap">
        <div class="glow"></div>
        <div class="blog-head">
            <p class="eyebrow">Blog</p>
            <h1>Artikel</h1>
            <p class="blog-intro">Ich schreibe über Softwarearchitektur, Domain Driven Design, Event Sourcing und die Arbeit mit KI-Coding-Agents.</p>
        </div>
    </div>

    <div class="blog-list">
        <?php foreach ($articlesByYear as $year => $yearArticles): ?>
            <section class="year-group">
                <h2 class="year-label"><?= htmlspecialchars($year) ?></h2>
                <div class="year-list">
                    <?php foreach ($yearArticles as $article): ?>
                        <a class="article-row" href="/articles/<?= htmlspecialchars($article['slug']) ?>">
                            <span class="article-row-date"><?= htmlspecialchars(formatArticleDate($article['date'], 'de', false)) ?></span>
                            <span class="article-row-text">
                                <span class="article-row-title"><?= htmlspecialchars($article['title']) ?></span>
                                <span class="article-row-description"><?= htmlspecialchars($article['description']) ?></span>
                            </span>
                            <span class="article-row-arrow" aria-hidden="true">&rarr;</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</main>
