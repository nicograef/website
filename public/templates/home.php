<?php

declare(strict_types=1);

/**
 * Homepage template
 * Variables: $lang ('de'|'en'), $latestArticles (3 newest articles), $earlyProjects (all projects with 'early' flag)
 *
 * @var string         $lang
 * @var list<Article>  $latestArticles
 * @var list<Project>  $earlyProjects
 */

$isGerman = $lang === 'de';
?>
<main>
    <section class="hero-wrap">
        <div class="glow hero-glow-blue" aria-hidden="true"></div>
        <div class="glow glow-amber hero-glow-amber" aria-hidden="true"></div>
        <div class="container hero">
            <div>
                <p class="eyebrow hero-eyebrow">Senior Software Engineer · Freiburg im Breisgau</p>
                <h1><?= $isGerman ? 'Hi, ich bin' : 'Hi, I&rsquo;m' ?> <span class="gradient-text">Nico</span>.</h1>
                <?php if ($isGerman): ?>
                    <p class="hero-lead">Seit über zehn Jahren baue ich Software, die Probleme wirklich löst: als Freelancer, Fullstack-Entwickler im Konzern und Teamleiter im Startup. Aktuell: <strong>jotti</strong>, ein Kassensystem für Vereine.</p>
                <?php else: ?>
                    <p class="hero-lead">For over ten years I&rsquo;ve been building software that actually solves problems: as a freelancer, as a fullstack developer at a large company, and as a team lead at a startup. Currently: <strong>jotti</strong>, a point-of-sale system for clubs.</p>
                <?php endif; ?>
                <div class="hero-ctas">
                    <a class="btn-primary" href="#portfolio"><?= $isGerman ? 'Portfolio ansehen' : 'View portfolio' ?></a>
                    <a class="btn-outline" href="/cv"><?= $isGerman ? 'Lebenslauf' : 'CV' ?></a>
                </div>
            </div>
            <div class="hero-portrait">
                <img src="/assets/img/nico-social.jpg" alt="Nico Gräf" width="1118" height="980" loading="eager" fetchpriority="high">
                <div class="hero-badge"><strong>10+ <?= $isGerman ? 'Jahre' : 'years' ?></strong> <span><?= $isGerman ? 'Software-Entwicklung' : 'of software development' ?></span></div>
                <div class="hero-underline" aria-hidden="true"></div>
            </div>
        </div>
    </section>

    <section id="artikel" class="container blog-teaser">
        <p class="eyebrow section-eyebrow">Blog</p>
        <div class="section-head">
            <h2><?= $isGerman ? 'Neueste Artikel' : 'Latest articles' ?></h2>
            <a class="section-link" href="/articles"><?= $isGerman ? 'Alle Artikel' : 'All articles' ?> →</a>
        </div>
        <div class="article-grid">
            <?php foreach ($latestArticles as $article): ?>
                <a class="card article-card" href="/articles/<?= htmlspecialchars($article['slug']) ?>">
                    <span class="article-date"><?= htmlspecialchars(formatArticleDate($article['date'], $lang)) ?></span>
                    <span class="article-title"><?= htmlspecialchars($article['title']) ?></span>
                    <span class="article-description"><?= htmlspecialchars($article['description']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="portfolio" class="container portfolio">
        <p class="eyebrow section-eyebrow">Portfolio</p>
        <h2 class="section-title"><?= $isGerman ? 'Woran ich arbeite' : 'What I&rsquo;m working on' ?></h2>

        <article class="featured-card">
            <div>
                <div class="featured-chips">
                    <span class="featured-chip">Go</span>
                    <span class="featured-chip">TypeScript</span>
                    <span class="featured-chip">React</span>
                    <span class="featured-chip">PostgreSQL</span>
                    <span class="featured-chip">Event Sourcing</span>
                    <span class="featured-chip">DDD</span>
                    <span class="featured-chip">KassenSichV</span>
                </div>
                <h3>jotti <span class="featured-since">/ <?= $isGerman ? 'seit' : 'since' ?> 2025</span></h3>
                <?php if ($isGerman): ?>
                    <p>Kostenloses mobiles Kassensystem für Vereine und gemeinnützige Organisationen: Bestellungen, Bons und Tagesabschluss direkt auf dem Smartphone, ausgelegt auf die KassenSichV. Servicekräfte kassieren Tisch für Tisch im Browser, self-hosted via Docker und abgesichert durch eine lückenlose, event-basierte Historie.</p>
                <?php else: ?>
                    <p>Free mobile point-of-sale system for clubs and non-profit organizations: orders, receipts, and daily close right on the smartphone, designed for KassenSichV. Service staff take payment table by table in the browser, self-hosted via Docker and backed by an audit-proof, event-sourced history.</p>
                <?php endif; ?>
                <a class="featured-link" href="https://jotti.rocks" target="_blank" rel="noopener noreferrer"><?= $isGerman ? 'Website besuchen' : 'Visit website' ?> →</a>
            </div>
            <img src="/assets/img/jotti.png" alt="jotti Screenshot" width="1516" height="854" loading="lazy">
        </article>

        <div class="job-cards">
            <article class="card job-card">
                <h3>Haufe Akademie <span class="job-since">/ <?= $isGerman ? 'seit' : 'since' ?> 2024</span></h3>
                <?php if ($isGerman): ?>
                    <p>Event-getriebene Serverless-Systeme auf AWS und React-Frontends im Scrum-Team des Weiterbildungsbereichs der Haufe Group.</p>
                <?php else: ?>
                    <p>Event-driven serverless systems on AWS and React frontends in a Scrum team of Haufe Group&rsquo;s training and education division.</p>
                <?php endif; ?>
                <div class="job-tags">
                    <span class="chip">AWS</span>
                    <span class="chip">Serverless</span>
                    <span class="chip">TypeScript</span>
                    <span class="chip">Event-Driven</span>
                    <span class="chip">React</span>
                </div>
            </article>
            <article class="card job-card">
                <h3>Idana <span class="job-since">/ 2020&ndash;2024</span></h3>
                <?php if ($isGerman): ?>
                    <p>Vom Fullstack-Entwickler zum Team Lead: Führung von fünf Entwicklern und Migration des Backends auf eine Go-Architektur.</p>
                <?php else: ?>
                    <p>From fullstack developer to team lead: leading a team of five developers and migrating the backend to a Go architecture.</p>
                <?php endif; ?>
                <div class="job-tags">
                    <span class="chip">Go</span>
                    <span class="chip">TypeScript</span>
                    <span class="chip">Vue.js</span>
                    <span class="chip">Google Cloud</span>
                    <span class="chip">E2EE</span>
                </div>
            </article>
        </div>

        <h3 class="early-heading"><?= $isGerman ? 'Frühe Projekte &amp; Experimente' : 'Early projects &amp; experiments' ?></h3>
        <div class="early-grid">
            <?php foreach ($earlyProjects as $project): ?>
                <?php
                [$projectName, $projectYear] = array_pad(explode(' / ', $project['title'], 2), 2, '');
                $projectTags = array_slice($project['tags'], 0, 3);
                ob_start();
                ?>
                <img src="<?= htmlspecialchars($project['image']) ?>" alt="<?= htmlspecialchars($projectName) ?>" loading="lazy">
                <span class="early-body">
                    <span class="early-name"><?= htmlspecialchars($projectName) ?><?php if ($projectYear !== ''): ?> <span class="early-year"><?= htmlspecialchars($projectYear) ?></span><?php endif; ?></span>
                    <span class="early-desc"><?= htmlspecialchars($project['description']) ?></span>
                    <span class="early-tags">
                        <?php foreach ($projectTags as $tag): ?><span class="chip"><?= htmlspecialchars($tag) ?></span><?php endforeach; ?>
                    </span>
                </span>
                <?php $projectBody = ob_get_clean(); ?>
                <?php if (!empty($project['linkUrl'])): ?>
                    <a class="card early-card" href="<?= htmlspecialchars($project['linkUrl']) ?>" target="_blank" rel="noopener noreferrer"><?= $projectBody ?></a>
                <?php else: ?>
                    <div class="card early-card"><?= $projectBody ?></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
</main>
