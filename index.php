<?php
include 'projects.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nico Graef</title>
  <link rel="stylesheet" href="styles.css">
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