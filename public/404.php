<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/render.php';

$lang = detectLang();
$isGerman = $lang === 'de';

render(__DIR__ . '/templates/404-page.php', [
    'pageTitle' => $isGerman
        ? '404 – Diese Seite gibt es nicht | Nico Gräf'
        : '404 – This page doesn’t exist | Nico Gräf',
    'pageDescription' => $isGerman
        ? 'Diese Seite gibt es nicht. Vielleicht ein alter Link — oder ein Tippfehler. Zurück zur Startseite oder zu allen Artikeln.'
        : 'This page doesn’t exist. Maybe an old link — or a typo. Head back to the homepage or browse all articles.',
    'pageLang' => $lang,
    'pageStyles' => ['/assets/css/error.css'],
    'lang' => $lang,
]);
