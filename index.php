<?php
require_once 'includes/lang.php';
include 'templates/projects.php';
include 'includes/render.php';

$lang = detectLang();
$projects = loadProjects($lang);

ob_start();
?>

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

<?php include __DIR__ . '/includes/articles-latest.php'; ?>

<main>
  <?php foreach ($projects as $index => $p): ?>
    <section class="project<?= $index === 0 ? ' fade-in' : '' ?>">
      <h3>
        <span class="title"><?= $p->title ?></span>
        <?php if (!empty($p->linkTitle) && !empty($p->linkUrl)): ?>
          <a href="<?= htmlspecialchars($p->linkUrl, ENT_QUOTES, 'UTF-8') ?>" title="<?= $p->linkTitle ?>" target="_blank"
            rel="noopener noreferrer" aria-label="<?= $p->linkTitle ?> <?= $lang === 'de' ? 'für' : 'for' ?> <?= $p->title ?>"><?= $p->linkTitle ?></a>
        <?php endif; ?>
      </h3>
      <div>
        <p>
          <?php foreach ($p->tags as $tag): ?>
            <span class="chip"><?= $tag ?></span>
          <?php endforeach; ?>
        </p>
        <p class="description"><?= $p->description ?></p>
        <img src="<?= $p->image ?>" alt="<?= $p->title ?>" title="<?= $p->title ?>" loading="<?= $index === 0 ? 'eager' : 'lazy' ?>" />
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

<?php
$pageContent = ob_get_clean();

$isGerman = $lang === 'de';

renderLayout([
    'pageTitle' => 'Nico Gräf – Software Engineer',
    'pageDescription' => $isGerman
        ? 'Portfolio von Nico Gräf, Software Engineer mit Fokus auf Webentwicklung und moderne Technologien.'
        : 'Portfolio of Nico Gräf, a Software Engineer specializing in web development and modern technologies.',
    'pageUrl' => '/',
    'pageLang' => $lang,
    'pageImage' => '/assets/img/nico-social.jpg',
    'extraStyles' => ['/assets/css/main.css'],
    'pageContent' => $pageContent,
]);
?>