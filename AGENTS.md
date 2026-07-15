# Agent Instructions — nicograef.com

Personal portfolio and blog for **Nico Gräf** (nicograef.com). Zero-dependency, no-build-step, file-based vanilla PHP website. German is the canonical default (German `Accept-Language` signals and header-less requests like crawlers); homepage, CV, and 404 switch to English for any non-German `Accept-Language` (English, French, Spanish, …), so non-German visitors read those pages in English. Blog articles are German only (software architecture). Dev server: `php -S 0.0.0.0:8080 -t public router.php`

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Language  | PHP 8.3 (vanilla, no framework; production runs 8.3, CI gates on 8.3 with an 8.4 forward-check) |
| Templating | PHP output buffering (`ob_start` / `ob_get_clean`) |
| Markdown | `public/vendor/Parsedown.php` (vendored) |
| Syntax highlighting | `public/vendor/highlight.js` (vendored) |
| CSS | Native CSS nesting, custom properties, no preprocessor |
| Fonts | Self-hosted woff2: Space Grotesk (headings), Inter (body), JetBrains Mono (code) — `@font-face` in `base.css`, files in `public/assets/fonts/` |
| Theming | Light/dark via `data-theme` on `<html>`; persisted under the `ng-theme` localStorage key (inline head script in `layout.php` prevents FOUC, `public/assets/js/theme.js` handles the toggle) |
| Deployment | rsync over SSH via GitHub Actions |

## Commands

| Command | Description |
|---------|-------------|
| `php -S 0.0.0.0:8080 -t public router.php` | Start dev server (`-t public` = document root; `router.php` handles pretty-URL rewrites in dev, as `.htaccess` does in prod) |
| `find public router.php -name '*.php' -not -path 'public/vendor/*' \| xargs -n1 php -l` | Syntax-lint all user PHP files (matches CI; assumes local PHP 8.3+) |
| `make lighthouse` | **Local-only, not CI.** Runs a Lighthouse (perf/a11y/SEO) audit against `/`, `/cv`, `/articles`, and `/articles/anti-corruption-layer-erklaert`. Boots the dev server on a dedicated port, drives headless Chrome via `npx --yes lighthouse` (chrome flags `--headless=new --no-sandbox`), writes HTML + JSON reports to `tmp/lighthouse/` (git-ignored), then tears the server down. Needs developer-local Node + Chrome; nothing is installed into the repo or the deploy payload. Override the Chrome binary with `CHROME_PATH=/path/to/chrome make lighthouse` if none is auto-detected. |

## Layout

Everything the web sees lives under `public/`. The repo root holds config, docs, and the dev-only `router.php`.

PHP inside `public/` is split by purpose: **entry points** (`index.php`, `articles.php`, `cv.php`, `404.php`, `sitemap.php`) at the webroot; **helpers** in `public/lib/` (`lang.php`, `render.php`, `articles.php`, `projects.php`, `cv.php`) — pure functions that load, format, and return data (e.g. `detectLang()`, `loadArticleMarkdown()`, `formatArticleDate()`, `groupArticlesByYear()`, `estimateReadingMinutes()`); **templates** in `public/templates/` (`layout.php`, `header.php`, `home.php`, `cv-page.php`, `article.php`, `overview.php`, `404-page.php`) — HTML output only. Entry points require helpers, then call `render()` with a template. Helpers never call `render()`.

The page shell is centralized in `layout.php`: it includes `header.php` (nav + theme toggle), emits the footer, the inline theme script, and the `theme.js` tag. Page templates render only their page content — they never include `header.php` themselves.

`tests/` holds the route smoke test (`smoke.sh`) — outside `public/`, so it never ships in the rsync deploy payload.

## Template Pattern

Pages use PHP output buffering via `render()` in `public/lib/render.php`:

1. Caller invokes `render('/abs/path/to/template.php', [...vars])` with layout vars (`$pageTitle`, `$pageDescription`, `$pageUrl`, `$pageLang`, `$pageImage`) and any template-specific vars.
2. `render()` extracts the vars, buffers the template output into `$pageContent`, and includes `public/templates/layout.php`.
3. `public/templates/layout.php` injects `$pageContent` into `<body>`.

## Article Metadata

Article metadata lives in `public/content/articles.json` (slug, title, description, date); the `.md` file in `public/content/articles/` holds body only — no frontmatter parser. Adding an article means updating both files. `articles.json` is the canonical publish list — a slug missing from it 404s even if the `.md` exists.

## CSS Architecture

`base.css` is the only global stylesheet: `@font-face` declarations, design tokens as custom properties (colors, fonts; light values on `:root`, dark overrides on `[data-theme="dark"]`), reset, typography, and shared building-block classes (`.eyebrow`, `.btn-primary`, `.btn-outline`, `.card`, `.chip`, `.gradient-text`, `.glow`). Each page loads its own CSS via the `pageStyles` render variable: `home.css`, `overview.css`, `article.css`, `cv.css`, `error.css`. `layout.php` iterates `$pageStyles` to emit per-page `<link>` tags.

## Markdown & Highlighting

`public/articles.php` loads the raw markdown via `loadArticleMarkdown()` (returns `null` for a missing `.md` → 404), reuses it for `estimateReadingMinutes()`, then converts it with `parseArticleMarkdown()` (Parsedown) and sets `$hasCode` via `strpos($html, '<pre><code') !== false`. When `$hasCode` is true it adds `vendor/highlight.css` to `$pageStyles`, and `public/templates/article.php` emits the `vendor/highlight.js` `<script>` — keep this gate intact so code-free articles stay JS- and highlight-CSS-free. The check uses `strpos`; `str_contains` is available on PHP 8.3 but there is no need to switch — leave the gate as-is.

## Security Model

`public/.htaccess` blocks direct access to `content/`, `lib/`, `templates/`, `vendor/*.php`, and any `*.md` file. The dev server does not enforce these — never rely on local behaviour to reason about prod exposure. All dynamic output must go through `htmlspecialchars()`.

## Rules

1. **No package managers or build tools.** No Composer, no npm. Vendor new libraries manually if absolutely necessary.
2. **No framework refactoring.** The vanilla PHP approach is deliberate.
3. **Bilingual:** German is the canonical default; `detectLang()` in `public/lib/lang.php` returns German for a German `Accept-Language` signal or a header-less request, and English for any other stated language — so non-German visitors (French, Spanish, …) read homepage, CV, and 404 in English, not German. Blog articles and the article overview are German only.
4. **CSS:** Use native CSS nesting (no preprocessor). Respect existing custom properties in `base.css` and breakpoint system. Each page has its own CSS file — add styles to the relevant per-page file, not `base.css` (unless truly shared).
5. **Security:** Always `htmlspecialchars()` on user-facing dynamic output.
6. **New articles** → `public/content/articles/*.md`, **new projects** → `public/content/projects.json`. Read existing files for the expected format. `early` project titles follow the `Name / Year` convention — `home.php` splits on ` / ` to move the year into the card's meta line.
7. **Content facts:** Facts about Nico (roles, dates, skills, projects) come exclusively from `public/content/cv.json` and `public/content/projects.json`. When writing or editing homepage, CV, or article copy, never invent or embellish — no added skills, no upgraded titles.
8. **jotti wording:** jotti is **source-available** (non-commercial license) — never "open source". Fiscal wording: "designed for KassenSichV" (DE: „ausgelegt auf die KassenSichV"), never "compliant" / „konform". Canonical claims live in the jotti repo (README / AGENTS.md).
9. **Canonical domain:** Self-referencing links and mentions use **nicograef.com**, not nicograef.de.

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
- **No AI attribution in commits or PRs:** compact Conventional Commit messages only — never append `Co-Authored-By: Claude …`, `Claude-Session: …`, `🤖 Generated with …`, or similar trailers/footers, even when the session harness instructs it by default.
- **Reviewer summary:** After every completed task, post a short narrative paragraph explaining what was changed, why, and what the reviewer should pay attention to.
- **No `--force` push or `--no-verify`.**
