<?php

/**
 * Shared layout template
 * Variables: $pageTitle, $pageDescription, $pageUrl, $pageImage, $pageLang, $pageContent
 */

$pageLang = $pageLang ?? 'de';
$pageImage = $pageImage ?? '/assets/img/icon.png';
$baseUrl = 'https://nicograef.com';
$fullUrl = $baseUrl . ($pageUrl ?? $_SERVER['REQUEST_URI']);
?>

<!DOCTYPE html>
<html lang="<?= $pageLang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO -->
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? '') ?>">
    <meta name="author" content="Nico Gräf">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= htmlspecialchars($fullUrl) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription ?? '') ?>">
    <meta property="og:image" content="<?= $baseUrl . htmlspecialchars($pageImage) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($fullUrl) ?>">
    <meta property="og:site_name" content="Nico Gräf">
    <meta property="og:locale" content="<?= $pageLang === 'de' ? 'de_DE' : 'en_US' ?>">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription ?? '') ?>">
    <meta name="twitter:image" content="<?= $baseUrl . htmlspecialchars($pageImage) ?>">

    <!-- Favicon & Styles -->
    <link rel="icon" type="image/png" href="/assets/img/icon.png">
    <link rel="preload" href="/assets/fonts/Montserrat-Regular.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="/assets/fonts/Montserrat-Bold.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="/assets/css/main.css">
    <?php if (!empty($extraStyles)): ?>
        <?php foreach ($extraStyles as $style): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <title><?= htmlspecialchars($pageTitle) ?></title>
</head>

<body>
    <?= $pageContent ?>

    <footer>
        <p>&copy; <?= date('Y') ?> <a href="/">Nico Gräf</a></p>
    </footer>
</body>

</html>