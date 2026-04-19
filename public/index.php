<?php
require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/articles.php';
require_once __DIR__ . '/lib/projects.php';
require_once __DIR__ . '/lib/render.php';

$lang = detectLang();
$projects = loadProjects($lang);
$isGerman = $lang === 'de';

render(__DIR__ . '/templates/home.php', [
    'pageTitle' => 'Nico Gräf – Software Engineer',
    'pageDescription' => $isGerman
        ? 'Portfolio von Nico Gräf, Software Engineer mit Fokus auf Webentwicklung und moderne Technologien.'
        : 'Portfolio of Nico Gräf, a Software Engineer specializing in web development and modern technologies.',
    'pageUrl' => '/',
    'pageLang' => $lang,
    'pageImage' => '/assets/img/nico-social.jpg',
    'pageStyles' => ['/assets/css/home.css'],
    'lang' => $lang,
    'currentPage' => 'home',
    'projects' => $projects,
]);