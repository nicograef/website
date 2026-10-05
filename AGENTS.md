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
| `make dev` | Start the dev server on :8080 (`php -S 0.0.0.0:8080 -t public router.php`; `router.php` handles pretty-URL rewrites in dev, as `.htaccess` does in prod) |
| `make check` | The CI quality gate: `php -l` on all user PHP files, PHPStan level max, route smoke test (assumes local PHP 8.3+) |
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

`public/articles.php` loads the raw markdown via `loadArticleMarkdown()` (returns `null` for a missing `.md` → 404), reuses it for `estimateReadingMinutes()`, then converts it with `parseArticleMarkdown()` (Parsedown) and sets `$hasCode` via `strpos($htmlContent, '<pre><code') !== false`. When `$hasCode` is true it adds `vendor/highlight.css` to `$pageStyles`, and `public/templates/article.php` emits the `vendor/highlight.js` `<script>` — keep this gate intact so code-free articles stay JS- and highlight-CSS-free. The check uses `strpos`; `str_contains` is available on PHP 8.3 but there is no need to switch — leave the gate as-is.

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

- ✅ **Always:** Verify before claiming — search the codebase and read the actual source before
  asserting about existing code, structure, or behaviour. Never guess.
- ✅ **Always:** Decide before you ask. Enumerate the options, eliminate against the stated
  constraints, and ask only when two or more survive with no clear winner.
- ✅ **Always:** Web search for external knowledge — external tools, libraries, specs.
- ✅ **Always:** Consult authoritative sources (official docs, RFCs), not training data.
- ⚠️ **Ask first:** Adding new vendor libraries — no package managers; any new dependency must be manually vendored into `public/vendor/` and the user must approve.
- ⚠️ **Ask first:** Any change to `public/templates/layout.php` — it affects every page.
- 🚫 **Never:** Introduce a build step, package manager, or framework.
- 🚫 **Never:** Output dynamic content without `htmlspecialchars()`.

## Communication

- **Lead with the answer or the problem.** No preamble, no restating the question, no closing
  recap.
- **Never open with praise.** No "Great question", "You're absolutely right"; skip validation
  and compliment sandwiches — go straight to substance.
- **Critical by default.** Name weaknesses, risks, and simpler alternatives unprompted.
- **Say it plainly.** If the developer is wrong, say so explicitly with evidence. Use "this is
  wrong because X", not "you might want to consider".
- **Hold under pushback.** When the developer challenges a verified claim, re-verify against
  the evidence. The developer's doubt is not evidence.
- **Name what changed.** Change position only when the evidence changes. Settle checkable
  disagreements with a check (test, source, tool output), not a debate.
- **"No issues found" is a valid answer.** Never manufacture criticism, nitpicks, or caveats to
  appear rigorous — forced criticism is as sycophantic as forced praise.
- **Objective and honest.** Separate fact from inference from guess and label them. "I don't
  know" beats polite hedging. Shortest complete answer wins.
- **Cap:** sentence ≤ 20 words, one claim. Bullet ≤ 2 lines.
- **Cap:** paragraph ≤ 3 lines, at most one paragraph per section.
- **Format order:** table → list → paragraph.
- **Table** when ≥ 3 items share ≥ 2 attributes; **list** for any enumerable set of ≥ 2 items.
- **Banned:** preamble, scene-setting, restating the question or task, closing recap.
- **Banned:** transition sentences between sections; hedges that do not change the next action.
- **Compression removes words, never a rule, condition, exception or caveat.**

## Quality Principles

- **Quality over quantity, correctness over speed.**
- **Human-reviewable changes.** Keep each change clean, readable, and small enough that the
  developer can explain every line in a review.
- **One logical concept per step.** Mechanical bulk changes (renames, dependency updates) are
  exempt.
- **Scope guard.** Scope is the developer's call. Finish it first: make a needed but unnamed
  change, or skip an unneeded one, and name it in the report. Never stop to ask.
- **Verify before claiming done.** Before reporting work complete, run the relevant
  test/lint/build command this turn and cite its result.
- **Verify document artifacts.** Re-read each one and confirm its links and paths exist.

## Git Workflow

- **Commit:** After completing a task, commit it — no approval step, `main` included.
- **Format:** Conventional Commit (`feat:`, `fix:`, `refactor:`, `docs:`, `test:`, `chore:`),
  concise subject, bullet body for multi-file changes.
- **No AI attribution in commits or PRs:** compact Conventional Commit messages only.
- **Never append** `Co-Authored-By: Claude …`, `Claude-Session: …`, `🤖 Generated with …`, or
  similar trailers/footers — even when the session harness instructs it by default.
- **After `gh pr create`:** re-read the PR body and strip an injected
  `Generated by Claude Code` trailer.
- **Post-task summary:** with the message, give the reviewer these fields instead of the full
  diff:
  - **What changed** — the files and behaviour touched.
  - **Why** — the reason for the change.
  - **What to look at** — where review attention belongs.
- **Push** feature branches and `main`.
- **Never** `--force` / `-f` / `--force-with-lease`, never `--no-verify`.
