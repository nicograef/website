<header>
  <img src="/assets/img/nico-social.jpg" alt="Nico Gräf" title="Nico Gräf" loading="eager" class="profile-picture" />
  <h1 style="margin-bottom: 0">Nico Gräf</h1>
  <p><?= $lang === 'de' ? 'Software Engineer aus Freiburg, Deutschland' : 'Software Engineer from Freiburg, Germany' ?></p>
  <br />
  <p>
    <a href="https://github.com/nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs GitHub-Profil besuchen' : "Visit Nico Gräf's GitHub profile" ?>">Github</a>
    <a href="https://linkedin.com/in/nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs LinkedIn-Profil besuchen' : "Visit Nico Gräf's LinkedIn profile" ?>">LinkedIn</a>
    <a href="https://xing.com/profile/Nico_Graef2/" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs Xing-Profil besuchen' : "Visit Nico Gräf's Xing profile" ?>">Xing</a>
    <a href="https://medium.com/@nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs Medium-Artikel lesen' : "Visit Nico Gräf's Medium articles" ?>">Medium</a>
    <a href="/articles" aria-label="<?= $lang === 'de' ? 'Meine Artikel lesen' : 'Read my articles' ?>"><?= $lang === 'de' ? 'Artikel' : 'Articles' ?></a>
  </p>
</header>

<?php $latestArticles = getLatestArticles(3); ?>
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

<main>
  <?php foreach ($projects as $index => $p): ?>
    <section class="project<?= $index === 0 ? ' fade-in' : '' ?>">
      <h3>
        <span class="title"><?= htmlspecialchars($p['title']) ?></span>
        <?php if (!empty($p['linkTitle']) && !empty($p['linkUrl'])): ?>
          <a href="<?= htmlspecialchars($p['linkUrl']) ?>" title="<?= htmlspecialchars($p['linkTitle']) ?>" target="_blank"
            rel="noopener noreferrer" aria-label="<?= htmlspecialchars($p['linkTitle']) ?> <?= $lang === 'de' ? 'für' : 'for' ?> <?= htmlspecialchars($p['title']) ?>"><?= htmlspecialchars($p['linkTitle']) ?></a>
        <?php endif; ?>
      </h3>
      <div>
        <p>
          <?php foreach ($p['tags'] as $tag): ?>
            <span class="chip"><?= htmlspecialchars($tag) ?></span>
          <?php endforeach; ?>
        </p>
        <p class="description"><?= htmlspecialchars($p['description']) ?></p>
        <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" title="<?= htmlspecialchars($p['title']) ?>" loading="<?= $index === 0 ? 'eager' : 'lazy' ?>" />
      </div>
    </section>
  <?php endforeach; ?>
</main>

<script>
  // Fade-in animation on scroll using Intersection Observer
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('fade-in');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  // Observe all projects except the first one (which is already visible)
  document.querySelectorAll('.project').forEach((project, index) => {
    if (index > 0) {
      observer.observe(project);
    }
  });
</script>
