<?php
require_once 'src/lang.php';
require_once 'src/cv.php';
require_once 'src/render.php';

$lang = detectLang();
$cv = loadCV($lang);
$isGerman = $lang === 'de';

render(__DIR__ . '/src/cv-page.php', [
    'pageTitle' => $cv['basics']['name'] . ' – CV',
    'pageDescription' => $isGerman
        ? 'Lebenslauf von ' . $cv['basics']['name'] . ', ' . $cv['basics']['headline']
        : 'CV of ' . $cv['basics']['name'] . ', ' . $cv['basics']['headline'],
    'pageUrl' => '/cv',
    'pageLang' => $lang,
    'pageImage' => '/assets/img/nico-social.jpg',
    'lang' => $lang,
    'cv' => $cv,
]);
