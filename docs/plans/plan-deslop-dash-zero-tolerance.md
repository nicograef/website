# Plan: Zero-Tolerance Dash Rule in Deslop Skill

> Source PRD: n/a — task description from conversation

## Goal

Enforce a zero-tolerance policy for em dashes (`—`) across the entire deslop
skill and all existing articles. Every dash must be replaced by a
context-appropriate alternative (comma, colon, parentheses, new sentence).
No dash should remain after a deslop pass — not in article prose, not in
article titles embedded in links, and not in the skill files' own examples.

## Architectural decisions

- **Dash character in scope:** em dash with spaces (` — `, U+2014). En dashes
  (`–`) and hyphens (`-`) in compound words are unaffected.
- **Link title dashes:** treat as prose — replace in the display text of
  Markdown links even when the target article title contains a dash.
- **Skill file examples:** examples that demonstrate what to avoid must
  themselves avoid the pattern (eat your own dog food).
- **No new dash introduced while merging sentences** — use comma, colon, or
  subordinate clause instead.

## Inventory

| File | Dash count | Notes |
|---|---|---|
| `skills/deslop/text-de.md` | 23 | Rule section + examples both affected |
| `skills/deslop/text.md` | 18 | Rule section + examples both affected |
| `skills/deslop/SKILL.md` | 21 | Prose in skill description |
| `content/articles/java-fuer-typescript-entwickler-erklaert.md` | 53 | Highest count |
| `content/articles/spring-boot-fuer-typescript-entwickler-erklaert.md` | 42 | |
| `content/articles/bounded-context-erklaert.md` | 24 | |
| `content/articles/was-ist-domain-driven-design.md` | 24 | Already partially deslopped |
| `content/articles/anti-corruption-layer-erklaert.md` | 15 | |
| `content/articles/event-sourcing-am-beispiel-warenkorb-erklaert.md` | 1 | |
| `content/articles/was-ist-event-sourcing.md` | 0 | Already clean |

## Resolved decisions

- **Scope:** All 7 articles + all 3 affected skill files.
- **Link titles:** Replace dash in display text of internal Markdown links
  even when the target article's own title contains a dash.
- **Skill examples:** Update examples in skill files so they no longer
  contain the pattern they prohibit.
- **Exceptions:** None.

---

## Phase 1: Update skill files

### Context

- `skills/deslop/text-de.md:326–335` — *Gedankenstrich* section: soft
  "reconsider" wording; currently swaps em for en dash rather than eliminating it
- `skills/deslop/text.md:222–228` — *Em-dash overuse* section: "candidate
  for simplification", escape hatch "keep where weaker" — both must go
- `skills/deslop/text-de.md:385–392` — Satzrhythmus blockquote example
  uses a dash in the "good" variant (`— nicht wie es gespeichert wird`)
- `skills/deslop/SKILL.md` — prose throughout uses dashes; must be cleaned
  for consistency

### What to build

Replace the *Gedankenstrich* section in `text-de.md` and the *Em-dash
overuse* section in `text.md` with a zero-tolerance rule and a replacement
table (one row per dash context: parenthetical, trailing addition, conjunction,
contrast, definition, enumeration). Add "Ausnahmen: Keine." / "Exceptions:
none." explicitly.

Update all dashes in skill file prose and examples so the files practise
what they enforce.

### Acceptance criteria

- [x] `text-de.md` *Gedankenstrich* section states zero-tolerance with
  replacement table; no "reconsider", no en-dash swap rule
- [x] `text.md` *Em-dash overuse* section replaced with zero-tolerance rule
  and replacement table; "keep where weaker" removed
- [x] Satzrhythmus example in `text-de.md` demonstrates dash-free "good" variant
- [x] `grep -c " — " skills/deslop/text-de.md` returns 0 (or only inside
  code-span examples showing what to avoid)
- [x] `grep -c " — " skills/deslop/text.md` returns 0 (same caveat)
- [x] `grep -c " — " skills/deslop/SKILL.md` returns 0

---

## Phase 2: Deslop high-count articles

### Context

- `content/articles/java-fuer-typescript-entwickler-erklaert.md` — 53 dashes
- `content/articles/spring-boot-fuer-typescript-entwickler-erklaert.md` — 42 dashes
- `content/articles/bounded-context-erklaert.md` — 24 dashes

These three files account for the majority of the work and are processed
first because errors compound when the same pattern recurs at high density.

### What to build

For each article, replace every ` — ` occurrence with the context-appropriate
alternative from the replacement table. Process article titles in Markdown
link display text as well.

Each replacement must be a real edit — not a mechanical substitution. The
resulting sentence must read naturally.

### Acceptance criteria

- [x] `grep -c " — " content/articles/java-fuer-typescript-entwickler-erklaert.md` → 0
- [x] `grep -c " — " content/articles/spring-boot-fuer-typescript-entwickler-erklaert.md` → 0
- [x] `grep -c " — " content/articles/bounded-context-erklaert.md` → 0
- [x] No information lost; no new sentence-merges introduce a dash
- [x] Internal link display texts with dashes updated

---

## Phase 3: Deslop remaining articles

### Context

- `content/articles/was-ist-domain-driven-design.md` — 24 dashes
- `content/articles/anti-corruption-layer-erklaert.md` — 15 dashes
- `content/articles/event-sourcing-am-beispiel-warenkorb-erklaert.md` — 1 dash

### What to build

Same approach as Phase 2. The single dash in
`event-sourcing-am-beispiel-warenkorb-erklaert.md` is handled here for
completeness.

### Acceptance criteria

- [x] `grep " — " content/articles/was-ist-domain-driven-design.md` → no matches
- [x] `grep " — " content/articles/anti-corruption-layer-erklaert.md` → no matches
- [x] `grep " — " content/articles/event-sourcing-am-beispiel-warenkorb-erklaert.md` → no matches
- [x] All seven articles pass `grep -rL " — " content/articles/` (i.e. appear in the "no match" list)

---
