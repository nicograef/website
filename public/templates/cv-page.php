<?php $basics = $cv['basics']; ?>

<header>
    <img src="/assets/img/nico-social.jpg" alt="Nico Gräf" title="Nico Gräf" loading="eager" class="profile-picture" />
    <h1 style="margin-bottom: 0">Nico Gräf</h1>
    <p><?= $lang === 'de' ? 'Software Engineer aus Freiburg' : 'Software Engineer from Freiburg, Germany' ?></p>
    <br />
    <p>
        <a href="/" aria-label="<?= $lang === 'de' ? 'Zum Portfolio' : 'Portfolio' ?>">Portfolio</a>
        <a href="https://github.com/nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs GitHub-Profil besuchen' : "Visit Nico Gräf's GitHub profile" ?>">Github</a>
        <a href="https://linkedin.com/in/nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs LinkedIn-Profil besuchen' : "Visit Nico Gräf's LinkedIn profile" ?>">LinkedIn</a>
        <a href="https://xing.com/profile/Nico_Graef2/" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs Xing-Profil besuchen' : "Visit Nico Gräf's Xing profile" ?>">Xing</a>
        <a href="https://medium.com/@nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs Medium-Artikel lesen' : "Visit Nico Gräf's Medium articles" ?>">Medium</a>
        <a href="/articles" aria-label="<?= $lang === 'de' ? 'Meine Artikel lesen' : 'Read my articles' ?>"><?= $lang === 'de' ? 'Artikel' : 'Articles' ?></a>
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