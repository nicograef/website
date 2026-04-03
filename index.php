<?php
require_once 'src/lang.php';
require_once 'src/articles.php';
include 'src/projects.php';
require_once 'src/render.php';

$lang = detectLang();
$projects = loadProjects($lang);
$isGerman = $lang === 'de';

render(__DIR__ . '/src/home.php', [
    'pageTitle' => 'Nico Gräf – Software Engineer',
    'pageDescription' => $isGerman
        ? 'Portfolio von Nico Gräf, Software Engineer mit Fokus auf Webentwicklung und moderne Technologien.'
        : 'Portfolio of Nico Gräf, a Software Engineer specializing in web development and modern technologies.',
    'pageUrl' => '/',
    'pageLang' => $lang,
    'pageImage' => '/assets/img/nico-social.jpg',
    'lang' => $lang,
    'projects' => $projects,
]);