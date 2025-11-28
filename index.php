<?php include 'projects.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description"
    content="Portfolio of Nico Gräf, a Software Engineer specializing in web development and modern technologies.">
  <meta name="author" content="Nico Gräf">
  <meta name="robots" content="index, follow">
  <meta property="og:title" content="Nico Gräf – Software Engineer">
  <meta property="og:description"
    content="Portfolio of Nico Gräf, showcasing projects and skills in software engineering.">
  <meta property="og:image" content="/img/icon.png">
  <meta property="og:url" content="https://nicograef.com">

  <link rel="icon" type="image/png" href="/img/icon.png">
  <link rel="stylesheet" href="styles.css">

  <title>Nico Gräf &ndash; Software Engineer</title>
</head>

<body>
  <header>
    <h1>Nico Gräf</h1>
    <p lang="en">Software Engineer from Freiburg, Germany</p>
    <br />
    <p>
      <a href="https://github.com/nicograef" target="_blank" rel="noopener noreferrer" aria-label="Visit Nico Gräf's GitHub profile">Github</a>
      <a href="https://linkedin.com/in/nicograef" target="_blank" rel="noopener noreferrer" aria-label="Visit Nico Gräf's LinkedIn profile">LinkedIn</a>
      <a href="https://xing.com/profile/Nico_Graef2/" target="_blank" rel="noopener noreferrer" aria-label="Visit Nico Gräf's Xing profile">Xing</a>
    </p>
  </header>
  <main>
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
  <footer>
    <p>&copy; <?= date('Y') ?> Nico Gräf</p>
  </footer>
</body>

</html>