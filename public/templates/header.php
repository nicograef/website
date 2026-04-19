<header>
  <img src="/assets/img/nico-social.jpg" alt="Nico Gräf" title="Nico Gräf" loading="eager" class="profile-picture" />
  <h1 style="margin-bottom: 0">Nico Gräf</h1>
  <p><?= htmlspecialchars($tagline) ?></p>
  <br />
  <p>
    <?php if ($showPortfolioLink): ?>
      <a href="/" aria-label="<?= $lang === 'de' ? 'Zum Portfolio' : 'Portfolio' ?>">Portfolio</a>
    <?php endif; ?>
    <a href="https://github.com/nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs GitHub-Profil besuchen' : "Visit Nico Gräf's GitHub profile" ?>">Github</a>
    <a href="https://linkedin.com/in/nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs LinkedIn-Profil besuchen' : "Visit Nico Gräf's LinkedIn profile" ?>">LinkedIn</a>
    <a href="https://xing.com/profile/Nico_Graef2/" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs Xing-Profil besuchen' : "Visit Nico Gräf's Xing profile" ?>">Xing</a>
    <a href="https://medium.com/@nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs Medium-Artikel lesen' : "Visit Nico Gräf's Medium articles" ?>">Medium</a>
    <a href="/articles" aria-label="<?= $lang === 'de' ? 'Meine Artikel lesen' : 'Read my articles' ?>"><?= $lang === 'de' ? 'Artikel' : 'Articles' ?></a>
  </p>
</header>
