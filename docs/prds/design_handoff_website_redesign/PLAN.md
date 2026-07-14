# Umsetzungsplan: Redesign im Repo nicograef/website

Zielumgebung: bestehendes PHP-Setup — Templates in `public/templates/`, CSS in `public/assets/css/`, Inhalte in `public/content/` (JSON + Markdown). Kein Framework, kein Build-Step. Der Plan ist so geschnitten, dass jede Phase einzeln committet und reviewt werden kann.

## Phase 0 — Vorbereitung
- [ ] Branch `redesign` anlegen
- [ ] Fonts self-hosten: Space Grotesk (400–700), Inter (400–700), JetBrains Mono (400/500) als woff2 nach `public/assets/fonts/` + `@font-face`-Deklarationen (`font-display: swap`)
- [ ] Design-Referenzen aus diesem Handoff lokal öffnen und als Zielbild danebenlegen

## Phase 1 — Token-Fundament (`base.css` neu)
- [ ] `public/assets/css/base.css` ersetzen: CSS-Variablen aus README („Design Tokens") unter `:root` (Light) und `[data-theme="dark"]`
- [ ] Body-Reset: `background-color: var(--bg)` (WICHTIG: nicht das `background`-Shorthand transitionieren — hängt in Firefox mit var()), `transition: background-color .35s, color .35s`
- [ ] Keyframes `sheen`, `orb`, `rise` + `prefers-reduced-motion`-Block
- [ ] Utility-Klassen definieren: `.eyebrow`, `.btn-primary`, `.btn-outline`, `.card`, `.chip`, `.gradient-text`, `.glow`
- [ ] `a`-Default + Hover-Farben aus der Palette

## Phase 2 — Layout-Shell (`layout.php` / `header.php`)
- [ ] Inline-Script in `<head>` (vor CSS-Link):
  ```html
  <script>document.documentElement.dataset.theme = localStorage.getItem('ng-theme') || 'light';</script>
  ```
- [ ] Header neu: Logo „nico gräf.", Nav (Portfolio/Artikel/Lebenslauf), Theme-Toggle-Button (`aria-label="Theme wechseln"`), Kontakt-Pill; aktive Seite über bestehende Routing-Variable markieren
- [ ] Toggle-JS (einziges JS der Seite, z. B. `assets/js/theme.js`):
  ```js
  document.querySelector('#theme-toggle').addEventListener('click', () => {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    localStorage.setItem('ng-theme', next);
  });
  ```
  Icon ☾/☀ per CSS (`[data-theme="dark"] .icon-moon { display:none }`) statt JS-Rerender
- [ ] Footer neu (Copyright + GitHub/LinkedIn/E-Mail)
- [ ] Alte Header/Nav-Styles entfernen

## Phase 3 — Startseite (`home.php` + `home.css`)
- [ ] Hero: Grid 1.45fr/1fr, Eyebrow, H1 mit `<span class="gradient-text">Nico</span>`, Lead, CTAs, Porträt + Badge + Verlaufs-Linie, 2 Glow-Divs (Werte aus `design/Startseite.dc.html` übernehmen)
- [ ] Blog-Teaser: 3 neueste aus `articles.json` (bestehende PHP-Logik wiederverwenden), Kartenraster `minmax(280px,1fr)`
- [ ] Portfolio: jotti-Featured-Card (fixe Dunkel-Farben, Verlaufs-Topline, Chips), 2 Job-Karten, „Frühe Projekte"-Grid aus `projects.json` (4 Einträge mit Bildern; echte Projekt-URLs verlinken)
- [ ] Entrance-Animationen (`rise` mit Stagger) nur im Hero
- [ ] Responsive: unter 900px Hero einspaltig (Foto über Text), Grids stapeln

## Phase 4 — Blog (`overview.php` + `overview.css`)
- [ ] Kopf (Eyebrow/H1/Intro, 820px Spalte)
- [ ] Liste aus `articles.json`: nach Jahr gruppieren, Zeilen-Grid `110px 1fr auto`, Hover ganze Zeile
- [ ] Datumsformat „13. Juli" (Jahr steckt in der Gruppe)

## Phase 5 — Artikelseite (Markdown-Rendering + `article.css`)
- [ ] Lese-Layout 760px: Backlink, Meta-Zeile (Kategorie · Datum · Lesezeit aus Wortzahl/200), H1, Lead
- [ ] Prose-Styles auf die Ausgabe des bestehenden Markdown-Renderers mappen: p/h2/ul/code/pre/a/strong gemäß `design/Artikel.dc.html` (Werte in README)
- [ ] Author-Card am Artikelende (Foto, Name, Kurzbio, „Alle Artikel →")
- [ ] Mit dem längsten Artikel (Warenkorb, 23 KB) gegentesten

## Phase 6 — Lebenslauf (`cv-page.php` + `cv.css`)
- [ ] Kopf: Foto 120px, Name, Headline, 3 Kontakt-Chips, Summary aus `cv.json` (`summary_de`)
- [ ] Timeline aus `experience[]`: Dot aktiv (Kobalt + Halo, wenn `end === null`) vs. beendet (`--line2`); Zeitraum deutsch formatieren („Okt 2025 – heute"); Tags als Chips
- [ ] Sidebar: education / languages / certifications / volunteering
- [ ] Grid 1.7fr/1fr, unter 900px einspaltig (Sidebar unter Timeline)

## Phase 7 — 404 + Politur
- [ ] 404-Template im neuen Design, Server liefert Status 404
- [ ] Meta/OG-Tags je Seite prüfen, Favicon gegen neues Kobalt prüfen
- [ ] Tote Styles/Templates des alten Designs löschen

## Phase 8 — QA-Checkliste
- [ ] Theme-Toggle auf jeder Seite; Wahl übersteht Reload + Seitenwechsel; kein FOUC
- [ ] Beide Themes: Kontraste, Kartenränder, jotti-Card (im Dark mit Border)
- [ ] Firefox-Gegentest Theme-Transition (background-color, nicht background!)
- [ ] `prefers-reduced-motion`: keine sheen/orb/rise
- [ ] Responsive 360px / 768px / 1280px / 1920px
- [ ] Alle 9 Artikel rendern; interne Artikel-Links (`/articles/...`) funktionieren
- [ ] Lighthouse ≥ 90 (Perf/A11y/SEO); Bilder lazy außer Hero
- [ ] EN-Variante nicht kaputt (falls Rollout später: mindestens erreichbar lassen)

## Hinweise für Claude Code
- Die `.dc.html`-Dateien in `design/` sind die pixelgenaue Referenz — bei Unklarheit dort die Inline-Styles nachschlagen, nicht raten
- Farb-/Typo-/Abstands-Werte niemals erfinden: alles steht in README „Design Tokens" bzw. in den Referenzdateien
- `color-mix()` für die Verlaufsstops g2/g3 verwenden; Fallback-Hexwerte: g2 ≈ `#7a4bd9`, g3 ≈ `#2a91d3`
- Bestehende Content-Pipeline (JSON/Markdown) nicht umbauen — nur Templates + CSS
