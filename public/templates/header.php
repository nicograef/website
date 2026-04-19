<?php
$navPortfolio = $currentPage === 'home' ? '#portfolio' : '/';
$navCv        = $currentPage === 'cv'   ? '#cv'       : '/cv';
?>
<header>
    <img src="/assets/img/nico-social.jpg" alt="Nico Gräf" title="Nico Gräf" loading="eager" class="profile-picture" />
    <h1>Nico Gräf</h1>
    <p><?= $lang === 'de' ? 'Software Engineer aus Freiburg im Breisgau' : 'Software Engineer from Freiburg, Germany' ?></p>
    <nav aria-label="<?= $lang === 'de' ? 'Hauptnavigation' : 'Main navigation' ?>">
        <a href="<?= $navPortfolio ?>"<?= $currentPage === 'home' ? ' aria-current="page"' : '' ?> aria-label="<?= $lang === 'de' ? 'Mein Portfolio ansehen' : 'View my portfolio' ?>">Portfolio</a>
        <a href="<?= $navCv ?>"<?= $currentPage === 'cv' ? ' aria-current="page"' : '' ?> aria-label="<?= $lang === 'de' ? 'Meinen Lebenslauf ansehen' : 'View my CV' ?>">CV</a>
        <a href="/articles"<?= $currentPage === 'articles' ? ' aria-current="page"' : '' ?> aria-label="<?= $lang === 'de' ? 'Meine Artikel lesen' : 'Read my articles' ?>">Blog</a>
        <a href="https://github.com/nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs GitHub-Profil besuchen' : "Visit Nico Gräf's GitHub profile" ?>">Github</a>
        <a href="https://linkedin.com/in/nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs LinkedIn-Profil besuchen' : "Visit Nico Gräf's LinkedIn profile" ?>">LinkedIn</a>
        <a href="https://xing.com/profile/Nico_Graef2/" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs Xing-Profil besuchen' : "Visit Nico Gräf's Xing profile" ?>">Xing</a>
        <a href="https://medium.com/@nicograef" target="_blank" rel="noopener noreferrer" aria-label="<?= $lang === 'de' ? 'Nico Gräfs Medium-Artikel lesen' : "Visit Nico Gräf's Medium articles" ?>">Medium</a>
    </nav>
</header>
