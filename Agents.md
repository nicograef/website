# Agents.md

Personal portfolio and blog for **Nico Gräf** (nicograef.com). Zero-dependency, no-build-step, file-based vanilla PHP website. Homepage is English (portfolio), blog articles are German (software architecture). Dev server: `php -S 0.0.0.0:8080`

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Language  | PHP 8.3 (vanilla, no framework) |
| Templating | PHP output buffering (`ob_start` / `ob_get_clean`) |
| Markdown | `vendor/Parsedown.php` (vendored) |
| Syntax highlighting | `vendor/highlight.js` (vendored) |
| CSS | Native CSS nesting, custom properties, no preprocessor |
| Deployment | rsync over SSH via GitHub Actions |

## Commands

| Command | Description |
|---------|-------------|
| `php -S 0.0.0.0:8080` | Start dev server |

## Template Pattern

Pages use PHP output buffering — this is non-obvious, so follow the existing pattern:

1. Set layout vars (`$pageTitle`, `$pageDescription`, `$pageUrl`, `$pageLang`, `$pageImage`, `$extraStyles`)
2. `ob_start()` → write HTML → `$pageContent = ob_get_clean()`
3. `include 'templates/layout.php'` injects `$pageContent` into `<body>`

## Frontmatter

The YAML frontmatter parser in `includes/articles.php` is a custom ~30-line regex — **not** a full YAML library. It only supports flat `key: value` and `- listitem`. Do not use nested YAML.

## Rules

1. **No package managers or build tools.** No Composer, no npm. Vendor new libraries manually if absolutely necessary.
2. **No framework refactoring.** The vanilla PHP approach is deliberate.
3. **Bilingual:** Homepage English, blog section German (UI strings and articles).
4. **CSS:** Use native CSS nesting (no preprocessor). Respect existing custom properties in `base.css` and breakpoint system.
5. **Security:** Always `htmlspecialchars()` on user-facing dynamic output.
6. **New articles** → `content/articles/*.md`, **new projects** → `content/projects.json`. Read existing files for the expected format.

## Boundaries

- ✅ **Always:** Verify before claiming — search the codebase before making assertions about existing code, structure, or behaviour. Never guess what a file contains or how something works — read the actual source.
- ✅ **Always:** Ask instead of assuming — when uncertain about requirements, design intent, or user expectations, ask structured questions to clarify. Only proceed with documented assumptions if the user explicitly declines to answer.
- ✅ **Always:** Web search for external knowledge — when working with external tools, libraries, or specs, consult authoritative sources (official docs, RFCs) instead of relying on training data.
- ⚠️ **Ask first:** Adding new vendor libraries — no package managers; any new dependency must be manually vendored and the user must approve.
- ⚠️ **Ask first:** Any change to `templates/layout.php` — it affects every page.
- 🚫 **Never:** Introduce a build step, package manager, or framework.
- 🚫 **Never:** Output dynamic content without `htmlspecialchars()`.

## Quality Principles

- **Quality over quantity, correctness over speed.** Fewer, correct changes beat many fast changes.
- **Human-reviewable changes.** Every change must be clean, readable, and maintainable enough for a senior developer to review, understand, and maintain long-term.
- **Self-review checklist** (run silently before presenting changes):
  1. Are the changes **correct** — do they actually solve the stated problem?
  2. Are the changes **clean** — no dead code, no debug artifacts, consistent style?
  3. Are the changes **readable** — would a human reviewer understand them without extra explanation?
  4. Are the changes **maintainable** — no over-engineering, no unnecessary abstractions?
  5. Are the changes **in scope** — nothing beyond what was requested or clearly necessary?
  6. Are the changes **complete** — both sides updated where needed?

## Git Workflow

- **Commit messages:** After completing a task, always propose a conventional commit message (`feat:`, `fix:`, `refactor:`, `docs:`, `chore:`) with a concise subject line and bullet-point body for multi-file changes. Do not commit — only output the message.
- **Reviewer summary:** After every completed task, post a short narrative paragraph explaining what was changed, why, and what the reviewer should pay attention to.
- **No `--force` push or `--no-verify`.**
