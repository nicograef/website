<?php

/**
 * CV page template: head (photo, name, contact chips), summary,
 * experience timeline (left) and sidebar (right).
 * Variables: $lang ('de'|'en'), $l (cvLabels), $cv (localized cv.json)
 */

$isGerman = $lang === 'de';
$basics = $cv['basics'];

$githubDisplay = preg_replace('#^https?://(www\.)?#', '', $basics['github']);
$linkedinDisplay = preg_replace('#^https?://(www\.)?#', '', $basics['linkedin']);

/* Presentation only: the design bolds the product name inside the summary. */
$summaryHtml = str_replace('jotti', '<strong>jotti</strong>', htmlspecialchars($basics['summary']));

$stationCount = count($cv['experience']);
?>
<main class="cv">
    <div class="cv-hero">
        <div class="glow cv-glow-blue" aria-hidden="true"></div>
        <div class="glow glow-amber cv-glow-amber" aria-hidden="true"></div>
        <div class="cv-container cv-head">
            <img class="cv-photo" src="/assets/img/nico-social.jpg" alt="<?= htmlspecialchars($basics['name']) ?>" width="120" height="120">
            <div class="cv-head-text">
                <h1><?= htmlspecialchars($basics['name']) ?></h1>
                <p class="cv-headline"><?= htmlspecialchars($basics['headline']) ?> · <?= htmlspecialchars($basics['location']) ?></p>
                <div class="cv-contacts">
                    <a href="mailto:<?= htmlspecialchars($basics['email']) ?>"><?= htmlspecialchars($basics['email']) ?></a>
                    <a href="<?= htmlspecialchars($basics['github']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($githubDisplay) ?></a>
                    <a href="<?= htmlspecialchars($basics['linkedin']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($linkedinDisplay) ?></a>
                </div>
            </div>
        </div>
        <div class="cv-container cv-summary-wrap">
            <p class="cv-summary"><?= $summaryHtml ?></p>
        </div>
    </div>

    <div class="cv-container cv-grid">
        <section class="cv-experience">
            <h2><?= htmlspecialchars($l['experience']) ?></h2>
            <div class="cv-timeline">
                <?php foreach ($cv['experience'] as $i => $exp): ?>
                    <?php
                    $ongoing = empty($exp['end']);
                    $isLast = $i === $stationCount - 1;
                    ?>
                    <div class="cv-station<?= $ongoing ? ' is-ongoing' : '' ?>">
                        <div class="cv-marker" aria-hidden="true">
                            <span class="cv-dot"></span>
                            <?php if (!$isLast): ?>
                                <span class="cv-connector"></span>
                            <?php endif; ?>
                        </div>
                        <div class="cv-station-body<?= $isLast ? ' is-last' : '' ?>">
                            <p class="cv-period"><?= htmlspecialchars(formatCVDate($exp['start'], $lang)) ?> – <?= $ongoing ? htmlspecialchars($l['present']) : htmlspecialchars(formatCVDate($exp['end'], $lang)) ?></p>
                            <h3><?= htmlspecialchars($exp['title']) ?> · <?= htmlspecialchars($exp['company']) ?></h3>
                            <p class="cv-location"><?= htmlspecialchars($exp['location']) ?></p>
                            <p class="cv-description"><?= htmlspecialchars($exp['description']) ?></p>
                            <div class="cv-tags">
                                <?php foreach ($exp['tags'] as $tag): ?>
                                    <span class="chip"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <aside class="cv-sidebar">
            <section>
                <h2><?= htmlspecialchars($l['education']) ?></h2>
                <?php foreach ($cv['education'] as $edu): ?>
                    <?php
                    /* Bare-year ranges use an unspaced en dash („2013–2020", like volunteering);
                       ranges with a word endpoint (e.g. "heute") keep the spaced form. */
                    $bareYears = strlen($edu['start']) === 4 && !empty($edu['end']) && strlen($edu['end']) === 4;
                    $eduEnd = empty($edu['end']) ? $l['present'] : formatCVDate($edu['end'], $lang);
                    ?>
                    <div class="card cv-education-card">
                        <p class="cv-period"><?= htmlspecialchars(formatCVDate($edu['start'], $lang) . ($bareYears ? '–' : ' – ') . $eduEnd) ?></p>
                        <p class="cv-degree"><?= htmlspecialchars($edu['degree']) ?> <?= htmlspecialchars($edu['field']) ?></p>
                        <p class="cv-school"><?= htmlspecialchars($edu['school']) ?> · <?= htmlspecialchars($l['grade']) ?> <?= htmlspecialchars($isGerman ? str_replace('.', ',', $edu['grade']) : $edu['grade']) ?></p>
                        <p class="cv-focus"><?= htmlspecialchars($edu['description']) ?></p>
                    </div>
                <?php endforeach; ?>
            </section>

            <section>
                <h2><?= htmlspecialchars($l['languages']) ?></h2>
                <div class="cv-languages">
                    <?php foreach ($cv['languages'] as $language): ?>
                        <div class="cv-language">
                            <span class="cv-language-name"><?= htmlspecialchars($language['name']) ?></span>
                            <span class="cv-language-level"><?= htmlspecialchars($language['level']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section>
                <h2><?= htmlspecialchars($l['certifications']) ?></h2>
                <div class="cv-list">
                    <?php foreach ($cv['certifications'] as $cert): ?>
                        <div>
                            <p class="cv-item-name"><?= htmlspecialchars($cert['name']) ?></p>
                            <p class="cv-item-meta"><?= htmlspecialchars($cert['issuer']) ?> · <?= htmlspecialchars(substr($cert['date'], 0, 4)) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section>
                <h2><?= htmlspecialchars($l['volunteering']) ?></h2>
                <div class="cv-list">
                    <?php foreach ($cv['volunteering'] as $vol): ?>
                        <?php
                        $volBareYears = strlen($vol['start']) === 4 && !empty($vol['end']) && strlen($vol['end']) === 4;
                        $volEnd = empty($vol['end']) ? $l['present'] : $vol['end'];
                        ?>
                        <div>
                            <p class="cv-item-name"><?= htmlspecialchars($vol['role']) ?></p>
                            <p class="cv-item-meta"><?= htmlspecialchars($vol['organization']) ?> · <?= htmlspecialchars($vol['start'] . ($volBareYears ? '–' : ' – ') . $volEnd) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </aside>
    </div>
</main>
