---
name: deslop
description: >-
  Remove AI-generated slop from code, documentation, and other content.
  Use when reviewing AI-generated output to clean up unnecessary comments,
  defensive code, type hacks, style inconsistencies, puffery, and LLM
  vocabulary. Triggers: "deslop", "remove slop", "clean up AI code",
  "review for slop", "remove AI writing".
---

# Deslop

Remove AI-generated slop from code, text, or other content. Scan the specified
files or diff and strip everything that feels imported rather than native.

This skill has separate reference files for different content types:

- [code.md](code.md): code-specific slop patterns and removal rules
- [text.md](text.md): prose and documentation slop patterns
- [config.md](config.md): config files, YAML, CI pipelines, IaC

Language-specific supplements (optional; use when the content language matches):

- [text-de.md](text-de.md): German prose and documentation slop patterns

## Workflow

### 1. Determine scope

- If the user specifies files or a diff, use those.
- If not specified, check staged changes (`git diff --cached`).
- Fall back to recently modified files if nothing is staged.

### 2. Identify content type and language

For each file, determine which reference to apply:

| Content type | Reference | Examples |
|---|---|---|
| Code | [code.md](code.md) | `.go`, `.ts`, `.js`, `.py`, `.java`, `.sh` |
| Prose / docs | [text.md](text.md) | `.md`, `.txt`, `.adoc`, READMEs, comments in PRDs |
| Config / infra | [config.md](config.md) | `.yml`, `.yaml`, `.json`, `.toml`, `Dockerfile`, `.tf` |

If a file contains mixed content (e.g. inline docs in code), apply both
code and text rules to the relevant sections.

**Language detection:** If the prose is in German, apply
[text-de.md](text-de.md) instead of (or in addition to) [text.md](text.md).
Some patterns overlap; German-specific patterns take priority when they
conflict. For German comments embedded in code or config files, also apply the
German text patterns to those sections.

### 3. Read the file's voice

Before making changes, read the surrounding code or prose that was NOT
AI-generated. Every codebase and document has its own dialect. Match it:

- What is the existing comment style and density?
- What is the error-handling convention?
- What is the documentation tone and structure?
- How are config files annotated?

### 4. Remove slop and rewrite

Apply the rules from the appropriate reference file.

**Code and config:** Subtract only. Remove what does not belong; do not
rewrite logic or restructure.

**Prose and documentation:** Remove all slop vocabulary, filler, and puffery.
Then rewrite as a default, not a last resort. Uniform sentence rhythm,
symmetric paragraph structure, formulaic transitions, mechanical lists: all
are mandatory rewrite targets. A text that has slop words removed but retains
LLM prose architecture is not finished.

Rewrite aggressively: combine staccato sentences, break apart over-loaded ones,
vary sentence openings, dissolve symmetric structures that pad without adding.
After every rewrite pass, re-read the result. If it still sounds like an LLM
wrote it, go further. When in doubt, change more. Preserve all information:
rewrite the form, not the content.

### 5. Verify

- Confirm no functionality changed (code still compiles, tests pass).
- Confirm no information was lost (docs still say the same things).
- Re-read changed files: prose should read like a human wrote it, including
  natural sentence rhythm.
- Challenge the scope: if only a handful of sentences were touched in a
  multi-paragraph prose file, the pass was too conservative. Re-read and go
  further.

### 6. Report

End with a 1–3 sentence summary of what you changed and why the result is
cleaner.

## Principles

- **Preserve functionality:** never change what code does, only how it reads.
- **Preserve information:** never remove facts from docs, only rephrase slop.
- **Prefer clarity over brevity:** explicit readable code/prose beats compact
  cleverness.
- **Match the native voice:** every file has a dialect. Respect it.
- **Code: subtract, don't rewrite.** Remove foreign patterns without
  restructuring logic.
- **Prose: subtract and rewrite aggressively.** Removing slop vocabulary is
  the start, not the end. Rewrite every sentence and paragraph that still reads
  like LLM output. Default to more changes, not fewer. A conservative pass that
  removes three words and calls the text clean has failed.
- **Low threshold for intervention:** when a sentence could plausibly have been
  written by an LLM, rewrite it. Don't wait for five slop signals in a row.
- **Focus scope:** only touch specified files or recent changes unless told
  otherwise.

## Anti-Patterns

- Do not add new comments, abstractions, or error handling while deslopping.
- Do not refactor logic in code. This is a cosmetic pass, not a rewrite.
- Do not invent new content in prose. Rewrite form and rhythm only.
- Do not flag patterns that are genuinely idiomatic for the project, even if
  they happen to overlap with AI tells.
- Do not run AI detection tools. Use your judgment based on the reference
  files, not statistical classifiers.
