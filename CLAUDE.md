# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Personal portfolio and blog for **Nico Gräf** (nicograef.com / nicograef.de). Vanilla PHP 8.3, no framework, no build step, no package manager. Homepage is bilingual (EN/DE via `Accept-Language`); blog articles are German only. `Agents.md` holds the full conventions — read it before non-trivial work.

## Commands

- `php -S 0.0.0.0:8080 -t public router.php` — local dev server. The `-t public` flag makes `public/` the document root for static files; `router.php` (at the repo root, dev-only) handles pretty-URL rewrites (`/articles`, `/articles/<slug>`, `/sitemap.xml`) the way Apache's `.htaccess` does in prod.
- Deployment is automatic on push to `main` via `.github/workflows/deploy.yml` — rsync ships the contents of `public/` (and only `public/`) to the server. There is no test / lint / build step.

## Architecture

### Repo layout

Everything deployable lives under `public/`. The repo root holds only config, docs, and the dev-only `router.php`.

```
public/                    webroot — everything the web sees
├── .htaccess
├── robots.txt
├── index.php cv.php sitemap.php 404.php     entry points
├── articles/index.php                        article controller
├── assets/{css,fonts,img}/                   static assets
├── content/                                  JSON + Markdown data (blocked by .htaccess)
├── lib/                                      pure-function PHP helpers
├── templates/                                HTML-output PHP templates (blocked by .htaccess)
└── vendor/                                   Parsedown.php, highlight.js, highlight.css
```

### Request flow

Every page is a tiny entry-point PHP file in `public/` that requires helpers from `public/lib/` and calls `render()` with a template from `public/templates/`:

- `public/index.php` → homepage (EN or DE based on `detectLang()`)
- `public/articles/index.php` → full controller: extracts slug from `?slug=` or URL path, calls `getArticle()` / `getArticles()` from `public/lib/articles.php`, then calls `render()` with the article or overview template
- `public/cv.php` → CV page (EN or DE)
- `public/sitemap.php` → dynamic XML from `articles.json`
- `public/404.php` → language-aware not-found page

`public/lib/render.php::render($template, $vars)` is the single rendering primitive: it `extract()`s `$vars`, buffers the template via `ob_start`/`ob_get_clean` into `$pageContent`, then includes `public/templates/layout.php`. All pages share this layout, which expects `$pageTitle`, `$pageDescription`, `$pageUrl`, `$pageLang`, `$pageImage`. **Changes to `public/templates/layout.php` affect every page — ask first.**

### Content model

- `public/content/articles.json` is the **canonical publish list** (slug, title, description, date, author, tags, sorted newest-first). A slug missing here 404s even if the `.md` exists. No frontmatter parser — the `.md` files in `public/content/articles/` hold body only.
- `public/content/projects.json` drives the homepage project cards; `loadProjects($lang)` in `public/lib/projects.php` resolves bilingual fields and returns plain associative arrays.
- Adding an article = new `.md` in `public/content/articles/` **plus** a new entry at the top of `articles.json`. Both sides must match.

### Markdown & highlighting

`public/articles/index.php` calls `parseArticleMarkdown()` (which runs Parsedown over the `.md`), then does `str_contains($html, '<pre><code')` to set `$hasCode`. `public/templates/article.php` only emits the `<script>` tag for `vendor/highlight.js` when `$hasCode` is true — keep this gate intact so code-free articles stay JS-free.

### Security model

`public/.htaccess` blocks direct access to `content/`, `lib/`, `templates/`, `vendor/*.php`, and any `*.md` file. The dev server does not enforce these — don't rely on local behavior to reason about prod exposure. All dynamic output must go through `htmlspecialchars()`.

## Conventions (highlights — full list in `Agents.md`)

- No Composer, no npm, no framework. New dependencies must be manually vendored into `public/vendor/` and require approval.
- CSS uses native nesting and custom properties from `public/assets/css/base.css`; no preprocessor.
- Bilingual split is fixed: homepage EN+DE, blog DE only.
- Commits follow Conventional Commits (`feat:`, `fix:`, `refactor:`, `docs:`, `chore:`). Never auto-commit — propose the message and let the user run it.
