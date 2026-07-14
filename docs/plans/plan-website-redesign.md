# Plan: Redesign nicograef.com

> Source PRD: docs/prds/prd-website-redesign.md
> Design-Referenz: docs/prds/design_handoff_website_redesign/ (README = Token-Tabellen, `design/*.dc.html` = pixelgenaue Vorgabe)

## Goal

Komplettes visuelles Redesign aller fünf Seiten (Startseite, Blog-Übersicht,
Artikel-Detail, Lebenslauf, 404) nach dem Design-Handoff: warmer Papierton mit
Kobalt-Akzent, Space Grotesk/Inter/JetBrains Mono, Light/Dark-Theme mit Toggle
und localStorage-Persistenz, Sprach-Default kippt auf Deutsch. Alle URLs,
Inhalte und die Content-Pipeline (JSON + Markdown) bleiben unverändert; kein
Framework, kein Build-Step, PHP 7.4. Umsetzung auf einem `redesign`-Branch,
ein Rollout am Ende.

Bei jeder Detailfrage zu Farben, Abständen, Typografie oder Copy gilt: im
Handoff nachschlagen (README „Design Tokens" bzw. Inline-Styles der
`.dc.html`-Dateien), nie raten.

## Architectural decisions

Durable Entscheidungen, die für alle Phasen gelten:

- **Routen/URLs**: unverändert — `/`, `/articles`, `/articles/{slug}`, `/cv`,
  `/sitemap.xml`; 404 weiterhin über `.htaccess` `ErrorDocument` (prod) bzw.
  `router.php` (dev). `sitemap.php` und `robots.txt` werden nicht angefasst.
- **Shell**: `public/templates/layout.php` bindet künftig `header.php` und den
  neuen Footer selbst ein — damit bekommen auch Artikel- und 404-Seiten Nav und
  Footer. Die Seiten-Templates enthalten kein `require header.php` mehr. Header
  braucht `$lang` (Fallback: `$pageLang`) und `$currentPage` (Fallback: kein
  aktiver Nav-Punkt).
- **`$currentPage`-Werte**: `'home'` | `'articles'` | `'cv'`. Artikel-Detail
  übergibt `'articles'` (Nav-Punkt „Artikel" aktiv), 404 übergibt keinen.
- **Theme-System**: einziger Client-State ist `data-theme` auf `<html>`,
  persistiert unter dem localStorage-Key **`ng-theme`**. Inline-Script im
  `<head>` von `layout.php` **vor** den Stylesheet-Links setzt das Attribut vor
  dem ersten Paint; ohne gespeicherte Wahl gilt Light. Toggle-Button mit
  `id="theme-toggle"` in `header.php`; Logik in **`public/assets/js/theme.js`**
  (einziges eigenes Script der Website; Icon-Wechsel ☾/☀ rein per CSS über
  `[data-theme]`-Selektoren). Theme-Übergang transitioniert `background-color`
  und `color` (350ms), nie das `background`-Shorthand (Firefox-Bug).
- **Sprachauflösung**: `detectLang()` in `public/lib/lang.php` — Deutsch-Signal
  im `Accept-Language`-Header → `de`; sonst Englisch-Signal → `en`; sonst
  (leer/andere Sprachen) → `de`.
- **CSS-Tokens**: `base.css` definiert die Token aus dem Handoff-README unter
  `:root` (Light) und `[data-theme="dark"]` mit exakt den Handoff-Namen
  (`--bg`, `--card`, `--text`, `--prose`, `--muted`, `--faint`, `--line`,
  `--line2`, `--accent`, `--accent-hover`, `--accent-ink`, `--btn-bg`,
  `--btn-fg`, `--chip`, `--code-bg`, `--g2`, `--g3`). Die bestehenden
  Font-Token-Namen `--font-heading` (Space Grotesk), `--font-body` (Inter),
  `--font-mono` (JetBrains Mono) bleiben erhalten.
- **Geteilte Bausteine** in `base.css`: Klassen `.eyebrow`, `.btn-primary`,
  `.btn-outline`, `.card`, `.chip`, `.gradient-text`, `.glow`; Keyframes
  `sheen`, `orb`, `rise`; ein `prefers-reduced-motion: reduce`-Block
  deaktiviert alle drei. Seitenspezifisches bleibt in den vorhandenen
  Per-Page-Stylesheets (`home.css`, `overview.css`, `article.css`, `cv.css`,
  `error.css`), geladen über die bestehende `pageStyles`-Mechanik.
- **Fonts**: self-hosted als statische woff2 (latin-Subset, nur tatsächlich
  genutzte Schnitte) in `public/assets/fonts/`; `@font-face` mit
  `font-display: swap` in `base.css`. Montserrat entfällt komplett, inklusive
  der Preloads in `layout.php`.
- **Neue Helper** (reine Funktionen in `public/lib/articles.php`):
  - `formatArticleDate(string $isoDate, string $lang, bool $withYear = true): string`
    — deutsches/englisches Datumsformat ohne Locale-Abhängigkeit (ersetzt das
    bisherige `date('d. F Y', …)`, das englische Monatsnamen lieferte).
  - `groupArticlesByYear(array $articles): array` — absteigend nach Datum
    sortiert, gruppiert nach Jahr (Jahr ⇒ Artikel-Liste, neuestes Jahr zuerst).
  - `estimateReadingMinutes(string $markdown): int` — Wortzahl / 200, gerundet,
    mindestens 1; Wortzählung Unicode-sicher über `preg_split` auf Whitespace
    (nicht `str_word_count`, das an Umlauten scheitert).

## Inventory

Entry Points:

- `public/index.php` — Startseite; ruft `detectLang()`, `loadProjects()`, `render()`
- `public/articles.php` — Übersicht + Detail (Slug-Routing); setzt `$hasCode`
  über `strpos($html, '<pre><code')` und lädt `vendor/highlight.css` nur bei Bedarf
- `public/cv.php` — Lebenslauf; `loadCV()`, `cvLabels()`
- `public/404.php` — Fehlerseite (Status setzt `.htaccess`/`router.php`/`articles.php`)
- `public/sitemap.php` — bleibt unangetastet

Helper (`public/lib/`):

- `lang.php — detectLang()` — Default kippt auf Deutsch (Phase 1)
- `render.php — render()` — Buffering-Mechanik, bleibt unverändert
- `articles.php — getArticles(), getLatestArticles(), getArticle(), parseArticleMarkdown()` — bekommt die drei neuen Helper
- `projects.php — loadProjects()` — `_de`-Auflösung für Projekte
- `cv.php — loadCV(), resolveLocale(), formatCVDate(), cvLabels()` — Datums-Helper
  („Okt 2025") und Label-Mechanik werden weiterverwendet, `cvLabels()` um neue
  UI-Strings ergänzt

Templates (`public/templates/`):

- `layout.php` — Meta/OG/Canonical-Mechanik (bleibt), Montserrat-Preloads
  (entfallen), Footer (wird neu), bekommt Inline-Theme-Script + Header-Include
- `header.php` — alter seitenfüllender Header (Profilbild + H1 + Nav inkl.
  Xing/Medium) — wird zur neuen schlanken Nav; Xing/Medium wandern in den Footer
- `home.php` — alter Portfolio-Loop + IntersectionObserver-Inline-Script (entfällt)
- `overview.php` — flache Artikelliste mit `date('d. F Y', …)`
- `article.php` — minimales Template mit `$hasCode`-Gate (Gate bleibt)
- `cv-page.php` — lineare Sektionen, wird Timeline + Sidebar
- `404-page.php` — nutzt `assets/img/404.webp` (entfällt)

Styles & Assets:

- `public/assets/css/base.css` — Montserrat-`@font-face`, alte Token — wird ersetzt
- `public/assets/css/{home,overview,article,cv,error}.css` — werden je Seitenphase ersetzt
- `public/assets/fonts/Montserrat-*.woff2` (4 Dateien) — werden gelöscht
- `public/assets/img/` — alle benötigten Bilder vorhanden (`nico-social.jpg`,
  `jotti.png`, `haufe-akademie.png`, `idana.png`, 16 Early-Projekt-Bilder,
  `icon.png`); `404.webp` wird obsolet
- `public/vendor/highlight.css` — Farbschema wird an neue Codeblock-Palette angepasst
- `public/vendor/highlight.js`, `public/vendor/Parsedown.php` — unverändert

Content (Quelle für alle Fakten, bleibt unverändert):

- `public/content/articles.json` — 9 Artikel, absteigend sortiert
- `public/content/projects.json` — 19 Projekte: 3 Haupt (jotti, Haufe Akademie,
  Idana) + **16** mit `early`-Flag; 2 Early-Projekte (Freiburg Challenge,
  Kuunery) haben keine `linkUrl`
- `public/content/cv.json` — `basics` (inkl. `email`, `github`, `linkedin`),
  7 `experience`-Einträge (laufende: **leeres** `end`-Feld), 1 `education`,
  4 `languages`, 3 `certifications`, 2 `volunteering`

Sonstiges:

- `public/.htaccess` — `ErrorDocument 404`, Schutz von `content/`/`lib/`/`templates/` — unverändert
- `router.php` — Dev-Routing — unverändert
- `Makefile — check` — Qualitätstor: `php -l`-Lint + PHPStan; muss in jeder Phase grün sein

## Resolved decisions

Während Recherche aufgelöst (keine offenen Nutzerfragen; die PRD deckt alle
Grundsatzentscheidungen ab):

- **Header-Zentralisierung**: `layout.php` bindet `header.php` ein statt jedes
  Seiten-Template einzeln — nötig, damit Artikel- und 404-Seiten die neue Nav
  bekommen. Die `require header.php`-Zeilen in `home.php`, `overview.php`,
  `cv-page.php` entfallen in Phase 1 (sonst doppelter Header im Zwischenstand).
- **Early-Projekte: 16, nicht 15.** Die PRD nennt „aktuell 15", das JSON
  enthält 16 Einträge mit `early`-Flag. Das Flag ist maßgeblich — alle 16
  erscheinen im Grid.
- **Projekte ohne `linkUrl`** (Freiburg Challenge, Kuunery) rendern als nicht
  verlinkte Karte statt als Link.
- **Copy der jotti-/Haufe-/Idana-Karten** lebt im Template (DE wortgleich aus
  `design/Startseite.dc.html`, EN neu formuliert) — die Langbeschreibungen aus
  `projects.json` passen nicht ins Kartenformat. Das Early-Grid dagegen iteriert
  vollständig über `projects.json`.
- **Laufende CV-Stationen** erkennt das Template wie bisher am falsy
  `end`-Feld (die Daten nutzen `""`, die PRD schreibt `null` — beides falsy).
- **Aufräumen**: Montserrat-woff2-Dateien und `404.webp` werden in Phase 7
  gelöscht (PRD: „Montserrat entfällt komplett"; neue 404 braucht kein Bild).
- **Font-Beschaffung**: statische woff2 (latin-Subset) der im Design genutzten
  Schnitte von Google Fonts, manuell nach `public/assets/fonts/` vendored — von
  der PRD gedeckt („die einzigen neuen Assets").
- **Artikel-Bilder** bekommen `loading="lazy"` (kleines Post-Processing der
  Parsedown-Ausgabe); Hero-Porträt der Startseite lädt eager.

## Open questions / Risks

- **Lighthouse ≥ 90 auf der Startseite** mit Porträt + 19 Projektbildern:
  Lazy-Loading ist eingeplant; falls der Wert reißt, sind Bildmaße
  (`width`/`height` gegen CLS) die erste Stellschraube — messen, nicht raten.
- **Favicon** (`icon.png`, altes Blau/Gelb) könnte gegen die neue Farbwelt
  beißen. Die PRD sieht keine neuen Bilder vor → in Phase 7 nur prüfen und
  ggf. als Folgearbeit berichten, nicht eigenmächtig ersetzen.
- **`color-mix()`**: von der PRD vorausgesetzt; statische Fallbacks laut
  Handoff (`--g2` ≈ `#7a4bd9`, `--g3` ≈ `#2a91d3`) definieren.
- **Zwischenstände auf dem Branch**: Nach Phase 1 sitzen die alten
  Per-Page-Styles auf den neuen Tokens — Seiten sehen bis zu ihrer Phase
  unfertig aus. Akzeptiert, Rollout erfolgt erst am Ende (PRD).

---

## Phase 1: Fundament & Shell — Tokens, Fonts, Header/Footer, Theme, Sprach-Default

**User stories**: 5 (Theme-Toggle), 9 (EN-Besucher), 11 (reduced motion, Basis), 13 (kein Framework/Build-Step)

### Context

- `public/assets/css/base.css` — wird vollständig ersetzt (Tokens, Fonts, Keyframes, Bausteine)
- `public/templates/layout.php` — Inline-Theme-Script, Header-Include, neuer Footer, Preload-Tausch
- `public/templates/header.php` — neue Nav (Logo, Links, Toggle, Kontakt-Pill)
- `public/lib/lang.php — detectLang()` — Default-Kipp auf Deutsch
- `public/index.php`, `public/articles.php`, `public/cv.php`, `public/404.php` — liefern `$lang`/`$currentPage` an die Shell
- Handoff: README „Design Tokens", „Interactions & Behavior"; PLAN.md Phase 0–2

### What to build

Das komplette Fundament als durchgängiger Pfad: Fonts self-hosten und per
`@font-face` einbinden; `base.css` neu schreiben (Token beider Themes,
Keyframes inkl. `prefers-reduced-motion`-Block, geteilte Bausteine, Link- und
Body-Grundstile mit 350ms-Theme-Transition auf `background-color`/`color`);
`layout.php` erhält das Inline-Theme-Script vor den Stylesheet-Links, bindet
den neuen Header ein und bekommt den neuen Footer (© links; GitHub, LinkedIn,
Xing, Medium, E-Mail rechts). `header.php` wird zur schlanken Nav nach Design
(Logo „nico gräf.", Portfolio → `/#portfolio`, Artikel, Lebenslauf,
Theme-Toggle `#theme-toggle` mit `aria-label`, Kontakt-Pill mailto), zweisprachig,
aktive Seite über `$currentPage`. `theme.js` toggelt und persistiert das Theme.
`detectLang()` kippt den Default auf Deutsch. Die Entry Points übergeben
`$lang`/`$currentPage` durchgängig (Artikel-Detail: `'articles'`); die
`require header.php`-Zeilen in den Seiten-Templates entfallen.

Danach zeigen alle Seiten die neue Shell mit funktionierendem Theme-Toggle —
die Seiteninhalte selbst folgen in den Phasen 2–6.

### Acceptance criteria

- [ ] Space Grotesk, Inter und JetBrains Mono laden self-hosted (woff2,
      `font-display: swap`); keine Montserrat-Referenz mehr in `layout.php`
      oder `base.css`, keine Google-Fonts-Requests.
- [ ] Header und Footer erscheinen auf allen fünf Seitentypen (Start, Übersicht,
      Artikel, CV, 404); aktive Seite ist markiert (Artikel-Detail markiert „Artikel").
- [ ] Theme-Toggle funktioniert auf jeder Seite; Wahl übersteht Reload und
      Seitenwechsel (`ng-theme` in localStorage); beim Laden blitzt kein
      falsches Theme auf (Inline-Script vor CSS-Links).
- [ ] Icon-Wechsel ☾/☀ erfolgt rein über CSS; `theme.js` ist das einzige
      eigene Script der Website.
- [ ] Ohne Sprachsignal und bei nicht-deutschen/nicht-englischen Headern
      liefert die Startseite Deutsch; mit englischem Header Englisch; mit
      deutschem Header Deutsch.
- [ ] `prefers-reduced-motion: reduce` deaktiviert `sheen`, `orb` und `rise` global.
- [ ] `make check` grün.

---

## Phase 2: Startseite

**User stories**: 1 (Recruiter-Überblick), 3 (jotti featured), 4 (Early-Grid), 10 (mobil)

### Context

- `public/templates/home.php` — vollständige Neufassung; altes
  IntersectionObserver-Script entfällt ersatzlos
- `public/assets/css/home.css` — vollständige Neufassung
- `public/index.php` — Meta-Wording, ggf. zusätzliche Template-Variablen
- `public/lib/articles.php — getLatestArticles()` — Blog-Teaser (3 Einträge);
  neu: `formatArticleDate()` für die Teaser-Daten
- `public/lib/projects.php — loadProjects()` — Early-Grid
- Handoff: `design/Startseite.dc.html`; README „Screens / Views → 1. Startseite"

### What to build

Die Startseite nach Design: Split-Hero (Eyebrow, H1 mit
`<span class="gradient-text">Nico</span>`, Lead, zwei CTAs „Portfolio ansehen"
primär / „Lebenslauf" outline, Porträt mit „10+ Jahre"-Badge und Verlaufs-Linie,
zwei animierte Glows, `rise`-Stagger nur hier); Blog-Teaser (`#artikel`) mit den
3 neuesten Artikeln als Karten (Datum via `formatArticleDate()`, Titel,
Beschreibung, Hover); Portfolio (`#portfolio`) mit jotti-Featured-Card (feste
Dunkel-Farben in beiden Themes, im Dark Theme zusätzlich Rahmen,
Verlaufs-Topline, vier Chips, Link zu https://jotti.rocks), dem Karten-Paar
Haufe Akademie/Idana und darunter „Frühe Projekte & Experimente" als Grid
**aller 16** `early`-Projekte in JSON-Reihenfolge (Bild lazy, Name, Meta, echte
Links; Einträge ohne `linkUrl` unverlinkt). Deutsche Copy wortgleich aus der
Design-Datei, englische Fassung neu formuliert — Fakten ausschließlich aus
`cv.json`/`projects.json`; jotti-Wording: „source-available", „ausgelegt auf
die KassenSichV". Unter ~900px stapelt alles einspaltig.

### Acceptance criteria

- [ ] Hero entspricht der Design-Referenz in beiden Themes; „Nico" trägt den
      animierten Verlauf; bei `prefers-reduced-motion` steht alles still.
- [ ] Blog-Teaser zeigt die 3 neuesten Artikel mit deutsch bzw. englisch
      formatiertem Datum (je nach Seitensprache) und verlinkt auf die Artikel.
- [ ] jotti-Featured-Card ist in beiden Themes dunkel (im Dark Theme mit
      Rahmen) und verlinkt auf jotti.rocks; Wording-Regeln eingehalten.
- [ ] Early-Grid zeigt alle 16 `early`-Projekte in JSON-Reihenfolge mit echten
      Link-Zielen; Freiburg Challenge und Kuunery erscheinen unverlinkt;
      alle Grid-Bilder laden lazy, das Hero-Porträt eager.
- [ ] Kein Inline-JS mehr in `home.php` (IntersectionObserver entfernt).
- [ ] EN-Variante vollständig (Hero, Badge, Buttons, Sektions-Überschriften,
      Kartentexte); keine erfundenen Fakten.
- [ ] Bei 360px und 768px einspaltig und bedienbar; `make check` grün.

---

## Phase 3: Blog-Übersicht

**User stories**: 6 (Jahres-Gruppierung)

### Context

- `public/templates/overview.php` — Neufassung als gruppierte Liste
- `public/assets/css/overview.css` — Neufassung
- `public/articles.php` — Übersichts-Zweig: Meta-Wording, Gruppierung übergeben
- `public/lib/articles.php` — neu: `groupArticlesByYear()`; Wiederverwendung
  von `formatArticleDate()` (`$withYear = false`)
- Handoff: `design/Blog.dc.html`; README „Screens / Views → 2. Blog-Übersicht"

### What to build

Die Blog-Übersicht als nach Jahr gruppierte Liste (820px-Spalte): Kopf mit
Eyebrow „Blog", H1 „Artikel" und Intro-Satz; pro Jahr ein Label und Zeilen im
Grid Datum (ohne Jahr, deutsch formatiert) / Titel + Beschreibung / Pfeil.
Die gesamte Zeile ist ein Link und hovert als Einheit. Datenfluss:
`getArticles()` → `groupArticlesByYear()` (sortiert absteigend, unabhängig von
der JSON-Reihenfolge) → Template.

### Acceptance criteria

- [ ] Alle 9 Artikel erscheinen, nach Jahr gruppiert, neuestes Jahr und
      neuester Artikel zuerst.
- [ ] Datum in der Zeile ohne Jahr und mit deutschen Monatsnamen
      (z. B. „13. Juli" — nicht mehr „13. July" wie bisher).
- [ ] Ganze Zeile verlinkt und hovert als Einheit in `--accent-ink`.
- [ ] Beide Themes und 360px/768px geprüft; `make check` grün.

---

## Phase 4: Artikel-Detail

**User stories**: 7 (Lese-Layout mit Lesezeit), 8 (Author-Card)

### Context

- `public/templates/article.php` — Neufassung (Backlink, Meta-Zeile, Author-Card);
  `$hasCode`-Gate bleibt exakt erhalten
- `public/assets/css/article.css` — Neufassung (Prose-, Codeblock-, Bild-Styles)
- `public/articles.php` — Detail-Zweig: Lesezeit berechnen und übergeben
- `public/lib/articles.php — parseArticleMarkdown()` — Ansatzpunkt für
  `loading="lazy"` auf Artikel-Bildern; neu: `estimateReadingMinutes()`
- `public/vendor/highlight.css` — Farbschema auf neue Codeblock-Palette abstimmen
- Handoff: `design/Artikel.dc.html`; README „Screens / Views → 3. Artikel-Detail"

### What to build

Das 760px-Lese-Layout: Backlink „← Alle Artikel", Meta-Zeile **Datum ·
Lesezeit** (bewusst ohne Kategorie, PRD-Entscheidung), H1, Lead, Prose-Styles
für die Parsedown-Ausgabe (Absätze, H2/H3, Listen, Links, Inline-Code,
Codeblöcke, Bilder) nach Design-Tokens. Lesezeit über
`estimateReadingMinutes()` aus dem Markdown-Rohtext. Artikel-Bilder bekommen
`loading="lazy"`. Das Highlight-Farbschema wird an die neue Codeblock-Palette
angepasst (nur Farben in `vendor/highlight.css`, kein Skript-Umbau). Am
Artikelende die Author-Card: Foto (`nico-social.jpg`, 64px rund), Name, Kurzbio
(DE wortgleich aus der Design-Datei), Link „Alle Artikel →". Artikelseiten
bleiben vollständig deutsch.

### Acceptance criteria

- [ ] Meta-Zeile zeigt deutsch formatiertes Datum und plausible Lesezeit
      (mindestens „1 Min."); keine Kategorie.
- [ ] Alle 9 Artikel rendern korrekt — insbesondere der längste Artikel
      (Warenkorb, mit Codeblöcken und Bildern); interne Artikel-Links
      funktionieren.
- [ ] Codeblöcke erscheinen in der neuen Token-Palette; das Highlight-Schema
      passt dazu; ein Artikel ohne Codeblöcke lädt weiterhin **kein**
      `highlight.js`/`highlight.css` (`$hasCode`-Gate intakt).
- [ ] Artikel-Bilder laden lazy und sind auf Spaltenbreite begrenzt.
- [ ] Author-Card am Ende mit Foto, Name, Kurzbio und Link zur Übersicht.
- [ ] Beide Themes und 360px/768px geprüft; `make check` grün.

---

## Phase 5: Lebenslauf

**User stories**: 2 (Timeline + Sidebar), 10 (mobil)

### Context

- `public/templates/cv-page.php` — Neufassung (Kopf, Timeline, Sidebar)
- `public/assets/css/cv.css` — Neufassung
- `public/cv.php` — Meta-Wording
- `public/lib/cv.php — formatCVDate(), cvLabels()` — Zeiträume („Okt 2025 –
  heute" / „Oct 2025 – present") und Labels; `cvLabels()` um neue UI-Strings
  ergänzen (z. B. Kontakt-Chips)
- `public/content/cv.json` — alleinige Faktenquelle
- Handoff: `design/Lebenslauf.dc.html`; README „Screens / Views → 4. Lebenslauf"

### What to build

Der Lebenslauf nach Design (1000px): Kopf mit Foto (120px), Name, Headline und
drei Kontakt-Chips (E-Mail, GitHub, LinkedIn aus `cv.json` `basics`), darunter
die Summary. Dann Grid 1.7fr/1fr: links die Erfahrungs-Timeline mit allen 7
Stationen — laufende (falsy `end`) mit Kobalt-Dot und Halo, beendete gedämpft;
pro Station Zeitraum-Eyebrow, „Rolle · Firma", Ort, Beschreibung und die
vollständigen Tag-Chips. Rechts die Sidebar mit Ausbildung, Sprachen (4),
Zertifikaten (3) und Ehrenamt (2) aus den vorhandenen JSON-Abschnitten.
Zweisprachig über die bestehende `_de`-Mechanik. Unter ~900px einspaltig,
Sidebar unter der Timeline.

### Acceptance criteria

- [ ] Alle 7 Stationen erscheinen; jotti und Haufe Akademie (laufend) mit
      Akzent-Dot und Halo, beendete mit gedämpftem Dot.
- [ ] Zeiträume korrekt formatiert: DE „Okt 2025 – heute", EN „Oct 2025 – present".
- [ ] Jede Station zeigt ihre vollständigen Tags als Chips.
- [ ] Sidebar vollständig: 1 Ausbildung, 4 Sprachen, 3 Zertifikate, 2 Ehrenämter.
- [ ] Kontakt-Chips verlinken auf mailto, GitHub und LinkedIn aus `cv.json`.
- [ ] EN-Variante bei englischer Browser-Sprache vollständig; keine Fakten
      außerhalb von `cv.json`.
- [ ] Beide Themes und 360px/768px geprüft; `make check` grün.

---

## Phase 6: 404-Seite

**User stories**: 12 (gestaltete 404)

### Context

- `public/templates/404-page.php` — Neufassung (Verlaufs-Ziffern statt `404.webp`)
- `public/assets/css/error.css` — Neufassung (Full-Height-Flex: Header / Mitte / Footer)
- `public/404.php` — Meta-Wording
- `public/.htaccess` (`ErrorDocument`), `router.php`, `public/articles.php`
  (unbekannter Slug) — bestehende 404-Auslöser, bleiben unverändert
- Handoff: `design/404.dc.html`; README „Screens / Views → 5. 404"

### What to build

Die 404-Seite nach Design: „404" als große Verlaufs-Ziffern mit
`sheen`-Animation, H1 und Erklärsatz, zwei Buttons („Zur Startseite" primär,
„Alle Artikel" outline), zwei Glows; die Seite füllt den Viewport als
Flex-Spalte mit Header oben und Footer unten. Zweisprachig (DE-Copy wortgleich
aus der Design-Datei, EN neu). Der Server liefert weiterhin Status 404 auf
allen bestehenden Wegen.

### Acceptance criteria

- [ ] Unbekannte URL liefert HTTP-Status 404 mit der neuen Seite (dev via
      `router.php` prüfbar); unbekannter Artikel-Slug ebenso.
- [ ] Verlaufs-Ziffern animieren per `sheen`; bei `prefers-reduced-motion`
      statisch.
- [ ] Beide Buttons führen zur Startseite bzw. zur Artikelübersicht.
- [ ] DE/EN je nach Browser-Sprache; Default Deutsch.
- [ ] Beide Themes und 360px geprüft; `make check` grün.

---

## Phase 7: Politur, Aufräumen, Doku & Gesamt-QA

**User stories**: 10 (responsive), 11 (reduced motion), 13 (URLs/Betrieb) — Gesamtabnahme

### Context

- `public/assets/fonts/Montserrat-*.woff2`, `public/assets/img/404.webp` — löschen
- `public/index.php`, `public/articles.php`, `public/cv.php`, `public/404.php` —
  Titel/Beschreibungen final ans neue Wording angleichen
- `AGENTS.md` — veraltete Aussagen aktualisieren (Sprach-Default jetzt Deutsch,
  neue Fonts statt Montserrat, neue Shell statt Profilbild-Header,
  CSS-Bausteine in `base.css`)
- Handoff: PLAN.md Phase 7–8 (QA-Checkliste)

### What to build

Abschlussarbeiten auf dem `redesign`-Branch vor dem Rollout: tote Reste des
alten Designs entfernen (Montserrat-Dateien, `404.webp`, verwaiste Klassen wie
`.profile-picture`, ungenutzte Styles), Meta-/OG-Texte aller Seiten final
prüfen, Favicon gegen die neue Farbwelt sichten (nur berichten, nicht
ersetzen — PRD sieht keine neuen Bilder vor), Repo-Doku (`AGENTS.md`) an den
neuen Stand anpassen. Danach die vollständige QA-Checkliste aus PRD/Handoff
als Gesamtabnahme über alle Seiten fahren.

### Acceptance criteria

- [ ] Keine Montserrat-Dateien, kein `404.webp`, keine verwaisten
      Klassen/Styles des alten Designs mehr im Repo.
- [ ] Meta-Titel/-Beschreibungen aller fünf Seiten stimmen mit dem neuen
      Wording überein; Canonical/OG/hreflang-Mechanik unverändert funktionsfähig;
      `/sitemap.xml` unverändert erreichbar.
- [ ] `AGENTS.md` beschreibt den neuen Stand (Default Deutsch, Fonts, Shell,
      CSS-Bausteine); keine veralteten Aussagen mehr („Homepage English",
      Montserrat, Profilbild-Header).
- [ ] QA: Theme-Toggle auf jeder Seite ohne FOUC; beide Themes auf allen fünf
      Seiten kontrastgeprüft (AA); Firefox-Gegentest der Theme-Transition;
      `prefers-reduced-motion` deaktiviert alle Animationen.
- [ ] QA: Responsive bei 360px, 768px, 1280px, 1920px auf allen Seiten.
- [ ] QA: alle 9 Artikel rendern, interne Links funktionieren, Lesezeiten
      plausibel; EN-Varianten von Startseite, CV und 404 bei englischer
      Browser-Sprache, Deutsch ohne Sprachsignal.
- [ ] QA: Lighthouse ≥ 90 (Performance, Accessibility, SEO) auf Startseite
      und einem Artikel.
- [ ] Sichtbare Fokus-Styles auf allen interaktiven Elementen; `aria-label`
      am Theme-Toggle.
- [ ] `make check` grün.
