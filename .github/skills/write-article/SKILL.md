---
name: write-article
description: >-
  Use when the user wants to write a new German-language blog article for
  nicograef.com, rewrite an existing one, or turn research and session notes
  into an article on a software architecture or developer-workflow topic.
  Articles live in public/content/articles/ with metadata in articles.json.
---

# Write Article

Write a German-language software architecture article for nicograef.com.
The output is a Markdown file in `public/content/articles/<slug>.md` plus a
metadata entry at the top of `public/content/articles.json`.

You may skip steps if you don't consider them necessary.

## Workflow

### 1. Receive Input

Accept the following inputs from the user — all may be provided at once:

- **Thema** (required) — the topic or concept to write about
- **Quellen-URLs** (optional) — a list of source URLs to fetch and learn from
- **Session-Notizen / Entwurf** (optional) — prior research, outlines, or
  session context (e.g. a conversation with an AI assistant)

**Rewrite eines bestehenden Artikels:** Wenn der Auftrag ist, einen
vorhandenen Artikel zu überarbeiten, die alte Version als zusätzliche Quelle
lesen (Datei oder `git show <ref>:<path>`). Stärken und Schwächen der alten
Version identifizieren und im Recherche-Protokoll (Schritt 2) dokumentieren.

**Wenn keine URLs angegeben wurden:** Perform a web search for 3–5
authoritative sources on the topic (official documentation, well-known DDD /
architecture references, high-quality blog posts). Present each candidate with
its URL and a one-sentence description of its content focus. Ask the user to
confirm which sources to fetch before proceeding to Step 2.

### 2. Web Research

Fetch all confirmed source URLs with your web-fetch tool. Read and
synthesize the content into a **Recherche-Protokoll**.

**Recherche-Protokoll format:**

1. **Kernerkenntnisse** — 5–8 key findings from the sources: core definitions,
   mental models, notable real-world examples, common nuances or
   misconceptions. Cite the source URL inline for each finding.
2. **Terminologie-Liste** — die 2–3 zentralen Fachbegriffe, die im Artikel
   eingeführt und erklärt werden (see Style Guide, Terminologie-Konsistenz).
   One term per line: `**Begriff** — kurze deutsche Erklärung`.
3. **Artikeltyp-Empfehlung** — recommend one of the three article types
   ("Was ist X?", Deep Dive, Pattern-Artikel) with 1–2 sentences of reasoning.
4. **Titelvorschläge** — propose 2–3 German `# Title` options following the
   pattern `[Konzept] — [Nutzen für Leser]`. Mark one as recommended.
5. **Praxisbeispiel aus jotti** — filled in Step 3 (see below).

**Session-Notizen / Entwurf:** If the user also provided prior session notes
or a draft, integrate them into the Recherche-Protokoll — treat them as an
additional source and note any differences or gaps compared to the fetched
URLs.

**Bestehende Artikel prüfen:** Vor dem Weitergehen die vorhandenen Artikel
in `public/content/articles/` scannen. Wenn ein Teilthema bereits einen eigenen
Artikel hat, im neuen Artikel nicht erneut erklären — nur erwähnen.

Do not present the Recherche-Protokoll yet — Step 3 adds the Praxisbeispiel
section first.

### 3. Praxisbeispiel aus jotti suchen

Nicos eigenes Produkt **jotti** (`~/r/jotti`) ist die bevorzugte Quelle für
Praxisbeispiele. Die Artikel sollen zeigen, dass Nico die Konzepte selbst
anwendet — nicht nur Theorie referiert.

Search the jotti repo for a real example that matches the topic. Good entry
points:

| Thema | Wo suchen |
|-------|-----------|
| DDD, Bounded Contexts, Schichtenarchitektur | `backend/domain/` (ein Go-Paket pro Kontext: kasse, produkt, tse, betreiber, …), `docs/handbuch.md` |
| Event Sourcing, Kassenjournal, TSE | `docs/handbuch.md`, `AGENTS.md` (Regeln, z. B. append-only Kassenjournal), `backend/domain/kasse/` |
| Ubiquitous Language, Namenskonventionen | `docs/language.md` |
| AI-Workflows, Planung, PRDs, Skills | `docs/plans/`, `docs/prds/`, `.github/` |
| Produkt- und Compliance-Kontext | `AGENTS.md`, `docs/compliance.md`, `docs/anforderungen.md` |

Rules:

- **Verifizieren statt vermuten.** Jede Aussage über jotti muss durch eine
  reale Datei oder ein reales Verzeichnis belegt sein. Verzeichnisbäume vor
  dem Zeichnen mit `ls`/`tree` gegen das Repo prüfen.
- **Wording:** jotti ist **source-available** (nie „Open Source“), „ausgelegt
  auf die <abbr>KassenSichV</abbr>“ (nie „konform“).
- **Kein Bezug erzwingen.** Wenn das Thema keinen echten jotti-Bezug hat,
  diesen Schritt mit einem Begründungssatz im Protokoll überspringen.

Fill in Recherche-Protokoll item 5 with 1–2 candidates, each with:
(a) was das Beispiel zeigt, (b) Belege (Datei-/Verzeichnispfade),
(c) vorgeschlagene Darstellungsform (Prosa, Verzeichnisbaum, simulierte
CLI-Session, Tabelle — see Style Guide, Visuals).

Then present the complete Recherche-Protokoll and ask:
*"Sollen wir mit diesen Erkenntnissen, [empfohlenem Titel] und
[Praxisbeispiel] fortfahren, oder möchtest du Titel, Artikeltyp,
Terminologie oder Beispiel anpassen?"*

Do not proceed to Step 4 until the user confirms.

### 4. Create an outline

Based on the Recherche-Protokoll from Steps 2–3, present the proposed article
structure as a numbered heading list. Include a one-sentence summary per
section. Mark where the jotti-Praxisbeispiel lands and list any planned
visuals (form + which insight each one carries that prose cannot). Ask the
user to confirm or adjust before writing.

> **Pattern-Artikel:** Folgt der Outline dem Pattern-Artikel-Typ, muss sie
> der Pflicht-Struktur aus dem Style Guide folgen: Einleitung: Das Problem →
> Das Pattern → Praxisbeispiel → Wann lohnt sich das? → Fazit.

**Konzept-Dichte-Check:** Zähle die neuen Fachbegriffe, die in der Outline
eingeführt und erklärt werden sollen. Sind es mehr als 2–3, ist der Artikel
zu breit. Entscheidung: Begriffe in eigene Artikel auslagern oder im
Artikel nur erwähnen ohne Erklärung.

### 5. Write the article

Write the full article following the Style Guide below. Save it to
`public/content/articles/<slug>.md`.

**Slug convention:** German, kebab-case, descriptive
(e.g. `was-ist-event-sourcing`, `event-sourcing-am-beispiel-warenkorb-erklaert`).

### 6. Self-review

Before saving the file, read through the finished article paragraph by paragraph
and check each of the following points. Fix any issues found inline.

1. **Satzlänge** — Sätze mit mehr als ~25 Wörtern aufteilen.
2. **Füllwörter** — Streiche: "grundsätzlich", "eigentlich", "sozusagen",
   "im Grunde", "Es ist wichtig zu beachten, dass…", "Man kann sagen, dass…".
3. **Jargon-Dichte** — Maximal 2 uneingeleitete Fachbegriffe pro Absatz.
   Neue Begriffe beim ersten Vorkommen fett setzen und kurz erklären.
4. **Zweiter-Lese-Test** — Jeden Satz, der einen zweiten Leseversuch
   benötigt, umschreiben.
5. **Konzept-Dichte** — Maximal 2–3 neue Fachbegriffe werden im gesamten
   Artikel eingeführt und erklärt. Jeden zusätzlichen Begriff streichen oder
   nur nennen ohne Erklärung.
6. **Zwischenfazit-Check** _(nur Deep Dive)_ — Jede `##`-Sektion endet mit
   einem Übergangssatz zur nächsten Sektion.
7. **Terminologie** — Alle Begriffe müssen konsistent mit der Begriffsliste
   aus Schritt 2 (Recherche-Protokoll, Terminologie-Liste) verwendet
   werden.
8. **Gedankenstrich-Check** — Kein „—“ und kein „–“ im Fließtext. Jeden
   Fund auflösen: Komma, Doppelpunkt, Klammern oder eigener Satz.
9. **Stimmen-Check** — Mindestens ein echter Ich-Anker (eigene Erfahrung,
   jotti). Keine LLM-Brücken („Soweit die Theorie…“), keine Fazits, die nur
   wiederholen, keine Staccato-Punchlines.
10. **Visual-Check** — Jeder Codeblock hat einen Sprach-Tag; ASCII-Art und
    Terminal-Sessions sind als ` ```text ` getaggt. Jedes Visual besteht den
    Mehrwert-Test und ist in Prosa eingebettet (Satz davor, Bezug danach).

Gib ein kurzes **Self-Review-Protokoll** aus — eine Liste der vorgenommenen
Änderungen (z. B. "Satz in Abschnitt X aufgeteilt", "Füllwort 'eigentlich'
 entfernt"). Speichere die Datei erst nach Ausgabe des Protokolls.

### 7. Post-write checklist

#### SEO Description

Propose the `description` value for `articles.json`. Rules:

- **Max. 155 characters** — show the character count next to the proposal.
- Answer the article's core question in one complete sentence.
- Make the reader want to click (concrete benefit, not a vague summary).

| | Beispiel |
|---|---|
| ❌ Schlecht | `"Ein Artikel über Event Sourcing und wie es funktioniert."` (57 Zeichen) |
| ✅ Gut | `"Wie du mit Event Sourcing jeden Zustandswechsel nachvollziehbar speicherst und warum das dein Debugging revolutioniert."` (119 Zeichen) |

Present the proposal as: `"<description>"` **(N Zeichen)**. Adjust until it
fits within 155 characters and the user approves it.

#### articles.json entry

After the user approves the description, insert the entry yourself at the
**top** of the array in `public/content/articles.json` (newest first):

```json
{
    "slug": "<slug>",
    "title": "<exact H1 title from the article>",
    "description": "<approved SEO description, max 155 characters>",
    "date": "<YYYY-MM-DD>"
}
```

The schema has exactly these four fields — no `author`, no `tags`. Match the
existing 4-space indentation.

#### Checklist

After completing the above, remind the user:

- [ ] Verify the article renders correctly with the dev server
      (`php -S 0.0.0.0:8080 -t public router.php`) — especially any
      ASCII-Art blocks (no syntax coloring, no horizontal scrolling on
      mobile width).

## Style Guide

These rules are extracted from the existing articles and must be followed
consistently.

### Language & Tone

- **German** throughout. Informal "Du"-Anrede.
- **Simple, clean, natural language.** Write like you would explain something
  to a colleague over coffee — clear and direct, but not sloppy. Avoid overly
  academic or stiff phrasing. Prefer short sentences. If a sentence needs a
  second read to understand, rewrite it.
- No filler phrases ("Es ist wichtig zu beachten, dass…",
  "Grundsätzlich kann man sagen…"). Get to the point.
- Explain concepts in a way that is accessible to developers who are new to
  the topic, without being condescending.
- Use **analogies** to make abstract concepts tangible
  ("Stell dir X wie Y vor: …").
- **Terminologie-Konsistenz** — Lege vor dem Schreiben eine Begriffsliste der
  Kern-Fachbegriffe fest (z. B. "Event Store", "Aggregate", "Command"). Einmal
  gewählte Terme werden durchgängig verwendet. Synonyme sind verboten — außer
  bei der einführenden Erklärung eines Begriffs
  (z. B. "Ein **Event Store**, auch *Ereignisspeicher* genannt, ist …").
- **Konzept-Dichte** — Maximal 2–3 neue Fachbegriffe pro Artikel einführen
  und erklären. Weitere Fachbegriffe nur erwähnen ohne Erklärung — oder
  ganz weglassen.

### Stimme (Nicos Stil)

Die Artikel sprechen mit Nicos persönlicher Stimme, nicht im neutralen
Lehrbuchton. Referenztexte: der Warenkorb-Event-Sourcing-Artikel und der
Workflow-Artikel (`workflow-ki-coding-agents.md`).

- **Ich-Perspektive und eigene Erfahrung als Anker** — „Mir ist das oft genug
  passiert…“, „Für jotti, mein Kassensystem für Vereine, …“. Fakten über Nico
  kommen ausschließlich aus `public/content/cv.json` und
  `public/content/projects.json`. Anekdoten nie erfinden — im Zweifel
  Platzhalter setzen und den User fragen.
- **Ehrliche Hedges und Skepsis gegen Overengineering** — „zumindest in der
  Theorie“, „Seien wir ehrlich“, „CRUD reicht oft“-Disclaimer. Ein Artikel,
  der nur Vorteile aufzählt, ist keiner.
- **Keine LLM-Brücken** — keine rhetorischen Überleitungen wie „Soweit die
  Theorie…“, keine Fazits, die den Artikel nur wiederholen, keine
  Staccato-Punchlines.
- **Keine Gedankenstriche im Fließtext** — weder „—“ noch „–“. Einschübe
  auflösen in Komma, Doppelpunkt, Klammern oder einen eigenen Satz.
  (Diese SKILL.md nutzt Gedankenstriche in Anweisungen; im Artikeltext
  sind sie tabu.)

### Title & Opening

- First line: `# Title` — must match the `title` field in articles.json.
- Opening: 1–2 paragraphs that explain the concept in plain, accessible
  language. No preamble ("In diesem Artikel…") — get to the point.
- If the article has a limited scope, state it early as a blockquote:
  `> Diese Erklärung wendet sich an …`

### Structure

- `##` for main sections, `###` for subsections.
- Keep a logical flow: concept → explanation → example → (optional)
  advantages/disadvantages.
- **Fließtext bevorzugt** — Schreibe primär in Prosa. Aufzählungen, Tabellen
  und Code-Blöcke nur einsetzen, wenn sie echten Mehrwert bieten, der sich
  in Fließtext nicht ausdrücken lässt. Struktur um der Struktur willen
  vermeiden.

### Hands-on & Praxisbezug

- Die Artikel zeigen, dass Nico die Konzepte selbst anwendet. jotti ist der
  primäre Praxisanker (siehe Workflow Schritt 3); alle jotti-Aussagen sind
  durch reale Dateien im Repo belegt.
- **jotti-Querverweis am Artikelende** ist erwünscht: kurzer Absatz mit Link
  auf [jotti.rocks](https://jotti.rocks) und/oder
  [github.com/nicograef/jotti](https://github.com/nicograef/jotti), inklusive
  „source-available“ und „ausgelegt auf die KassenSichV“. Wording von Artikel
  zu Artikel variieren — nicht dreimal derselbe Satz.
- Self-links use **nicograef.com** (never nicograef.de).

### Visuals: ASCII-Art, Terminal-Sessions & Fake-Screenshots

Gemäßigt eingesetzte Text-Visuals machen Artikel greifbarer. Es gibt keine
feste Obergrenze — aber jedes Visual muss den **Mehrwert-Test** bestehen:
Es zeigt Struktur, Ablauf oder Look-and-feel, die Prosa nicht transportieren
kann. Im Zweifel weglassen; „Fließtext bevorzugt“ gilt weiter.

Ein gutes Visual besteht aus drei Teilen: ein Satz davor, der sagt, was
gleich zu sehen ist; der Block selbst; danach ein Bezug auf ein Detail aus
dem Block.

**Formen:**

- **Verzeichnisbaum** — z. B. die DDD-Struktur des jotti-Backends. Nur reale,
  vorher verifizierte Struktur zeichnen; Unwichtiges weglassen statt erfinden.
- **Simulierte Terminal-/CLI-Session** — z. B. eine Claude-Code-Session mit
  Prompt-Zeilen (`$`, `>`) und gekürzter Ausgabe. Darf kondensiert sein, muss
  aber plausibel bleiben und echtem Tool-Verhalten entsprechen; Kürzungen
  mit `…` markieren.
- **Fake-Screenshot als Textbox** — ein UI-Zustand als gerahmte ASCII-Box
  (z. B. das Kassen-Display einer Bestellung).

**Technik:**

- Jeder Fence bekommt einen Sprach-Tag. ASCII-Art, Bäume, Sessions und
  Textboxen: ` ```text ` (das vendored highlight.js registriert `plaintext`
  mit Alias `text` und deaktivierter Auto-Erkennung). Reine Befehlsfolgen:
  ` ```bash `. Echter Code: die jeweilige Sprache. **Nie ohne Tag** —
  `hljs.highlightAll()` würde den Block per Auto-Detect einfärben und
  ASCII-Art zerschießen.
- Zeilen unter ~60 Zeichen halten, damit der Block auf dem Smartphone ohne
  horizontales Scrollen lesbar bleibt.
- Jeder Codeblock (auch ASCII-Art) triggert das `$hasCode`-Gate und lädt
  highlight.js — das ist in Ordnung, solange der Tag stimmt.

**Form-Beispiel** (Struktur vor Verwendung im Artikel gegen das echte Repo
prüfen):

```text
backend/
├── api/          HTTP-Schicht: Handler und DTOs
├── domain/       Fachlogik, ein Paket pro Bounded Context
│   ├── kasse/
│   ├── produkt/
│   └── tse/
└── repository/   Persistenz (sqlc, PostgreSQL)
```

Eine simulierte CLI-Session nutzt dieselbe Form: Eingaben mit `$` oder `>`,
Werkzeug-Ausgaben eingerückt, Gekürztes mit `…`.

### Formatting Conventions

| Element | Convention |
|---------|-----------|
| Technical terms | **Bold** on first use, with German explanation if English |
| Abbreviations | `<abbr title="Full Name">ABBR</abbr>` |
| Code examples | Fenced code blocks, always with language tag (```go, ```sql, ```text, …) — see Visuals section |
| Tables | Markdown tables for comparisons, data examples, API endpoints |
| Blockquotes | For disclaimers, scope notes, tips (`> **Disclaimer:** …`) |
| Pros/Cons lists | Bulleted list with **bold keyword**: explanation |
| Inline code | Backtick-wrapped for identifiers, file names, commands |
| Gedankenstriche | Keine „—“/„–“ im Fließtext: auflösen in Komma, Doppelpunkt, Klammern oder eigenen Satz |
| Anführungszeichen | Deutsche Anführungszeichen („…“) im Fließtext |

### Content Patterns

- **"Was ist X?" articles** (overview): Define the concept, give a short
  example, list advantages and disadvantages. Keep it concise.
- **"X am Beispiel Y erklärt" articles** (deep dive): Start with the
  traditional approach (e.g. CRUD), show its limitations, then introduce the
  new approach with a worked example. Include code, tables, and step-by-step
  walkthroughs.
- **Disclaimer pattern**: If the topic could be over-applied, add a
  disclaimer early: `> **Disclaimer:** X hat, wie alles, seine Vor- und
  Nachteile …`
- **Problem-first Hook** — Jeder Artikel beginnt mit einem konkreten Problem
  oder Pain Point, nicht mit einer Definition. Beispiel: Statt "Event Sourcing
  ist ein Muster, bei dem …" → "Du hast einen Bug in der Produktion. Du weißt,
  dass etwas schiefgelaufen ist, aber du kannst nicht mehr nachvollziehen,
  wann und warum."
- **Zwischenfazit** _(nur Deep Dive)_ — Jede `##`-Sektion endet mit einem
  Übergangssatz, der das Gelernte kurz zusammenfasst und auf die nächste
  Sektion hinleitet. Beispiel: "Damit hast du gesehen, wie Events gespeichert
  werden. Als Nächstes schauen wir uns an, wie man den aktuellen Zustand
  daraus rekonstruiert."
- **Pattern-Artikel** _(dritter Artikel-Typ)_ — Neben "Was ist X?" und Deep
  Dive gibt es Pattern-Artikel, die ein konkretes Entwurfsmuster beschreiben.
  Pflicht-Struktur:
  1. **Einleitung: Das Problem** — Welches konkrete Problem löst das Pattern?
  2. **Das Pattern** — Wie funktioniert die Lösung?
  3. **Praxisbeispiel** — bevorzugt aus jotti (Workflow Schritt 3): Code,
     Verzeichnisbaum, Tabelle oder Schritt-für-Schritt-Walkthrough.
  4. **Wann lohnt sich das?** — Enthält: Kriterien (wann passt das Pattern),
     Gegenindikatoren (wann lieber nicht).
  5. **Fazit** — Kurze Zusammenfassung.

### What NOT to include

- No YAML frontmatter in the Markdown file (metadata lives in articles.json).
- No table of contents (the website does not render one).
- No author byline in the article body (handled by the template).
- No date in the article body (handled by articles.json).
- No glossary.
- No raster images or image placeholders — ASCII-Diagramme in getaggten
  Codeblöcken sind erlaubt (see Visuals section).
- No cross-links to other articles.

## Constraints

- **Create the Markdown file; update `articles.json` only in Step 7** after
  the user approved the SEO description. Do not modify templates or PHP code.
- **Verify jotti facts against `~/r/jotti`** before they go into the article —
  never invent structure, behavior, or anecdotes.
- **Follow existing patterns exactly.** When in doubt, match what the existing
  articles do.

## Quality

- The self-review in Step 6 is the primary quality gate for article content.
  Complete all check points and output the protocol before saving.
- After task completion, include a human-readable summary paragraph alongside
  the commit message (see AGENTS.md, Git Workflow).
