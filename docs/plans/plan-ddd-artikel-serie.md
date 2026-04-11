# Plan: DDD-Artikel-Serie neu aufstellen

> Source PRD: n/a (task description aus Konversation)

## Goal

Den bestehenden DDD-Übersichtsartikel radikal fokussieren (Kommunikationsproblem → Ubiquitous Language → wie sich DDD im Code anfühlt → wann lohnt es sich) und zwei neue Entwurf-Artikel anlegen, die die ausgelagerten Themen abdecken: strategisches DDD und taktisches DDD. Außerdem Learnings aus dem Prozess in den write-article Skill übertragen.

## Inventory

- `content/articles/was-ist-domain-driven-design.md` — der aktuelle Übersichtsartikel (überladen: strategisch + taktisch + Glossar + Cross-Links)
- `content/articles/bounded-context-erklaert.md` — existierender Artikel zu Bounded Context (strategisches DDD, muss nicht wiederholt werden)
- `content/articles/anti-corruption-layer-erklaert.md` — existierender Pattern-Artikel zu ACL
- `content/articles.json` — Metadaten aller Artikel
- `skills/write-article/SKILL.md` — der write-article Skill (Ziel für Learnings)
- `assets/img/articles/ddd-api-example.png` — vorhandenes Bild (API-Vergleich)
- `assets/img/articles/ddd-onion-architecture.png` — vorhandenes Bild (Onion-Architektur)
- `assets/img/articles/was-ist-domain-driven-design-strategic.png` — vorhandenes Bild (Domain/Subdomains/Bounded Contexts)
- Originalversion des Artikels via `git show 7b0f7b6:content/articles/was-ist-domain-driven-design.md`

## Resolved decisions

- Artikeltyp für DDD-Übersicht: "Was ist X?" (kurz, fokussiert)
- Titel: "Domain Driven Design: Wenn der Code die Fachsprache lernt"
- Nur 2 Fachbegriffe einführen und erklären: **Domain**, **Ubiquitous Language**
- Taktische/strategische Bausteine nur erwähnen, nicht einzeln erklären (eigene Artikel)
- Kein Glossar, keine Cross-Links (entspricht SKILL.md-Vorgabe)
- Die zwei neuen Artikel sind Entwürfe (Drafts), keine fertigen Artikel

---

## Phase 1: Write-Article Skill verbessern

### Context

- `skills/write-article/SKILL.md` — Gesamter Skill

### What to build

Drei Learnings aus dem DDD-Rewrite in den write-article Skill einarbeiten:

1. **Rewrite-Variante im Workflow:** Step 1 ("Receive Input") kennt nur Neuanlage. Ergänze eine "Rewrite"-Variante: Wenn ein bestehender Artikel überarbeitet werden soll, die alte Version als Quelle lesen (git history), Stärken und Schwächen identifizieren, und das Recherche-Protokoll darauf aufbauen.

2. **Konzept-Dichte schon im Outline prüfen:** Aktuell wird Konzept-Dichte erst in Step 5 (Self-Review) geprüft. Ergänze in Step 3 (Outline) einen Hinweis: Wenn die Outline mehr als 2–3 neue Fachbegriffe einführt, ist der Artikel zu breit. Entscheidung: aufteilen oder Begriffe nur erwähnen.

3. **Bestehende Artikel prüfen:** Ergänze in Step 2 oder 3 einen Hinweis: Vor dem Schreiben die vorhandenen Artikel in `content/articles/` scannen. Wenn ein Teilthema bereits einen eigenen Artikel hat, nicht erneut erklären.

### Acceptance criteria

- [ ] Step 1 enthält eine "Rewrite"-Variante mit Hinweis auf git history
- [ ] Step 3 enthält einen Konzept-Dichte-Check für die Outline
- [ ] Step 2 oder 3 enthält einen Hinweis, bestehende Artikel auf Überschneidungen zu prüfen

---

## Phase 2: DDD-Übersichtsartikel neu schreiben

### Context

- `content/articles/was-ist-domain-driven-design.md` — aktueller Artikel, wird ersetzt
- Originalversion aus `7b0f7b6` — als Orientierung für Ton und Fokus

### What to build

Den Artikel komplett neu schreiben nach dem "Was ist X?"-Pattern. Fokus auf das Kommunikationsproblem und die Ubiquitous Language. Taktische und strategische Bausteine nur in einem kurzen Ausblick-Absatz erwähnen. Anschließend deslop-Pass durchführen.

Workflow: Outline präsentieren → User bestätigt → Schreiben → Self-Review (7 Punkte) → Deslop → SEO-Description → articles.json aktualisieren.

### Acceptance criteria

- [ ] Artikel folgt "Was ist X?"-Pattern
- [ ] Maximal 2 Fachbegriffe eingeführt und erklärt (Domain, Ubiquitous Language)
- [ ] Problem-first Hook (kein "DDD ist...")
- [ ] Kein Glossar, keine Cross-Links
- [ ] Self-Review-Protokoll ausgegeben
- [ ] Deslop-Pass durchgeführt (text-de.md Regeln)
- [ ] SEO-Description vorgeschlagen (max. 155 Zeichen)
- [ ] articles.json aktualisiert

---

## Phase 3: Entwurf-Artikel — Strategisches DDD

### Context

- Inhalte aus dem aktuellen DDD-Artikel, die ausgelagert werden: Subdomains (Core/Supporting/Generic), Bounded Context (Verweis auf bestehenden Artikel), Context Map
- `content/articles/bounded-context-erklaert.md` — existiert bereits, Overlap vermeiden

### What to build

Einen kurzen Entwurf-Artikel (Draft) zum Thema strategisches DDD: Subdomains, Domain-Typen, Context Mapping als Überblick. Der Bounded Context wird nicht erneut erklärt, sondern verwiesen. Zielformat: Markdown-Datei als Grundlage für spätere Ausarbeitung.

### Acceptance criteria

- [ ] Datei angelegt unter `content/articles/strategisches-domain-driven-design.md`
- [ ] Enthält Outline + kurze Prosa-Absätze pro Sektion
- [ ] Kein Duplikat des Bounded-Context-Artikels
- [ ] Als Entwurf markiert (noch nicht in articles.json)

---

## Phase 4: Entwurf-Artikel — Taktisches DDD

### Context

- Inhalte aus dem aktuellen DDD-Artikel, die ausgelagert werden: Entity, Value Object, Aggregate, Repository, Domain Events
- Onion-Architektur-Idee passt thematisch hierhin

### What to build

Einen kurzen Entwurf-Artikel (Draft) zum Thema taktisches DDD: die Bausteine Entity, Value Object, Aggregate (mit Aggregate Root), Repository, Domain Event. Mit einem konkreten Beispiel (z.B. Bestellung). Zielformat: Markdown-Datei als Grundlage für spätere Ausarbeitung.

### Acceptance criteria

- [ ] Datei angelegt unter `content/articles/taktisches-domain-driven-design.md`
- [ ] Enthält Outline + kurze Prosa-Absätze pro Sektion
- [ ] Ein durchgängiges Beispiel (z.B. Bestellung)
- [ ] Als Entwurf markiert (noch nicht in articles.json)
