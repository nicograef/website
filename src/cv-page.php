<?php
$basics = $cv['basics'];
$isDE = $lang === 'de';
$present = $isDE ? 'heute' : 'present';
?>
<div class="cv">
    <a href="/" class="back-link">&larr; <?= $isDE ? 'Zum Portfolio' : 'Portfolio' ?></a>

    <header class="cv-header">
        <h1><?= htmlspecialchars($basics['name']) ?></h1>
        <p class="cv-headline"><?= htmlspecialchars($basics['headline']) ?></p>
        <p class="cv-location"><?= htmlspecialchars($basics['location']) ?></p>
    </header>

    <?php if (!empty($basics['summary'])): ?>
        <p class="cv-summary"><?= htmlspecialchars($basics['summary']) ?></p>
    <?php endif; ?>

    <h2><?= $isDE ? 'Berufserfahrung' : 'Experience' ?></h2>
    <?php foreach ($cv['experience'] as $exp): ?>
        <div class="cv-entry">
            <h3>
                <?php if (!empty($exp['website'])): ?>
                    <a href="<?= htmlspecialchars($exp['website']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($exp['company']) ?></a>
                <?php else: ?>
                    <?= htmlspecialchars($exp['company']) ?>
                <?php endif; ?>
            </h3>
            <?php foreach ($exp['positions'] as $pos): ?>
                <div class="cv-position">
                    <div class="cv-row">
                        <span class="cv-title"><?= htmlspecialchars($pos['title']) ?></span>
                        <span class="cv-date"><?= formatCVDate($pos['start'], $lang) ?> – <?= $pos['end'] ? formatCVDate($pos['end'], $lang) : $present ?></span>
                    </div>
                    <?php if (!empty($pos['description'])): ?>
                        <p><?= htmlspecialchars($pos['description']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <h2><?= $isDE ? 'Ausbildung' : 'Education' ?></h2>
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
        </div>
    <?php endforeach; ?>

    <h2><?= $isDE ? 'Zertifizierungen' : 'Certifications' ?></h2>
    <ul>
        <?php foreach ($cv['certifications'] as $cert): ?>
            <li>
                <strong><?= htmlspecialchars($cert['name']) ?></strong> — <?= htmlspecialchars($cert['issuer']) ?>
                <span class="cv-date">(<?= formatCVDate($cert['date'], $lang) ?>)</span>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2><?= $isDE ? 'Sprachen' : 'Languages' ?></h2>
    <ul>
        <?php foreach ($cv['languages'] as $language): ?>
            <li><?= htmlspecialchars($language['name']) ?> — <?= htmlspecialchars($language['level']) ?></li>
        <?php endforeach; ?>
    </ul>

    <?php if (!empty($cv['skills']['top'])): ?>
        <h2><?= $isDE ? 'Schwerpunkte' : 'Focus Areas' ?></h2>
        <p><?= htmlspecialchars(implode(', ', $cv['skills']['top'])) ?></p>
    <?php endif; ?>

    <h2><?= $isDE ? 'Ehrenamt' : 'Volunteering' ?></h2>
    <?php foreach ($cv['volunteering'] as $vol): ?>
        <div class="cv-entry">
            <div class="cv-row">
                <span><strong><?= htmlspecialchars($vol['role']) ?></strong> — <?= htmlspecialchars($vol['organization']) ?></span>
                <span class="cv-date"><?= formatCVDate($vol['start'], $lang) ?> – <?= formatCVDate($vol['end'], $lang) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>
