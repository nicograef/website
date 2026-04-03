<?php
require_once __DIR__ . '/src/render.php';

render(__DIR__ . '/src/404-page.php', [
    'pageTitle' => '404 - Page Not Found | Nico Gräf',
    'pageDescription' => 'The page you are looking for could not be found.',
    'pageLang' => 'en',
]);