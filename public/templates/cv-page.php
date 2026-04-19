<?php require __DIR__ . '/header.php'; ?>

<?php $basics = $cv['basics']; ?>

<main id="cv" class="cv">
    <section class="content-section">
        <h2><?= $lang === 'de' ? 'Über mich' : 'About Me' ?></h2>
        <div>
            <p class="cv-summary"><?= htmlspecialchars($basics['summary']) ?></p>
        </div>
    </section>

    <section class="content-section">
        <h2><?= $l['experience'] ?></h2>
        <div>
            <?php foreach ($cv['experience'] as $exp): ?>
                <div class="cv-entry">
                    <h3><?= htmlspecialchars($exp['company']) ?></h3>
                    <div class="cv-position">
                        <div class="cv-row">
                            <span class="cv-title"><?= htmlspecialchars($exp['title']) ?></span>
                            <span class="cv-date"><?= formatCVDate($exp['start'], $lang) ?> – <?= $exp['end'] ? formatCVDate($exp['end'], $lang) : $l['present'] ?></span>
                        </div>
                        <p><?= htmlspecialchars($exp['description']) ?></p>
                        <p class="cv-tags">
                            <?php foreach ($exp['tags'] as $tag): ?>
                                <span class="tag"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="content-section">
        <h2><?= $l['education'] ?></h2>
        <div>
            <?php foreach ($cv['education'] as $edu): ?>
                <div class="cv-entry">
                    <h3><?= htmlspecialchars($edu['school']) ?></h3>
                    <div class="cv-row">
                        <span class="cv-title"><?= htmlspecialchars($edu['degree']) ?> — <?= htmlspecialchars($edu['field']) ?></span>
                        <span class="cv-date"><?= htmlspecialchars($edu['start']) ?> – <?= htmlspecialchars($edu['end']) ?></span>
                    </div>
                    <p><?= htmlspecialchars($edu['description']) ?></p>
                    <p class="cv-tags">
                        <?php foreach ($edu['tags'] as $tag): ?>
                            <span class="tag"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="content-section">
        <h2><?= $l['languages'] ?></h2>
        <div>
            <ul>
                <?php foreach ($cv['languages'] as $language): ?>
                    <li><?= htmlspecialchars($language['name']) ?> — <?= htmlspecialchars($language['level']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section class="content-section">
        <h2><?= $l['volunteering'] ?></h2>
        <div>
            <?php foreach ($cv['volunteering'] as $vol): ?>
                <div class="cv-entry">
                    <div class="cv-row">
                        <span><strong><?= htmlspecialchars($vol['role']) ?></strong> — <?= htmlspecialchars($vol['organization']) ?></span>
                        <span class="cv-date"><?= formatCVDate($vol['start'], $lang) ?> – <?= formatCVDate($vol['end'], $lang) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="content-section">
        <h2><?= $l['certifications'] ?></h2>
        <div>
            <ul>
                <?php foreach ($cv['certifications'] as $cert): ?>
                    <li>
                        <strong><?= htmlspecialchars($cert['name']) ?></strong> — <?= htmlspecialchars($cert['issuer']) ?>
                        <span class="cv-date">(<?= formatCVDate($cert['date'], $lang) ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</main>