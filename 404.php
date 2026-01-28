<?php
// Layout data
$pageTitle = '404 - Page Not Found | Nico Gräf';
$pageDescription = 'The page you are looking for could not be found.';
$pageLang = 'en';
$extraStyles = [];

ob_start();
?>
<style>
    .error-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 80vh;
        text-align: center;
        padding: 20px;
    }
</style>
<div class="error-container" id="main-content">
    <img src="/assets/img/404.webp" alt="404 - Page not found" style="max-width: 400px; width: 100%; height: auto; margin-bottom: 20px;">
    <p>
        <a href="/">back to my portfolio</a>
    </p>
</div>
<?php
$pageContent = ob_get_clean();
include __DIR__ . '/templates/layout.php';
?>