<?php
require_once __DIR__ . '/src/lang.php';
require_once __DIR__ . '/src/render.php';

$lang = detectLang();
$isGerman = $lang === 'de';

render(__DIR__ . '/src/404-page.php', [
    'pageTitle' => $isGerman
        ? '404 - Seite nicht gefunden | Nico Gräf'
        : '404 - Page Not Found | Nico Gräf',
    'pageDescription' => $isGerman
        ? 'Die gesuchte Seite konnte nicht gefunden werden.'
        : 'The page you are looking for could not be found.',
    'pageLang' => $lang,
    'pageStyles' => ['/assets/css/error.css'],
]);
