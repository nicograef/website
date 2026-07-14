# PRD: Redesign nicograef.com

Design-Quelle: Das Design-Handoff unter `docs/prds/design_handoff_website_redesign/`
(README mit Design-Tokens, fünf `.dc.html`-Referenzdateien, Umsetzungsplan) ist die
verbindliche, pixelgenaue Vorgabe für Farben, Typografie, Abstände, Copy und
Interaktionen. Diese PRD regelt, **was** umgesetzt wird und welche Entscheidungen
über das Handoff hinaus getroffen wurden — Design-Werte werden hier bewusst nicht
dupliziert.

## Problem Statement

Die persönliche Website nicograef.com (Portfolio, Blog, Lebenslauf) wirkt gegenüber
der neu gestalteten Produktseite jotti.rocks veraltet und visuell beliebig. Für die
primäre Zielgruppe — Recruiter und potenzielle Auftraggeber — vermittelt sie nicht
den Qualitätsanspruch, den Nicos Arbeit (insbesondere jotti) tatsächlich hat. Es
fehlen außerdem ein Dark Theme, eine klare visuelle Hierarchie im Portfolio (jotti
geht zwischen 17 anderen Projekten unter) und ein ruhiges Lese-Layout für die
deutschsprachigen Fachartikel.

## Solution

Komplettes visuelles Redesign aller fünf Seiten (Startseite, Blog-Übersicht,
Artikel-Detail, Lebenslauf, 404) mit derselben Design-DNA wie jotti.rocks
(Space Grotesk + Inter, Eyebrow-Labels, weiche Glows, große Radien), aber eigener
Farbwelt: warmer Papierton mit Kobalt-Akzent und animiertem
Kobalt→Violett→Cyan-Verlauf als Signature-Element.

Kern der Lösung:

- **Startseite** mit Split-Hero (Text + großes Porträt, Verlaufs-Wort „Nico“,
  „10+ Jahre“-Badge), Blog-Teaser (3 neueste Artikel) und neu hierarchisiertem
  Portfolio: jotti als dunkle Featured-Card mit Verlaufs-Topline, Haufe
  Akademie/Idana als Karten-Paar, darunter alle frühen Projekte als kompaktes Grid.
- **Blog-Übersicht** als nach Jahr gruppierte Liste (Datum / Titel + Beschreibung /
  Pfeil), **Artikel-Detail** als 760px-Lese-Layout mit Meta-Zeile (Datum · Lesezeit),
  Prose-Styles, Codeblöcken im neuen Token-Design und Author-Card am Ende.
- **Lebenslauf** mit Foto-Kopf, Kontakt-Chips und Summary, Berufserfahrung als
  Timeline (laufende Stationen mit Kobalt-Dot hervorgehoben), Sidebar mit
  Ausbildung, Sprachen, Zertifikaten und Ehrenamt.
- **404-Seite** mit animierten Verlaufs-Ziffern und Wegen zurück.
- **Light/Dark-Theme** mit Toggle in der Navigation, Persistenz über localStorage
  und Vor-Paint-Initialisierung (kein Aufblitzen des falschen Themes).
- **Zweisprachigkeit bleibt erhalten, Default kippt auf Deutsch:** Startseite,
  Lebenslauf und 404 rendern weiterhin DE oder EN je nach Browser-Sprache, aber
  ohne eindeutige Englisch-Präferenz wird Deutsch ausgeliefert. Der Blog-Bereich
  bleibt wie bisher vollständig deutsch.

Alle Inhalte (9 Artikel, CV-Daten, Projekte) und alle URLs bleiben unverändert
erreichbar; die Content-Pipeline (JSON + Markdown) wird nicht angetastet.

## User Stories

1. Als **Recruiter** möchte ich auf der Startseite in wenigen Sekunden erfassen,
   wer Nico ist, was er macht und wo ich Lebenslauf und Kontakt finde, damit ich
   ihn schnell einschätzen kann.
2. Als **Recruiter** möchte ich einen Lebenslauf mit klarer Timeline, visuell
   hervorgehobenen laufenden Stationen und einer Sidebar (Ausbildung, Sprachen,
   Zertifikate, Ehrenamt), damit ich den Werdegang auf einen Blick erfasse.
3. Als **potenzieller Auftraggeber** möchte ich jotti als prominentes
   Featured-Projekt mit Tech-Chips und Link zur Produktseite sehen, damit ich
   Nicos aktuelles Hauptprojekt sofort erkenne.
4. Als **potenzieller Auftraggeber** möchte ich unterhalb der Hauptprojekte alle
   frühen Projekte und Experimente als kompaktes Karten-Grid mit Bild, Name und
   Link sehen, damit ich Nicos Breite und Neugier nachvollziehen kann, ohne dass
   sie die Hauptprojekte verdrängen.
5. Als **Besucher** möchte ich zwischen Light- und Dark-Theme wechseln können,
   wobei meine Wahl über Seitenwechsel und Besuche hinweg erhalten bleibt und beim
   Laden nichts aufblitzt, damit die Seite meinen Sehgewohnheiten entspricht.
6. Als **Blog-Leser** möchte ich eine nach Jahren gruppierte Artikelübersicht,
   damit ich schnell sehe, was aktuell ist und was Archiv.
7. Als **Blog-Leser** möchte ich Artikel in einem ruhigen Lese-Layout mit Datum
   und geschätzter Lesezeit lesen, damit ich Inhalte fokussiert aufnehmen kann.
8. Als **Blog-Leser** möchte ich am Artikelende eine Author-Card mit Foto,
   Kurzbio und Link zur Artikelübersicht sehen, damit ich den Autor einordnen und
   weiterlesen kann.
9. Als **englischsprachiger Besucher** ohne Deutsch-Präferenz im Browser möchte
   ich Startseite, Lebenslauf und 404-Seite auf Englisch sehen, damit ich die
   Inhalte verstehe.
10. Als **Besucher auf einem Mobilgerät** möchte ich alle Seiten einspaltig,
    lesbar und bedienbar sehen, damit die Website auch unterwegs funktioniert.
11. Als **Besucher mit reduzierter Bewegungspräferenz** möchte ich, dass
    Verlaufs-, Glow- und Entrance-Animationen deaktiviert sind, damit mich die
    Seite nicht belastet.
12. Als **Besucher einer unbekannten URL** möchte ich eine gestaltete 404-Seite
    mit Links zur Startseite und zu den Artikeln sehen, damit ich nicht in einer
    Sackgasse lande.
13. Als **Website-Betreiber** möchte ich, dass alle bestehenden URLs, Artikel und
    Inhalte erhalten bleiben und die Seite weiterhin ohne Framework und Build-Step
    auskommt, damit Links, SEO und der bewusst einfache Betrieb nicht brechen.

## Implementation Decisions

### Ansatz & Rahmen

- Umbau in der bestehenden Codebasis: nur Templates, CSS und minimales JS ändern
  sich; Entry-Point-/Helper-/Template-Struktur, Content-Pipeline (JSON + Markdown)
  und Deployment bleiben unverändert. Kein Framework, kein Build-Step, PHP
  7.4-kompatibel.
- Umsetzung phasenweise auf einem `redesign`-Branch (Phasen gemäß Umsetzungsplan
  im Handoff), ein gemeinsames Rollout am Ende — kein Mischbetrieb aus altem und
  neuem Design in Produktion.
- Die Änderung der Layout-Shell (betrifft jede Seite) ist durch diese PRD
  abgesegnet; das „Ask first“-Gate aus den Agent-Regeln ist damit erfüllt.

### Design-System

- CSS Custom Properties für beide Themes exakt nach den Token-Tabellen des
  Handoffs; Light ist Default, Dark über `data-theme="dark"` auf dem
  Wurzelelement. `color-mix()` für die Verlaufsstufen mit statischen
  Hex-Fallbacks.
- Keyframes `sheen`, `orb`, `rise` zentral im Basis-Stylesheet; ein
  `prefers-reduced-motion: reduce`-Block deaktiviert alle drei.
  Entrance-Animationen (`rise`) nur auf Hero-/Kopfelementen.
- Wiederkehrende Bausteine (Eyebrow, Primär-/Outline-Button, Karte, Chip,
  Verlaufs-Text, Glow) als geteilte Klassen im Basis-Stylesheet; seitenspezifische
  Styles bleiben in den vorhandenen Per-Page-Stylesheets (bestehende
  `pageStyles`-Mechanik unverändert).
- Fonts Space Grotesk, Inter und JetBrains Mono werden self-hosted (woff2,
  `font-display: swap`); Montserrat entfällt komplett, inklusive Preloads.

### Theme-System

- Einziger clientseitiger State: `data-theme` auf dem Wurzelelement, persistiert
  unter dem localStorage-Key `ng-theme`.
- Ein Inline-Script im `<head>` (vor den Stylesheet-Links) liest den Key und setzt
  das Attribut vor dem ersten Paint; ohne gespeicherte Wahl gilt Light.
- Der Toggle-Button in der Navigation ist das einzige Script der Website
  (kleine eigene JS-Datei); Icon-Wechsel ☾/☀ rein über CSS statt JS-Rerender.
- Theme-Übergang transitioniert `background-color` und `color` (350ms) — bewusst
  nicht das `background`-Shorthand (bekannter Firefox-Bug mit CSS-Variablen).

### Sprache

- Die Sprachauflösung bleibt Header-basiert, der Default kippt von Englisch auf
  Deutsch: Deutsch-Signal im Header → DE; sonst Englisch-Signal → EN; sonst
  (leer/andere Sprachen) → DE.
- Startseite, Lebenslauf und 404 sind zweisprachig; die bestehende
  Feld-Mechanik (Basisfeld EN, `_de`-Suffix) und die Label-Helper werden
  weiterverwendet und um neue UI-Strings ergänzt.
- Neue sichtbare Copy (Hero-Eyebrow/-Lead, Badge, Author-Card-Kurzbio,
  404-Texte, Button-Beschriftungen) übernimmt die deutsche Fassung wortgleich aus
  den Design-Dateien; die englische Fassung wird neu formuliert, wobei Fakten
  ausschließlich aus den bestehenden Content-JSONs stammen (keine erfundenen
  Titel, Skills oder Zahlen).
- Blog-Übersicht und Artikelseiten bleiben wie bisher vollständig deutsch
  (Inhalte sind deutsch).

### Navigation & Shell

- Header auf allen Seiten: Logo „nico gräf.“ (Punkt im Akzent, verlinkt zur
  Startseite), Links Portfolio (→ Startseite `#portfolio`), Artikel, Lebenslauf,
  Theme-Toggle mit `aria-label`, Kontakt-Pill (mailto). Aktive Seite wird über
  die bestehende Seitenkennung farblich markiert.
- Footer auf allen Seiten: Copyright links; rechts GitHub, LinkedIn, Xing,
  Medium und E-Mail (Entscheidung: Xing und Medium bleiben erhalten und wandern
  aus der alten Kopfnavigation in den Footer; das Design nennt nur drei Links —
  die zwei zusätzlichen folgen demselben Stil).
- Der bisherige seitenfüllende Header (Profilbild + H1 + Untertitel) entfällt;
  das Porträt lebt künftig im Hero der Startseite bzw. im Kopf des Lebenslaufs.

### Seiten

- **Startseite:** Hero, Blog-Teaser und Portfolio gemäß Design. Blog-Teaser nutzt
  den bestehenden „neueste Artikel“-Helper (3 Einträge). Die jotti-Featured-Card
  verwendet feste Dunkel-Farben in beiden Themes (im Dark Theme zusätzlich mit
  Rahmen). Unter „Frühe Projekte & Experimente“ erscheinen **alle** Projekte mit
  `early`-Flag aus dem Projekte-JSON (aktuell 15) im Karten-Grid des Designs, in
  JSON-Reihenfolge, mit ihren echten Link-Zielen aus dem JSON — nicht nur die vier
  in der Design-Datei gezeigten.
- **Blog-Übersicht:** Alle Artikel aus dem Artikel-JSON, absteigend sortiert, per
  neuer reiner Helper-Funktion nach Jahr gruppiert; Datum innerhalb der Gruppe
  ohne Jahr, deutsch formatiert. Die gesamte Zeile ist verlinkt und hovert als
  Einheit.
- **Artikel-Detail:** Meta-Zeile zeigt **Datum · Lesezeit** — bewusst ohne
  Kategorie-Eyebrow (Entscheidung: kein Kategorie-Feld, keine Ableitung; das
  Design wird an dieser Stelle reduziert). Lesezeit = Wortzahl des
  Markdown-Inhalts / 200, gerundet, mindestens 1 Minute, als neue reine
  Helper-Funktion. Prose-, Codeblock- und Bild-Styles nach Design-Tokens. Die
  bestehende Gate-Logik (Syntax-Highlighting-Script nur bei vorhandenen
  Codeblöcken) bleibt unangetastet; das Highlight-Farbschema wird auf die neue
  Codeblock-Palette abgestimmt. Am Artikelende eine Author-Card (Foto, Name,
  Kurzbio, Link zur Übersicht).
- **Lebenslauf:** Kopf mit Foto, Name, Headline und drei Kontakt-Chips
  (E-Mail, GitHub, LinkedIn) plus Summary; Timeline aus den Erfahrungs-Einträgen,
  laufende Stationen (`end` = null) mit Akzent-Dot und Halo, beendete gedämpft;
  Zeiträume deutsch/englisch über den bestehenden Datums-Helper („Okt 2025 –
  heute“); vollständige Tag-Chips je Station. Sidebar mit Ausbildung, Sprachen,
  Zertifikaten, Ehrenamt aus den vorhandenen JSON-Abschnitten.
- **404:** Verlaufs-„404“ mit Sheen, Erklärtext, Buttons zur Startseite und zur
  Artikelübersicht; der Server liefert weiterhin Status-Code 404.

### Nicht-funktional

- **Responsive:** Desktop-Referenz; unter ~900px stapeln Hero, Grids und
  CV-Spalten einspaltig, die Navigation reduziert auf eine kompakte Zeile;
  Schriftgrößen über `clamp()`.
- **Performance:** Kein zusätzliches JS außer dem Theme-Toggle; Bilder lazy
  (außer Hero-Porträt); Ziel Lighthouse ≥ 90 in Performance, Accessibility, SEO.
- **A11y:** AA-Kontraste in beiden Themes, sichtbare Fokus-Styles auf allen
  interaktiven Elementen, `aria-label` am Toggle, `prefers-reduced-motion`
  respektiert.
- **SEO:** Bestehende Meta-/OG-/Canonical-Mechanik bleibt; Titel/Beschreibungen
  je Seite werden ans neue Wording angepasst; Sitemap und robots bleiben
  unverändert; semantisches HTML (article, nav, time).
- **Sicherheit:** Jede dynamische Ausgabe weiterhin über `htmlspecialchars()`;
  das Zugriffs-Schutzmodell der Produktion bleibt unangetastet.
- **Browser:** Aktuelle Chrome/Firefox/Safari; `color-mix()` vorausgesetzt,
  statische Fallback-Farbwerte definiert; Theme-Transition explizit in Firefox
  gegentesten.

## Testing Decisions

- Im Repo existiert kein automatisiertes Test-Framework, und es wird im Rahmen
  dieser PRD keines eingeführt (kein Package Manager, kein Build-Step). Das
  bestehende Qualitätstor — Syntax-Lint aller PHP-Dateien plus statische
  PHPStan-Analyse (`make check`) — muss durchgehend grün bleiben.
- Verifiziert wird ausschließlich externes Verhalten (die gerenderte Seite im
  Browser), nie Implementierungsdetails. Abnahmegrundlage ist die QA-Checkliste
  aus dem Handoff-Umsetzungsplan, insbesondere:
  - Theme-Toggle auf jeder Seite; Wahl übersteht Reload und Seitenwechsel; kein
    Aufblitzen des falschen Themes beim Laden.
  - Beide Themes auf allen fünf Seiten: Kontraste, Kartenränder, jotti-Card.
  - Firefox-Gegentest der Theme-Transition; `prefers-reduced-motion` deaktiviert
    alle Animationen.
  - Responsive-Prüfung bei 360px, 768px, 1280px, 1920px.
  - Alle 9 Artikel rendern korrekt (inkl. des längsten Artikels mit Codeblöcken
    und Bildern); interne Artikel-Links funktionieren; Lesezeit plausibel.
  - EN-Variante von Startseite, Lebenslauf und 404 bei englischer
    Browser-Sprache; Deutsch als Default ohne Sprachsignal.
  - Unbekannte URL liefert Status 404 mit neuer Fehlerseite.
  - Lighthouse ≥ 90 (Performance/A11y/SEO) auf Startseite und einem Artikel.
- Neue Logik (Lesezeit, Jahresgruppierung, Sprach-Default) wird als kleine reine
  Funktionen neben den bestehenden Helpern geschnitten — über Eingabe/Ausgabe
  manuell prüfbar und später isoliert testbar, falls doch einmal Testinfrastruktur
  eingeführt wird.

## Out of Scope

- Neue Artikel oder inhaltliche Copy-Überarbeitung über das Design hinaus;
  Übersetzung der Blog-Artikel ins Englische.
- Kategorie-Kennzeichnung von Artikeln (weder Datenfeld noch Ableitung) — die
  Artikel-Meta-Zeile kommt ohne Kategorie aus.
- Kuratierung oder Reduzierung der frühen Projekte; eine eigene
  Projekte-Unterseite.
- CMS, Build-Pipeline, Package Manager, Framework oder Hosting-/
  Deployment-Änderungen.
- Kontaktformular (Kontakt bleibt mailto), Suche, Kommentare, Newsletter.
- Einführung automatisierter Tests oder Test-Infrastruktur.
- Änderungen an Sitemap-/robots-Logik über bestehende URLs hinaus.

## Further Notes

- Bei jeder Detailfrage zu Farben, Abständen, Typografie oder Copy gilt: in den
  Design-Dateien des Handoffs nachschlagen, nie raten oder erfinden.
- Die Agent-Dokumentation des Repos (Beschreibung der Website als „Homepage
  English“, Font-Angaben, Header-Beschreibung) muss am Ende der Umsetzung an den
  neuen Stand angepasst werden (Default Deutsch, neue Fonts, neue Shell).
- Für jotti gilt unverändert die Sprachregelung: „source-available“ (nie „Open
  Source“) und „ausgelegt auf die KassenSichV“ (nie „konform“) — die Design-Copy
  hält das bereits ein.
- Das Handoff nennt als Assets auch Bilddateien, die im Repo bereits vorhanden
  sind; neue Bilder werden nicht benötigt. Die Font-Dateien (Space Grotesk,
  Inter, JetBrains Mono als woff2) sind die einzigen neuen Assets.
