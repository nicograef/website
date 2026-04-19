<?php
require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/cv.php';
require_once __DIR__ . '/lib/render.php';

$lang = detectLang();
$cv = loadCV($lang);
$l = cvLabels($lang);
$isGerman = $lang === 'de';

render(__DIR__ . '/templates/cv-page.php', [
    'pageTitle' => $cv['basics']['name'] . ' – CV',
    'pageDescription' => $isGerman
        ? 'Lebenslauf von ' . $cv['basics']['name'] . ', ' . $cv['basics']['headline']
        : 'CV of ' . $cv['basics']['name'] . ', ' . $cv['basics']['headline'],
    'pageUrl' => '/cv',
    'pageLang' => $lang,
    'pageImage' => '/assets/img/nico-social.jpg',
    'pageStyles' => ['/assets/css/cv.css'],
    'lang' => $lang,
    'currentPage' => 'cv',
    'l' => $l,
    'cv' => $cv,
]);
