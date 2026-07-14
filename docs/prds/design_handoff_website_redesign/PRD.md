# PRD: Redesign nicograef.com

## 1. Hintergrund & Ziel
Die persönliche Website nicograef.com (Portfolio, Blog, Lebenslauf) wirkt gegenüber der neu gestalteten Produktseite jotti.rocks veraltet. Ziel ist ein komplettes visuelles Redesign mit derselben Design-DNA wie jotti (Space Grotesk + Inter, Eyebrows, weiche Glows, große Radien, Light/Dark), aber einer eigenen Farbwelt für die Personal Brand: warmer Papierton + Kobalt-Akzent + animierter Kobalt→Violett→Cyan-Verlauf.

**Primäre Zielgruppe:** Recruiter und potenzielle Auftraggeber (professionell-seriöser Ton). Sekundär: Entwickler, die die Blog-Artikel lesen.

## 2. Erfolgskriterien
- Website wirkt auf einen Blick modern und konsistent mit jotti.rocks, bleibt aber eigenständig erkennbar
- Recruiter finden in <10 Sekunden: Wer, was, wo, aktueller Fokus, Weg zum Lebenslauf/Kontakt
- Light- und Dark-Theme vollständig, Wahl bleibt über Seiten und Besuche erhalten
- Keine Regression: alle bestehenden Inhalte (9 Artikel, CV-Daten, Projekte) und URLs bleiben erreichbar
- Lighthouse: Performance/A11y/SEO ≥ 90 (Seite bleibt statisch, kein Framework)

## 3. Scope

### In Scope
1. **Startseite** — neuer Hero (Split: Text + großes Porträt, animierter Verlauf auf „Nico"), Blog-Teaser (3 neueste Artikel), Portfolio neu strukturiert: jotti als Featured-Card (dunkel, Verlaufs-Topline), Haufe/Idana als Karten-Paar, frühe Projekte als kompaktes 4er-Grid
2. **Blog-Übersicht** — nach Jahr gruppierte Liste (Datum / Titel + Beschreibung / Pfeil)
3. **Artikel-Detailseite** — Lese-Layout 760px, Meta-Zeile, Prose-Styles, Codeblöcke, Author-Card
4. **Lebenslauf** — Kopf mit Foto + Kontakt-Chips + Summary; Erfahrung als Timeline (aktive Stationen mit Kobalt-Dot), Sidebar mit Ausbildung/Sprachen/Zertifikaten/Ehrenamt
5. **404-Seite** — Verlaufs-„404" mit Sheen, Links zurück
6. **Theme-System** — Light (Default) + Dark, Toggle in der Nav, localStorage `ng-theme`, kein Flash beim Laden
7. **Deutsche Inhalte** als führende Sprache (bestehende Zweisprachigkeit DE/EN bleibt technisch erhalten, `*_de`-Felder)

### Out of Scope
- Neue Inhalte/Artikel, Copy-Überarbeitung über das Design hinaus
- CMS, Build-Pipeline- oder Hosting-Wechsel
- Kontaktformular (Kontakt bleibt mailto)
- Mobile-App-artige Features, Suche, Kommentare

## 4. Funktionale Anforderungen

### F1 Navigation (alle Seiten)
Header mit Logo „nico gräf." (Punkt im Akzent), Links: Portfolio (→ Startseite#portfolio), Artikel, Lebenslauf; Theme-Toggle (38×38, ☾/☀); Kontakt-Pill (mailto). Aktive Seite farblich markiert. Footer: Copyright + GitHub/LinkedIn/E-Mail.

### F2 Theme
- `data-theme` auf `<html>`, CSS-Variablen für beide Themes (Werte: README „Design Tokens")
- Toggle wechselt Theme sofort (Transition 350ms) und schreibt localStorage `ng-theme`
- Inline-Script im `<head>` liest den Key vor dem ersten Paint (kein FOUC)
- Ohne gespeicherte Wahl: Light (optional: `prefers-color-scheme` respektieren)

### F3 Startseite
Hero mit Eyebrow, H1 mit Verlaufs-Wort, Lead, 2 CTAs, Porträt mit Badge „10+ Jahre"; 2 animierte Glows. Blog-Teaser = 3 neueste aus `articles.json`. Portfolio-Hierarchie: jotti (featured) → Haufe/Idana → frühe Projekte (4 Karten mit Bild).

### F4 Blog
Übersicht listet alle Artikel aus `articles.json`, absteigend, nach Jahr gruppiert, Datum lokalisiert (z. B. „13. Juli"). Artikelseite rendert Markdown aus `public/content/articles/<slug>.md` mit den Prose-Styles der Referenz (`design/Artikel.dc.html`); Meta-Zeile mit Kategorie/Datum/Lesezeit (Lesezeit ≈ Wörter/200, gerundet).

### F5 Lebenslauf
Rendert `cv.json` (deutsche Felder): alle 7 Stationen chronologisch absteigend als Timeline, laufende Stationen visuell hervorgehoben; vollständige Tag-Chips; Sidebar-Blöcke Ausbildung, Sprachen, Zertifikate, Ehrenamt.

### F6 404
Statische Fehlerseite im neuen Design; Server liefert sie für unbekannte Routen mit Status 404.

## 5. Nicht-funktionale Anforderungen
- **Responsive:** Desktop-first-Referenz; unter ~900px stapeln Hero/Grids einspaltig, Nav darf zu kompakter Zeile/Burger reduzieren; seitliches Padding min. 20px; Schriftgrößen via clamp()
- **Performance:** Fonts self-hosted mit `font-display: swap`; Bilder lazy (außer Hero); keine JS-Frameworks — einziges JS: Theme-Toggle
- **A11y:** Kontraste AA (Prose/Muted auf bg geprüft), Fokus-Styles auf allen Interaktiven, Toggle mit `aria-label`, `prefers-reduced-motion` deaktiviert sheen/orb/rise
- **SEO:** Title/Description je Seite, OG-Bild (bestehendes `nico-social.jpg`), semantisches HTML (article, nav, time)
- **Browser:** aktuelle Chrome/Firefox/Safari. `color-mix()` wird vorausgesetzt; Fallback: g2/g3 als statische Hexwerte (#7a4bd9, #2a91d3) definieren

## 6. Offene Punkte
- EN-Variante: gleiche Templates, Texte aus den EN-Feldern — Umfang des EN-Rollouts klären
- Kategorie-Label je Artikel (aktuell nicht in articles.json — ableiten oder Feld ergänzen)
- Links der frühen Projekte (Referenz verlinkt pauschal auf GitHub-Profil; echte Ziel-URLs aus projects.json übernehmen)
