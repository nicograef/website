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
    <header>
        <h1>My Projects</h1>
    </header>
    <main>
        <?php foreach ($projects as $index => $project): ?>
            <section class="project big <?= $index % 2 ? 'left' : 'right' ?>">
                <h3>
                    <?= htmlspecialchars($project->title) ?>
                    <span style="font-size: small;">
                        <?php if (!empty($project->linkTitle) && !empty($project->linkUrl)): ?>
                            <a href="<?= htmlspecialchars($project->linkUrl) ?>"
                                title="<?= htmlspecialchars($project->linkTitle) ?>">
                                <?= htmlspecialchars($project->linkTitle) ?>
                            </a>
                        <?php endif; ?>
                    </span>
                </h3>
                <div>
                    <p>
                        <?php foreach ($project->tags as $tag): ?>
                            <span class="chip"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </p>
                    <p><?= htmlspecialchars($project->description) ?></p>
                    <img src="<?= htmlspecialchars($project->image) ?>" alt="<?= htmlspecialchars($project->title) ?>"
                        title="<?= htmlspecialchars($project->title) ?>" loading="lazy" />
                </div>
            </section>
        <?php endforeach; ?>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> nico graef</p>
    </footer>
</body>

</html>