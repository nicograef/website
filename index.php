<?php include 'projects.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description"
    content="Portfolio of Nico Graef, a Software Engineer specializing in web development and modern technologies.">
  <meta name="author" content="Nico Graef">
  <meta name="robots" content="index, follow">
  <meta property="og:title" content="Nico Graef – Software Engineer">
  <meta property="og:description"
    content="Portfolio of Nico Graef, showcasing projects and skills in software engineering.">
  <meta property="og:image" content="/img/icon.png">
  <meta property="og:url" content="https://nicograef.com">

  <link rel="icon" type="image/png" href="/img/icon.png">
  <link rel="stylesheet" href="styles.css">

  <title>Nico Graef &ndash; Softare Engineer</title>
</head>

<body>
  <main>
    <?php foreach ($projects as $p): ?>
      <section class="project">
        <h3>
          <span class="title"><?= $p->title ?></span>
          <?php if (!empty($p->linkTitle) && !empty($p->linkUrl)): ?>
            <a href="<?= $p->linkUrl ?>" title="<?= $p->linkTitle ?>" target="_blank"
              rel="noopener noreferrer"><?= $p->linkTitle ?></a>
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
    <p>
      <a href="https://github.com/nicograef" target="_blank" rel="noopener noreferrer">Github</a>
      <a href="https://linkedin.com/in/nicograef" target="_blank" rel="noopener noreferrer">LinkedIn</a>
      <a href="https://xing.com/profile/Nico_Graef2/" target="_blank" rel="noopener noreferrer">Xing</a>
    </p>
    <br />
    <p>&copy; <?= date('Y') ?> nico graef</p>
  </footer>
</body>

</html>