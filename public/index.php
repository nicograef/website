<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/articles.php';
require_once __DIR__ . '/lib/projects.php';
require_once __DIR__ . '/lib/render.php';

$lang = detectLang();
$isGerman = $lang === 'de';

$earlyProjects = [];
foreach (loadProjects($lang) as $project) {
    if (!empty($project['early'])) {
        $earlyProjects[] = $project;
    }
}

render(__DIR__ . '/templates/home.php', [
    'pageTitle' => $isGerman
        ? 'Nico Gräf – Senior Softwareentwickler'
        : 'Nico Gräf – Senior Software Engineer',
    'pageDescription' => $isGerman
        ? 'Portfolio von Nico Gräf, Senior Softwareentwickler aus Freiburg im Breisgau. Aktuell: jotti, ein Kassensystem für Vereine — dazu Artikel über Softwarearchitektur und Lebenslauf.'
        : 'Portfolio of Nico Gräf, senior software engineer from Freiburg im Breisgau. Currently building jotti, a point-of-sale system for clubs — plus articles on software architecture and a CV.',
    'pageUrl' => '/',
    'pageLang' => $lang,
    'pageImage' => '/assets/img/nico-social.jpg',
    'pageStyles' => ['/assets/css/home.css'],
    'lang' => $lang,
    'currentPage' => 'home',
    'latestArticles' => getLatestArticles(3),
    'earlyProjects' => $earlyProjects,
]);
