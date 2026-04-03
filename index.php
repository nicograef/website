<?php
require_once 'includes/lang.php';
require_once 'includes/articles.php';
include 'templates/projects.php';
require_once 'includes/render.php';

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
    'extraStyles' => ['/assets/css/main.css'],
    'lang' => $lang,
    'projects' => $projects,
]);