<?php
require_once __DIR__ . '/includes/render.php';

render(__DIR__ . '/templates/404.php', [
    'pageTitle' => '404 - Page Not Found | Nico Gräf',
    'pageDescription' => 'The page you are looking for could not be found.',
    'pageLang' => 'en',
    'extraStyles' => ['/assets/css/404.css'],
]);