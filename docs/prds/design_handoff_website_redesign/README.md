# Handoff: nicograef.com Redesign

## Overview
Komplettes Redesign der persönlichen Website nicograef.com (Portfolio + Blog + Lebenslauf) im Stil der jotti-Produktseite (jotti.rocks): gleiche Design-DNA (Space Grotesk + Inter, Eyebrow-Labels, weiche Glows, großzügige Radien), aber eigene Farbwelt — warmer Papierton mit Kobalt-Akzent und animiertem Kobalt→Violett→Cyan-Verlauf. Fünf Seiten: Startseite, Blog-Übersicht, Artikel-Detail, Lebenslauf, 404. Light/Dark-Theme mit Toggle und localStorage-Persistenz.

## About the Design Files
Die Dateien in `design/` sind **Design-Referenzen in HTML** (Prototypen, die Aussehen und Verhalten zeigen) — **kein Produktionscode zum Kopieren**. Aufgabe ist es, diese Designs **in der bestehenden Codebasis (github.com/nicograef/website — PHP-Templates + Vanilla-CSS/JS) nachzubauen**, mit deren etablierten Mustern: PHP-Templates in `public/templates/`, CSS in `public/assets/css/`, Inhalte aus `public/content/*.json` und `public/content/articles/*.md`. Die `.dc.html`-Dateien im Browser öffnen, um das Zielbild zu sehen (benötigen `support.js` im selben Ordner).

Siehe auch: `PRD.md` (Produktanforderungen) und `PLAN.md` (Umsetzungsplan für das PHP-Repo).

## Fidelity
**High-fidelity (hifi).** Farben, Typografie, Abstände, Copy und Interaktionen sind final gemeint und sollen pixelgenau übernommen werden. Alle Werte stehen unten und als CSS-Variablen/Inline-Styles in den Design-Dateien.

## Design Tokens

### Farben — Light Theme (Default)
| Token | Wert | Verwendung |
|---|---|---|
| `--bg` | `#f8f4ec` | Seitenhintergrund (warmer Papierton) |
| `--card` | `#fffdf7` | Karten, Author-Box |
| `--text` | `#17150e` | Überschriften, Primärtext |
| `--prose` | `#3d3b2f` | Fließtext (Artikel, CV-Beschreibungen) |
| `--muted` | `#5f5c4e` | Sekundärtext, Nav-Links |
| `--faint` | `#8f8a76` | Daten, Meta, Footer |
| `--line` | `#e7e2d3` | Trennlinien, Kartenrahmen |
| `--line2` | `#ddd7c5` | Button-Outlines, Toggle-Rahmen |
| `--accent` | `#2b45d8` | Kobalt: Buttons, Timeline-Dots |
| `--accent-hover` | `#1f36b8` | Button-Hover |
| `--accent-ink` | `#2b45d8` | Eyebrows, Links, aktiver Nav-Punkt |
| `--btn-bg` / `--btn-fg` | `#17150e` / `#f8f4ec` | Kontakt-Pill in der Nav |
| `--chip` | `#f0ebdd` | Tag-Chips im CV |
| `--code-bg` | `#17150e` | Codeblöcke |
| Glow 1 / Glow 2 | `rgba(43,69,216,.10)` / `rgba(200,140,40,.12)` | Radiale Hero-Glows (blau / bernstein) |

### Farben — Dark Theme (`[data-theme="dark"]`)
| Token | Wert |
|---|---|
| `--bg` | `#14130c` |
| `--card` | `#1c1a11` |
| `--text` | `#f2eee0` |
| `--prose` | `#d6d1bf` |
| `--muted` | `#b8b3a0` |
| `--faint` | `#8c8874` |
| `--line` | `#2b281c` |
| `--line2` | `#3a3627` |
| `--accent-ink` | `#98a7ff` (aufgehelltes Kobalt für Text auf dunklem Grund) |
| `--btn-bg` / `--btn-fg` | `#f2eee0` / `#14130c` (invertiert) |
| `--chip` | `#26231a` |
| `--code-bg` | `#0e0d08` |
| Glow 1 / Glow 2 | `rgba(105,130,255,.10)` / `rgba(220,160,60,.07)` |

`--accent` (#2b45d8) und `--accent-hover` bleiben in beiden Themes gleich (Buttons mit weißem Text funktionieren auf beiden Hintergründen).

### Verlauf (Gradient)
Signature-Element, abgeleitet vom Akzent:
```css
--g2: color-mix(in oklab, var(--accent) 55%, #a855f7);  /* violett */
--g3: color-mix(in oklab, var(--accent) 50%, #22d3ee);  /* cyan */
background: linear-gradient(100deg, var(--accent), var(--g2), var(--g3), var(--accent));
```
Verwendung: „Nico" im Hero-H1 (Text-Clip), 3px-Topline der jotti-Featured-Card, Unterstrich-Glow unter dem Hero-Foto, „404"-Ziffern. Animiert per `sheen` (s.u.) mit `background-size: 200% auto`.

### Typografie
- **Headings:** Space Grotesk (Google Fonts, 400–700), `letter-spacing: -0.02em` auf H1
- **Body:** Inter (400–700)
- **Code:** JetBrains Mono (400–500)
- Skala: H1 Hero `clamp(44px, 6vw, 72px)` /1.05 · H1 Unterseiten `clamp(34–36px, 5vw, 48–52px)` · Artikel-H1 `clamp(32px, 4.5vw, 46px)` /1.12 · H2 Sektionen 34px · Artikel-H2 27px · H3 Karten 20–21px · Lead 19px/1.65 · Prose 17px/1.75 · Body 15–15.5px/1.6 · Meta 13–13.5px · Chips 12px
- Eyebrows: 13px, 700, `letter-spacing: .06em`, uppercase, `color: var(--accent-ink)`

### Radien
26px (Hero-Foto, Featured-Card) · 20px (Karten) · 16px (kleine Projektkarten, Badges) · 13px (Buttons) · 11px (Theme-Toggle) · 999px (Chips, Kontakt-Pill)

### Schatten
- Hero-Foto: `0 30px 70px -18px rgba(60,50,20,.35)`
- Badge („10+ Jahre"): `0 12px 30px -10px rgba(60,50,20,.25)`
- jotti-Screenshot: `0 20px 50px -12px rgba(0,0,0,.6)`
- CV-Foto: `0 16px 40px -12px rgba(60,50,20,.35)`

### Layout
- Content-Breite: 1160px (Startseite/Nav/Footer), 1000px (Lebenslauf), 820px (Blog-Liste), 760px (Artikel); seitliches Padding 32px
- Header: Logo links („nico gräf." — Punkt in `--accent-ink`), Nav rechts: Portfolio / Artikel / Lebenslauf / Theme-Toggle (38×38px) / Kontakt-Pill. Aktiver Punkt: `color: var(--accent-ink); font-weight: 700`
- Footer: © links; GitHub / LinkedIn / E-Mail rechts, 14px `--faint`

## Screens / Views

### 1. Startseite (`design/Startseite.dc.html`)
- **Hero:** Grid `1.45fr 1fr`, Gap 60px, Padding `80px 32px 84px`. Links: Eyebrow „Senior Software Engineer · Freiburg im Breisgau", H1 „Hi, ich bin **Nico**." (Nico = animierter Verlauf), Lead-Absatz (560px max), zwei Buttons: „Portfolio ansehen" (primär, Kobalt) + „Lebenslauf" (Outline 2px `--line2`, Hover Kobalt). Rechts: Foto `aspect-ratio: 4/4.5`, `object-position: 50% 15%`, Radius 26px; Badge „**10+ Jahre** Software-Entwicklung" unten links überlappend (`bottom: -14px; left: -18px`); Verlaufs-Linie (70% breit, 3px, blur 1px, Opacity .4) unter dem Foto. Zwei radiale Glows hinter dem Hero (`orb`-Animation, 9s/11s, zweiter reverse).
- **Blog-Sektion** (`#artikel`): Eyebrow „Blog", H2 „Neueste Artikel" + Link „Alle Artikel →" rechts (baseline). 3 Karten im Grid `repeat(auto-fit, minmax(280px, 1fr))`: Datum (13px `--faint`) / Titel (Space Grotesk 19px/600) / Beschreibung (14.5px `--muted`). Hover: `border-color: var(--accent-ink)`.
- **Portfolio-Sektion** (`#portfolio`): Eyebrow „Portfolio", H2 „Woran ich arbeite".
  - **jotti Featured-Card:** immer dunkel (`#17150e`, Text `#f8f4ec`), Radius 26px, Padding 44px, Grid `1fr 1.1fr`, 3px-Verlaufs-Topline. Links: 4 Outline-Chips (Go, React, Event Sourcing, KassenSichV), H3 „jotti / seit 2025" (Suffix `#8f9dee`), Beschreibung `#b5b1a0`, Link „Website besuchen →" (`#8f9dee`, → https://jotti.rocks). Rechts: Screenshot. Im Dark Theme zusätzlich Border `--line`.
  - **2 Job-Karten** (Haufe Akademie / Idana): `--card`, Border `--line`, Radius 20px, Padding 30px.
  - **„Frühe Projekte & Experimente":** H3 in `--muted`, 4 Karten `minmax(220px, 1fr)`: Bild 110px cover + Name (14.5px/600) + Meta (12.5px `--faint`). What The Flag (PWA · MERN · 2019), Sudoku Android App (Java · Android · 2016), Freiburg Challenge (PWA · Firebase · 2020), Smart Coffee (Arduino · C++ · 2015).

### 2. Blog-Übersicht (`design/Blog.dc.html`)
Kopf (820px): Eyebrow „Blog", H1 „Artikel", Intro-Satz. Liste nach Jahr gruppiert (Jahres-Label: Space Grotesk 15px/600 `--faint`). Zeile: Grid `110px 1fr auto`, Padding `24px 0`, `border-top: 1px solid var(--line)` (letzte Zeile auch border-bottom): Datum ohne Jahr / Titel (19px/600) + Beschreibung (14.5px `--muted`) / Pfeil „→" in `--accent-ink`. Hover färbt die Zeile `--accent-ink`. Inhalte: alle 9 Artikel aus `public/content/articles.json`.

### 3. Artikel-Detail (`design/Artikel.dc.html`)
Referenzartikel „Strategisches Domain Driven Design". 760px Spalte. „← Alle Artikel"-Backlink, Meta-Zeile (Kategorie-Eyebrow · Datum · Lesezeit), H1, Lead (19px `--muted`), Prose 17px/1.75 in `--prose` mit 22px Absatzabstand. Artikel-H2: Space Grotesk 27px/700. Inline-Code: JetBrains Mono 14.5px, `background: var(--line)`, Radius 6px, Padding `2px 7px`. Codeblock: `--code-bg`, Text `#e8e4d4`, 13.5px/1.7, Radius 14px, Padding `24px 28px`. Prose-Links: `--accent-ink` mit 40%-transparenter Unterstreichung. Listen: `gap: 10–14px`. Am Ende Author-Card: Foto 64px rund + Name + Kurzbio + „Alle Artikel →".

### 4. Lebenslauf (`design/Lebenslauf.dc.html`)
Kopf (1000px): Foto 120px (Radius 28px) + H1 + Untertitel + 3 Kontakt-Chips (Outline-Pills: E-Mail, GitHub, LinkedIn). Summary-Absatz (17px/1.7, 760px max). Dann Grid `1.7fr 1fr`, Gap 56px:
- **Links „Erfahrung":** Timeline `24px 1fr`. Dot 12px rund — aktuelle Stationen (jotti, Haufe) in `--accent` mit 4px-Halo (`color-mix(in oklab, var(--accent) 18%, transparent)`), beendete in `--line2`; 2px-Verbindungslinie `--line`. Pro Station: Zeitraum-Eyebrow (aktiv `--accent-ink`, sonst `--faint`), H3 „Rolle · Firma" (20px/600), Ort (14px `--faint`), Beschreibung (15px/1.65 `--prose`), Tag-Chips (`--chip`, 999px, `3px 11px`, 12px `--muted`). Alle 7 Stationen und vollständige Tags: siehe `public/content/cv.json` im Original-Repo (deutsche Felder `*_de`).
- **Rechts (Sidebar):** Ausbildung (Karte: B.Sc. Embedded Systems Engineering, Uni Freiburg, 2013–2020, Note 1,8 + Schwerpunkte), Sprachen (4 Zeilen Name/Level `space-between`), Zertifikate (3), Ehrenamt (2).

### 5. 404 (`design/404.dc.html`)
`min-height: 100vh` Flex-Spalte (Header / Mitte / Footer). Zentriert: „404" in `clamp(120px, 20vw, 220px)`, Space Grotesk 700, `letter-spacing: -0.04em`, Verlaufs-Text mit `sheen`; H1 „Diese Seite gibt es nicht." (28px); Erklärsatz; Buttons „Zur Startseite" (primär) + „Alle Artikel" (Outline). Zwei Glows.

## Interactions & Behavior

### Theme-Toggle
- 38×38px Button in der Nav, Icon ☾ (light) / ☀ (dark), Border `--line2`, Radius 11px
- Klick toggelt `data-theme="light|dark"` auf `<html>` und persistiert unter localStorage-Key **`ng-theme`**; beim Laden jeder Seite wird der Key gelesen und das Attribut vor dem ersten Paint gesetzt (Inline-Script im `<head>`, um Flash zu vermeiden)
- Übergang: `transition: background-color .35s, color .35s` auf body; Border-Übergänge .35s. **Wichtig:** `background-color` transitionieren, nicht das `background`-Shorthand (Shorthand + CSS-Variable bleibt in Firefox hängen)

### Animationen
```css
@keyframes sheen { 0% { background-position: -140% 0; } 100% { background-position: 240% 0; } }  /* 6s linear infinite, auf background-size:200% auto */
@keyframes orb   { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(14px,-18px) scale(1.06); } }  /* 9s bzw. 11s reverse, ease-in-out infinite */
@keyframes rise  { from { opacity:0; transform: translateY(18px); } to { opacity:1; transform: translateY(0); } }  /* Entrance: .6s ease both, Stagger 0/.08s/.16s/.24s */
```
`rise` nur auf Hero-/Kopfelemente jeder Seite. Glows: `filter: blur(26px)`, `pointer-events: none`, absolut hinter dem Hero, Container `overflow: hidden`. Optional `prefers-reduced-motion: reduce` → Animationen aus.

### Hover-States
- Primär-Button: `--accent` → `--accent-hover`
- Outline-Button/Chips: Border + Text → `--accent-ink`
- Karten: `border-color` → `--accent-ink` (transition .2s)
- Nav-Links: Text → `--accent-ink`; Kontakt-Pill: `opacity: .85`
- Blog-Listenzeile: gesamte Zeile → `--accent-ink`

### Navigation
Logo → Startseite · „Portfolio" → Startseite `#portfolio` · „Artikel" → Blog-Übersicht · „Lebenslauf" → CV · „Kontakt" → `mailto:graef.nico@gmail.com` · Blog-Karten/-Zeilen → Artikelseite · jotti-Card → https://jotti.rocks (neuer Tab) · Footer: GitHub / LinkedIn / E-Mail.

## State Management
Einziger State: Theme (`ng-theme` in localStorage, `data-theme` auf `<html>`). Kein weiterer JS-State; alles andere ist statisch/serverseitig gerendert. Inhalte kommen wie bisher aus `public/content/*.json` + Markdown.

## Assets
Alle in `design/public/assets/img/`, kopiert aus dem bestehenden Repo (dort bereits vorhanden): `nico-social.jpg` (Porträt), `jotti.png`, `haufe-akademie.png`, `idana.png`, `what-the-flag.jpg`, `sudoku-app.jpg`, `freiburg-challenge.jpg`, `smart-coffee.jpg`, `wiwili.png`, `lokalrunde.jpg`. Fonts via Google Fonts: Space Grotesk, Inter, JetBrains Mono (besser: self-hosten).

## Files
- `design/Startseite.dc.html` — Startseite (Hero, Blog-Teaser, Portfolio)
- `design/Blog.dc.html` — Blog-Übersicht
- `design/Artikel.dc.html` — Artikel-Detailseite
- `design/Lebenslauf.dc.html` — Lebenslauf
- `design/404.dc.html` — Fehlerseite
- `design/support.js` — Runtime für die Prototypen (nur fürs Betrachten nötig)
- `PRD.md` — Produktanforderungen
- `PLAN.md` — Umsetzungsplan für das PHP-Repo
