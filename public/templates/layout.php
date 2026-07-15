<?php

declare(strict_types=1);

/**
 * Shared layout template
 * Variables: $pageTitle, $pageDescription, $pageUrl, $pageImage, $pageLang, $pageContent
 * Shell variables: $lang (fallback: $pageLang), $currentPage ('home'|'articles'|'cv'|null)
 *
 * Optional vars carry a `|null` type because not every caller passes them
 * (e.g. the 404 render omits $pageUrl/$pageImage/$currentPage); the `??`
 * fallbacks below and in the body supply the defaults.
 *
 * @var string            $pageTitle
 * @var string|null       $pageDescription
 * @var string|null       $pageUrl
 * @var string|null       $pageImage
 * @var string|null       $pageLang
 * @var string            $pageContent
 * @var list<string>|null $pageStyles
 * @var string|null       $lang
 * @var string|null       $currentPage
 */

$pageLang = $pageLang ?? 'de';
$pageImage = $pageImage ?? '/assets/img/icon.png';
$baseUrl = 'https://nicograef.com';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$fullUrl = $baseUrl . ($pageUrl ?? (is_string($requestUri) ? $requestUri : ''));
$lang = $lang ?? $pageLang;
$currentPage = $currentPage ?? null;
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
    <?php if (($pageUrl ?? '') === '/'): ?>
        <link rel="alternate" hreflang="en" href="<?= $baseUrl ?>/">
        <link rel="alternate" hreflang="de" href="<?= $baseUrl ?>/">
        <link rel="alternate" hreflang="x-default" href="<?= $baseUrl ?>/">
    <?php endif; ?>

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

    <!-- Theme: set data-theme before the stylesheets load to avoid a wrong-theme flash -->
    <script>
        (function () {
            var theme = 'light';
            try {
                if (localStorage.getItem('ng-theme') === 'dark') theme = 'dark';
            } catch (e) {}
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <!-- Favicon & Styles -->
    <link rel="icon" type="image/png" href="/assets/img/icon.png">
    <link rel="preload" href="/assets/fonts/SpaceGrotesk-Bold.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/assets/fonts/Inter-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/css/base.css">
    <?php foreach (($pageStyles ?? []) as $style): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
    <?php endforeach; ?>
    <script src="/assets/js/theme.js" defer></script>

    <title><?= htmlspecialchars($pageTitle) ?></title>
</head>

<body>
    <?php include __DIR__ . '/header.php'; ?>

    <?= $pageContent ?>

    <footer class="site-footer">
        <div class="site-footer-inner">
            <span>&copy; <?= date('Y') ?> Nico Gräf</span>
            <span class="site-footer-links">
                <a href="https://github.com/nicograef" target="_blank" rel="noopener noreferrer">GitHub</a>
                <a href="https://www.linkedin.com/in/nicograef" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                <a href="https://xing.com/profile/Nico_Graef2/" target="_blank" rel="noopener noreferrer">Xing</a>
                <a href="https://medium.com/@nicograef" target="_blank" rel="noopener noreferrer">Medium</a>
                <a href="mailto:graef.nico@gmail.com"><?= $lang === 'de' ? 'E-Mail' : 'Email' ?></a>
            </span>
        </div>
    </footer>
</body>

</html>
