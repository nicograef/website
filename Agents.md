# Agents.md

Personal portfolio and blog for **Nico Gräf** (nicograef.com). Zero-dependency, no-build-step, file-based vanilla PHP website. Homepage is English (portfolio), blog articles are German (software architecture). Dev server: `php -S 0.0.0.0:8080 -t public router.php`

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Language  | PHP 7.4 (vanilla, no framework; production and CI both run 7.4) |
| Templating | PHP output buffering (`ob_start` / `ob_get_clean`) |
| Markdown | `public/vendor/Parsedown.php` (vendored) |
| Syntax highlighting | `public/vendor/highlight.js` (vendored) |
| CSS | Native CSS nesting, custom properties, no preprocessor |
| Deployment | rsync over SSH via GitHub Actions |

## Commands

| Command | Description |
|---------|-------------|
| `php -S 0.0.0.0:8080 -t public router.php` | Start dev server |

## Layout

Everything the web sees lives under `public/`. The repo root holds config, docs, and the dev-only `router.php`.

PHP inside `public/` is split by purpose: **entry points** (`index.php`, `articles.php`, `cv.php`, `404.php`, `sitemap.php`) at the webroot; **helpers** in `public/lib/` (`lang.php`, `render.php`, `articles.php`, `projects.php`, `cv.php`) — pure functions that load and return data; **templates** in `public/templates/` (`layout.php`, `header.php`, `home.php`, `cv-page.php`, `article.php`, `overview.php`, `404-page.php`) — HTML output only. Entry points require helpers, then call `render()` with a template. Helpers never call `render()`.

## Template Pattern

Pages use PHP output buffering via `render()` in `public/lib/render.php`:

1. Caller invokes `render('/abs/path/to/template.php', [...vars])` with layout vars (`$pageTitle`, `$pageDescription`, `$pageUrl`, `$pageLang`, `$pageImage`) and any template-specific vars.
2. `render()` extracts the vars, buffers the template output into `$pageContent`, and includes `public/templates/layout.php`.
3. `public/templates/layout.php` injects `$pageContent` into `<body>`.

## Article Metadata

Article metadata lives in `public/content/articles.json` (slug, title, description, date, author, tags); the `.md` file in `public/content/articles/` holds body only — no frontmatter parser. Adding an article means updating both files. `articles.json` is the canonical publish list — a slug missing from it 404s even if the `.md` exists.

## CSS Architecture

`base.css` is the only global stylesheet (reset, typography, shared classes like `.tag`, `.profile-picture`). Each page loads its own CSS via the `pageStyles` render variable: `home.css`, `overview.css`, `article.css`, `cv.css`, `error.css`. `layout.php` iterates `$pageStyles` to emit per-page `<link>` tags.

## Rules

1. **No package managers or build tools.** No Composer, no npm. Vendor new libraries manually if absolutely necessary.
2. **No framework refactoring.** The vanilla PHP approach is deliberate.
3. **Bilingual:** Homepage English, blog section German (UI strings and articles).
4. **CSS:** Use native CSS nesting (no preprocessor). Respect existing custom properties in `base.css` and breakpoint system. Each page has its own CSS file — add styles to the relevant per-page file, not `base.css` (unless truly shared).
5. **Security:** Always `htmlspecialchars()` on user-facing dynamic output.
6. **New articles** → `public/content/articles/*.md`, **new projects** → `public/content/projects.json`. Read existing files for the expected format.

## Boundaries

- ✅ **Always:** Verify before claiming — search the codebase before making assertions about existing code, structure, or behaviour. Never guess what a file contains or how something works — read the actual source.
- ✅ **Always:** Ask instead of assuming — when uncertain about requirements, design intent, or user expectations, ask structured questions to clarify. Only proceed with documented assumptions if the user explicitly declines to answer.
- ✅ **Always:** Web search for external knowledge — when working with external tools, libraries, or specs, consult authoritative sources (official docs, RFCs) instead of relying on training data.
- ⚠️ **Ask first:** Adding new vendor libraries — no package managers; any new dependency must be manually vendored into `public/vendor/` and the user must approve.
- ⚠️ **Ask first:** Any change to `public/templates/layout.php` — it affects every page.
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
