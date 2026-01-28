<?php
include 'templates/projects.php';

// Layout data
$pageTitle = 'Nico Gräf – Software Engineer';
$pageDescription = 'Portfolio of Nico Gräf, a Software Engineer specializing in web development and modern technologies.';
$pageUrl = '/';
$pageLang = 'en';
$pageImage = '/assets/img/nico-social.jpg';

ob_start();
?>
<header>
  <img src="/assets/img/nico-social.jpg" alt="Nico Gräf" title="Nico Gräf" loading="eager" class="profile-picture" />
  <h1 style="margin-bottom: 0">Nico Gräf</h1>
  <p>Software Engineer from Freiburg, Germany</p>
  <br />
  <br />
  <p>
    <a href="https://github.com/nicograef" target="_blank" rel="noopener noreferrer" aria-label="Visit Nico Gräf's GitHub profile">Github</a>
    <a href="https://linkedin.com/in/nicograef" target="_blank" rel="noopener noreferrer" aria-label="Visit Nico Gräf's LinkedIn profile">LinkedIn</a>
    <a href="https://xing.com/profile/Nico_Graef2/" target="_blank" rel="noopener noreferrer" aria-label="Visit Nico Gräf's Xing profile">Xing</a>
    <a href="https://medium.com/@nicograef" target="_blank" rel="noopener noreferrer" aria-label="Visit Nico Gräf's Medium articles">Medium</a>
    <a href="/articles" aria-label="Read my articles">Articles</a>
  </p>
</header>
<main id="main-content">
  <?php foreach ($projects as $p): ?>
    <section class="project">
      <h3>
        <span class="title"><?= $p->title ?></span>
        <?php if (!empty($p->linkTitle) && !empty($p->linkUrl)): ?>
          <a href="<?= htmlspecialchars($p->linkUrl, ENT_QUOTES, 'UTF-8') ?>" title="<?= $p->linkTitle ?>" target="_blank"
            rel="noopener noreferrer" aria-label="<?= $p->linkTitle ?> for <?= $p->title ?>"><?= $p->linkTitle ?></a>
        <?php endif; ?>
      </h3>
      <div>
        <p>
          <?php foreach ($p->tags as $tag): ?>
            <span class="chip"><?= $tag ?></span>
          <?php endforeach; ?>
        </p>
        <p class="description"><?= $p->description ?></p>
        <img src="<?= $p->image ?>" alt="<?= $p->title ?>" title="<?= $p->title ?>" loading="lazy" />
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

  document.querySelectorAll('.project').forEach(project => {
    observer.observe(project);
  });
</script>
<?php
$pageContent = ob_get_clean();
include 'templates/layout.php';
?>