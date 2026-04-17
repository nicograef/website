# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Personal portfolio and blog for **Nico Gräf** (nicograef.com / nicograef.de). Vanilla PHP 8.3, no framework, no build step, no package manager. Homepage is bilingual (EN/DE via `Accept-Language`); blog articles are German only. `Agents.md` holds the full conventions — read it before non-trivial work.

## Commands

- `php -S 0.0.0.0:8080 router.php` — local dev server. The `router.php` shim is required so pretty URLs (`/articles`, `/articles/<slug>`, `/sitemap.xml`) resolve; Apache uses `.htaccess` rewrites for the same routes in prod.
- Deployment is automatic on push to `main` via `.github/workflows/deploy.yml` (rsync over SSH). There is no test / lint / build step.

## Architecture

### Request flow

Every page is a tiny entry-point PHP file in the repo root that requires helpers from `src/` and calls `render()`:

- `index.php` → homepage (EN or DE based on `detectLang()`)
- `articles/index.php` → dispatches on `?slug=` (set by Apache rewrite or parsed from the path by `router.php`) to `renderArticle()` or `renderOverview()` in `src/articles.php`
- `sitemap.php` → dynamic XML from `articles.json`
- `404.php` → language-aware not-found page

`src/render.php::render($template, $vars)` is the single rendering primitive: it `extract()`s `$vars`, buffers the template via `ob_start`/`ob_get_clean` into `$pageContent`, then includes `src/layout.php`. All pages share this layout, which expects `$pageTitle`, `$pageDescription`, `$pageUrl`, `$pageLang`, `$pageImage`. **Changes to `src/layout.php` affect every page — ask first.**

### Content model

- `content/articles.json` is the **canonical publish list** (slug, title, description, date, author, tags, sorted newest-first). A slug missing here 404s even if the `.md` exists. No frontmatter parser — the `.md` files in `content/articles/` hold body only.
- `content/projects.json` drives the homepage project cards; `loadProjects($lang)` in `src/projects.php` picks the language variant.
- Adding an article = new `.md` in `content/articles/` **plus** a new entry at the top of `articles.json`. Both sides must match.

### Markdown & highlighting

`renderArticle()` runs Parsedown over the `.md`, then does `str_contains($html, '<pre><code')` to set `$hasCode`. `src/article.php` only emits the `<script>` tag for `vendor/highlight.js` when `$hasCode` is true — keep this gate intact so code-free articles stay JS-free.

### Security model

`.htaccess` blocks direct access to `content/`, `src/`, `vendor/*.php`, and any `*.md` file. The dev server does not enforce these — don't rely on local behavior to reason about prod exposure. All dynamic output must go through `htmlspecialchars()`.

## Conventions (highlights — full list in `Agents.md`)

- No Composer, no npm, no framework. New dependencies must be manually vendored into `vendor/` and require approval.
- CSS uses native nesting and custom properties from `assets/css/base.css`; no preprocessor.
- Bilingual split is fixed: homepage EN+DE, blog DE only.
- Commits follow Conventional Commits (`feat:`, `fix:`, `refactor:`, `docs:`, `chore:`). Never auto-commit — propose the message and let the user run it.
