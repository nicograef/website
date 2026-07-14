<?php

/**
 * Slim site navigation, included by layout.php on every page.
 * Variables: $lang ('de'|'en'), $currentPage ('home'|'articles'|'cv'|null)
 */

$lang = $lang ?? 'de';
$currentPage = $currentPage ?? null;
$isGerman = $lang === 'de';
?>
<header class="site-header">
    <div class="site-header-inner">
        <a class="logo" href="/">nico gräf<span class="logo-dot">.</span></a>
        <nav class="site-nav" aria-label="<?= $isGerman ? 'Hauptnavigation' : 'Main navigation' ?>">
            <a href="/#portfolio"<?= $currentPage === 'home' ? ' aria-current="page"' : '' ?>>Portfolio</a>
            <a href="/articles"<?= $currentPage === 'articles' ? ' aria-current="page"' : '' ?>><?= $isGerman ? 'Artikel' : 'Articles' ?></a>
            <a href="/cv"<?= $currentPage === 'cv' ? ' aria-current="page"' : '' ?>><?= $isGerman ? 'Lebenslauf' : 'CV' ?></a>
            <button id="theme-toggle" type="button" aria-label="<?= $isGerman ? 'Theme wechseln' : 'Toggle theme' ?>"></button>
            <a class="contact-pill" href="mailto:graef.nico@gmail.com"><?= $isGerman ? 'Kontakt' : 'Contact' ?></a>
        </nav>
    </div>
</header>
