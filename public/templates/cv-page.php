<?php require __DIR__ . '/header.php'; ?>

<?php $basics = $cv['basics']; ?>

<main id="cv" class="cv">
    <section class="content-section">
        <h2><?= $lang === 'de' ? 'Über mich' : 'About Me' ?></h2>
        <p class="cv-summary"><?= htmlspecialchars($basics['summary']) ?></p>
    </section>

    <section class="content-section">
        <h2><?= $l['experience'] ?></h2>
        <div>
            <?php foreach ($cv['experience'] as $exp): ?>
                <div class="cv-entry">
                    <h3><?= htmlspecialchars($exp['title']) ?> @ <?= htmlspecialchars($exp['company']) ?></h3>
                    <p><?= formatCVDate($exp['start'], $lang) ?> – <?= $exp['end'] ? formatCVDate($exp['end'], $lang) : $l['present'] ?></p>
                    <p><?= htmlspecialchars($exp['description']) ?></p>
                    <p class="cv-tags">
                        <?php foreach ($exp['tags'] as $tag): ?>
                            <span class="tag"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="content-section">
        <h2><?= $l['education'] ?></h2>
        <div>
            <?php foreach ($cv['education'] as $edu): ?>
                <div class="cv-entry">
                    <h3><?= htmlspecialchars($edu['degree']) ?> <?= htmlspecialchars($edu['field']) ?> @ <?= htmlspecialchars($edu['school']) ?></h3>
                    <p><?= formatCVDate($edu['start'], $lang) ?> – <?= $edu['end'] ? formatCVDate($edu['end'], $lang) : $l['present'] ?></p>
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
                    <li><strong><?= htmlspecialchars($language['name']) ?></strong> — <?= htmlspecialchars($language['level']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    
    <section class="content-section">
        <h2><?= $l['volunteering'] ?></h2>
        <div>
            <ul>
                <?php foreach ($cv['volunteering'] as $vol): ?>
                    <li><strong><?= htmlspecialchars($vol['role']) ?></strong> — <?= htmlspecialchars($vol['organization']) ?>, <?= formatCVDate($vol['start'], $lang) ?> – <?= $vol['end'] ? formatCVDate($vol['end'], $lang) : $l['present'] ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section class="content-section">
        <h2><?= $l['certifications'] ?></h2>
        <div>
            <ul>
                <?php foreach ($cv['certifications'] as $cert): ?>
                    <li><strong><?= htmlspecialchars($cert['name']) ?></strong> — <?= htmlspecialchars($cert['issuer']) ?>, <?= formatCVDate($cert['date'], $lang) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</main>