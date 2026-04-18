<?php $basics = $cv['basics']; ?>
<header>
  <img src="/assets/img/nico-social.jpg" alt="Nico Gräf" title="Nico Gräf" loading="eager" class="profile-picture" />
  <h1 style="margin-bottom: 0"><?= htmlspecialchars($basics['name']) ?></h1>
  <p><?= htmlspecialchars($basics['headline']) ?> <?= $l['from'] ?> <?= htmlspecialchars($basics['location']) ?></p>
  <br />
  <p>
    <a href="/" aria-label="<?= htmlspecialchars($l['portfolio_aria']) ?>"><?= htmlspecialchars($l['portfolio']) ?></a>
    <a href="https://github.com/nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars($l['github_aria']) ?>">Github</a>
    <a href="https://linkedin.com/in/nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars($l['linkedin_aria']) ?>">LinkedIn</a>
    <a href="https://xing.com/profile/Nico_Graef2/" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars($l['xing_aria']) ?>">Xing</a>
    <a href="https://medium.com/@nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars($l['medium_aria']) ?>">Medium</a>
    <a href="/articles" aria-label="<?= htmlspecialchars($l['articles_aria']) ?>"><?= htmlspecialchars($l['articles']) ?></a>
  </p>
</header>

<div class="cv">
    <?php if (!empty($basics['summary'])): ?>
        <p class="cv-summary"><?= htmlspecialchars($basics['summary']) ?></p>
    <?php endif; ?>

    <h2><?= $l['experience'] ?></h2>
    <?php foreach ($cv['experience'] as $exp): ?>
        <div class="cv-entry">
            <h3>
                <?= htmlspecialchars($exp['company']) ?>
            </h3>
            <?php foreach ($exp['positions'] as $pos): ?>
                <div class="cv-position">
                    <div class="cv-row">
                        <span class="cv-title"><?= htmlspecialchars($pos['title']) ?></span>
                        <span class="cv-date"><?= formatCVDate($pos['start'], $lang) ?> – <?= $pos['end'] ? formatCVDate($pos['end'], $lang) : $l['present'] ?></span>
                    </div>
                    <?php if (!empty($pos['description'])): ?>
                        <p><?= htmlspecialchars($pos['description']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($pos['tags'])): ?>
                        <p class="cv-tags">
                            <?php foreach ($pos['tags'] as $tag): ?>
                                <span class="chip"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <h2><?= $l['education'] ?></h2>
    <?php foreach ($cv['education'] as $edu): ?>
        <div class="cv-entry">
            <h3><?= htmlspecialchars($edu['school']) ?></h3>
            <div class="cv-row">
                <span class="cv-title"><?= htmlspecialchars($edu['degree']) ?> — <?= htmlspecialchars($edu['field']) ?></span>
                <span class="cv-date"><?= htmlspecialchars($edu['start']) ?> – <?= htmlspecialchars($edu['end']) ?></span>
            </div>
            <?php if (!empty($edu['description'])): ?>
                <p><?= htmlspecialchars($edu['description']) ?></p>
            <?php endif; ?>
            <?php if (!empty($edu['tags'])): ?>
                <p class="cv-tags">
                    <?php foreach ($edu['tags'] as $tag): ?>
                        <span class="chip"><?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <h2><?= $l['certifications'] ?></h2>
    <ul>
        <?php foreach ($cv['certifications'] as $cert): ?>
            <li>
                <strong><?= htmlspecialchars($cert['name']) ?></strong> — <?= htmlspecialchars($cert['issuer']) ?>
                <span class="cv-date">(<?= formatCVDate($cert['date'], $lang) ?>)</span>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2><?= $l['languages'] ?></h2>
    <ul>
        <?php foreach ($cv['languages'] as $language): ?>
            <li><?= htmlspecialchars($language['name']) ?> — <?= htmlspecialchars($language['level']) ?></li>
        <?php endforeach; ?>
    </ul>

    <h2><?= $l['volunteering'] ?></h2>
    <?php foreach ($cv['volunteering'] as $vol): ?>
        <div class="cv-entry">
            <div class="cv-row">
                <span><strong><?= htmlspecialchars($vol['role']) ?></strong> — <?= htmlspecialchars($vol['organization']) ?></span>
                <span class="cv-date"><?= formatCVDate($vol['start'], $lang) ?> – <?= formatCVDate($vol['end'], $lang) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>
