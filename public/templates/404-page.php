<?php

/**
 * 404 page template
 * Variables: $lang ('de'|'en')
 */

$isGerman = ($lang ?? 'de') === 'de';
?>

<main class="error-main">
    <div class="glow error-glow-blue"></div>
    <div class="glow glow-amber error-glow-amber"></div>

    <div class="error-content">
        <p class="error-code gradient-text" aria-hidden="true">404</p>
        <h1><?= $isGerman ? 'Diese Seite gibt es nicht.' : 'This page doesn&#8217;t exist.' ?></h1>
        <p class="error-lead">
            <?= $isGerman
                ? 'Vielleicht ein alter Link &mdash; oder ein Tippfehler. Hier geht&#8217;s zur&uuml;ck:'
                : 'Maybe an old link &mdash; or a typo. Here&#8217;s the way back:' ?>
        </p>
        <div class="error-actions">
            <a class="btn-primary" href="/"><?= $isGerman ? 'Zur Startseite' : 'Back to home' ?></a>
            <a class="btn-outline" href="/articles"><?= $isGerman ? 'Alle Artikel' : 'All articles' ?></a>
        </div>
    </div>
</main>
