# Review PR #5 „feat: complete website redesign" — Findings (lokal)

Unabhängiges Multi-Experten-Review (9 Dimensionen, jedes Finding adversarial
verifiziert) des Branches `claude/website-redesign-moma8b` gegen `main`
(46fdf19, 38 Dateien, +1961/−707). Stand: 2026-07-14.

## Urteil

**Approve mit kleinen Fixes** (alle in diesem Review direkt behoben, siehe unten).
Alle Acceptance-Criteria der Plan-Phasen 1–7 wurden einzeln geprüft und bestehen;
Design-Fidelity, Repo-Regeln und Funktionalität sind durchgehend eingehalten.

**Nachtrag (vom Autor gemeldet):** Ein major-Finding kam durch das plan-basierte
Audit, weil es dem Plan *entspricht* — die Sprach-Fallback-Logik (Finding 0).
Der Plan hatte das ursprüngliche Produktziel invertiert; nach Rücksprache
korrigiert. Die im PR deklarierten bewussten
Abweichungen (`--faint`-Abdunklung auf 4,66:1 — nachgerechnet, gerechtfertigt;
Gedankenstrich-Konvention — der Prototyp ist selbst inkonsistent, gerechtfertigt)
sind plausibel begründet.

## Eigene Verifikation (nicht nur gelesen)

- `make check` (php -l + PHPStan level 5, phpVersion 70400): **grün** (selbst ausgeführt).
- Dev-Server-Matrix: alle 5 Seitentypen × DE/EN (Accept-Language), korrekte
  `<html lang>`, Titel, Meta; 404-Status auf unbekannter URL, unbekanntem Slug,
  `?slug[]=x` (Array) und Traversal-Versuchen; alle 9 Artikel 200 ohne
  PHP-Warnings; `$hasCode`-Gate exakt (6 Code-Artikel laden highlight.js+css,
  3 codefreie laden **nichts** davon); Sitemap/robots.txt intakt (nicograef.com).
- Playwright (Chromium): 5 Seiten × 360px/1280px × Light/Dark = 20 Kombinationen,
  **kein horizontaler Overflow**, keine Layoutbrüche, keine kaputten Bilder;
  Theme-Toggle inkl. Reload-/Navigations-Persistenz (`ng-theme`), FOUC-Guard
  (Inline-Script vor CSS-Links), Icon-Wechsel rein per CSS; `prefers-reduced-motion`
  stoppt sheen/orb/rise global; Fokus-Ringe + `aria-label` am Toggle vorhanden.
- Design-Vergleich: alle 5 `.dc.html`-Prototypen mit Playwright gerendert und
  gegen die echten Seiten verglichen (beide Themes) — Tokens, Typo-Skala, Radien,
  Schatten, Layout-Breiten wertgleich; DE-Copy wortgleich.
- AGENTS.md-Regeln: keine PHP-8.0+-Features im Nicht-Vendor-Code; alle dynamischen
  Ausgaben durch `htmlspecialchars()` (Raw-Ausgaben sind die korrekten, bewussten
  Ausnahmen: `$pageContent`, Parsedown-HTML, vorab-escapte Fragmente); Fakten
  stimmen mit cv.json/projects.json; jotti-Wording korrekt („source-available",
  „ausgelegt auf die KassenSichV"); Domain nicograef.com.
- Lighthouse (durch Review-Agent): Start 90/100/100, Artikel 91/100/100 — deckt
  die PR-Behauptung.

## Bestätigte Findings (nach Schwere)

| # | Schwere | Stelle | Finding | Fix in diesem Review |
|---|---------|--------|---------|----------------------|
| 0 | major | `public/lib/lang.php:12` | **Sprach-Fallback invertiert (Regression ggü. Produktziel).** `main` lieferte allen Nicht-Deutsch-Sprechern Englisch (`strpos('de') ? de : en`); der PR liefert jeder nicht-deutschen/nicht-englischen Sprache (fr, es, ja, …) **Deutsch**. Ein französischer/spanischer Besucher bekam also Deutsch statt Englisch — entgegen der Absicht „Deutsche lesen Deutsch, alle anderen Englisch". Das plan-basierte Audit hat es nicht markiert, weil Plan (Phase-1-Kriterium) **und** AGENTS.md diese Umkehr ausdrücklich vorschrieben — ein Spec-Bug, kein Code-Bug. | **Gefixt (Variante B):** Deutsch-Signal **oder** signalfreie Anfrage (Bots/SEO-Crawler) → `de`; jede andere gestellte Sprache → `en`. `AGENTS.md` (Kopfzeile + Regel 3) und Docstring an das korrigierte Verhalten angepasst. Matrix verifiziert: `de-DE`→de, `en-US`→en, `fr-FR`→en, `es-ES`→en, kein Header→de. |
| 1 | minor | `public/content/cv.json` | Plan friert Inhalte ein („bleibt unverändert"), der PR ändert cv.json trotzdem: typografische Apostrophe, `company_de`-Ergänzungen, gekürzte `location`-Strings. Die `company_de`-Felder SIND im PR-Text deklariert; Apostrophe + Location-Kürzungen nicht. Fakten werden nicht verfälscht. | Keine Code-Änderung nötig (Inhalte korrekt und layoutdienlich). Dokumentiert hier; PR-Beschreibung sollte die kosmetischen cv.json-Änderungen ergänzend erwähnen (nicht ausgeführt — keine GitHub-Writes gewünscht). |
| 2 | minor | `public/assets/css/base.css:280` | `.contact-pill` hat `border-radius: 999px`; alle fünf Prototypen stylen den Kontakt-Button einheitlich mit `13px`. Spec-interner Konflikt: README-Tokentabelle sagt „999px (Chips, Kontakt-Pill)", die als „pixelgenau" deklarierten Prototypen sagen 13px. | **Gefixt**: auf 13px geändert (die Prototypen sind das gerenderte, freigegebene Zielbild; alle fünf sind konsistent). CSS-Kommentar dokumentiert den Spec-Konflikt. |
| 3 | minor | `public/templates/cv-page.php:122` | Regression gegenüber main: Volunteering-Zeitraum rendert `start–end` ohne „heute/present"-Fallback. Ein laufender Eintrag (`"end": null`) ergäbe „2014–" mit hängendem Strich. Experience und Education behandeln den Fall weiterhin. | **Gefixt**: Fallback auf `$l['present']` analog Education wiederhergestellt (inkl. Spatiierung bei Wort-Endpunkt). |
| 4 | nit | `public/assets/css/cv.css:152` | Halo des „laufend"-Dots nutzt `color-mix()` ohne Fallback — in Browsern ohne color-mix (Chrome <111, Safari <16.2) fällt die gesamte box-shadow-Deklaration weg. Anderswo im PR gibt es Fallbacks (article.css, base.css @supports). | **Gefixt**: statischer rgba-Fallback davor, konsistent mit article.css-Muster. |
| 5 | nit | `public/lib/lang.php:12` | `detectLang()` wertet `de;q=0` (RFC 9110: „not acceptable") als Deutsch-Signal; `en, de;q=0` liefert fälschlich Deutsch. Reproduziert per `php -r`. | **Gefixt**: Einträge mit `;q=0` werden vor dem Match entfernt. |
| 6 | nit | `public/templates/home.php:99` | Early-Grid parst das Jahr aus dem undokumentierten Titel-Format „Name / Jahr" in projects.json; ein künftiger Titel ohne ` / ` verlöre still die Jahresangabe. Funktioniert für alle 16 aktuellen Einträge. | **Gefixt**: Konvention in AGENTS.md (Regel 6) dokumentiert. |

## Geprüfte und verworfene Findings

- **Firefox-Gegentest der Theme-Transition offen**: Als Abweichung deklariert;
  die Plan-Mitigation (nur `background-color`/`color` transitionieren, nie das
  `background`-Shorthand) ist im Code verifiziert erfüllt. Kein Finding.
- **Blog-Teaser zeigt volle Beschreibungen statt der im Prototyp gekürzten
  Ein-Satz-Fassungen**: articles.json ist die kanonische Datenquelle; der Plan
  bindet den Teaser an `getLatestArticles()`/articles.json, nicht an
  handgekürzte Prototyp-Texte. Kein Finding.

## Bekannte, akzeptierte Follow-ups (aus dem PR-Text, bestätigt)

- `jotti.png`-Screenshot enthält die Marketingzeile „Kostenlos. Self-hosted.
  Open Source." — Wording-Konflikt liegt im Bild, nicht im Code; Fix braucht
  einen neuen Screenshot (deklariert, einverstanden).
- Favicon (navy „NG") passt nicht perfekt zur neuen Farbwelt — akzeptabel,
  deklariert.
