# Plan: Simplify Repository Structure

> Source PRD: `docs/prds/prd-simplify-repo-structure.md`

## Goal

Reduce the repository from 3 PHP subdirectories (`includes/`, `templates/`, `templates/articles/`) + 6 CSS files to 1 flat `src/` directory + 2 CSS files, without changing any public-facing behavior or URLs.

## Architectural decisions

- **`src/` is flat** — no subdirectories. Logic files (`articles.php`, `render.php`, `lang.php`, `projects.php`) live next to templates (`layout.php`, `home.php`, `article.php`, `overview.php`, `404-page.php`).
- **Entry points stay in root** — `index.php`, `404.php`, `sitemap.php`, `router.php` remain at project root.
- **`articles/index.php` stays** — retained for `/articles/*` URL routing via PHP's built-in server and Apache DirectoryIndex.
- **CSS reduces to 2 files** — `base.css` (global, absorbs `fonts.css`) and `pages.css` (all page-specific styles). The `$extraStyles` mechanism is removed entirely.
- **No new dependencies, no build tools.** This is a pure file-move + path-update refactoring.

## Inventory

PHP include/require statements to update:

- `index.php:2-5` — requires `includes/lang.php`, `includes/articles.php`, `templates/projects.php`, `includes/render.php`; renders `templates/home.php`
- `404.php:2` — requires `includes/render.php`; renders `templates/404.php`
- `sitemap.php:8` — requires `includes/articles.php`
- `articles/index.php:7` — requires `../includes/articles.php`
- `includes/articles.php:2-3` — requires `../vendor/Parsedown.php`, `render.php`; defines `TEMPLATES_DIR` pointing to `../templates`
- `includes/articles.php:47` — includes `../404.php` on invalid slug
- `includes/articles.php:57` — renders `TEMPLATES_DIR . '/articles/article.php'`
- `includes/articles.php:78` — renders `TEMPLATES_DIR . '/articles/overview.php'`
- `includes/render.php:20` — includes `../templates/layout.php`
- `includes/articles-latest.php:2` — requires `articles.php` (CWD-relative)
- `templates/home.php:17` — includes `../includes/articles-latest.php`
- `templates/projects.php:37` — reads `../content/projects.json`

CSS files (approximate line counts):

- `assets/css/fonts.css` — 29 lines (`@font-face` declarations)
- `assets/css/base.css:1` — imports `fonts.css` via `@import`; ~90 lines global styles
- `assets/css/main.css` — ~112 lines (homepage: project fade-in, article cards)
- `assets/css/article.css` — ~325 lines (article reading page, responsive breakout)
- `assets/css/overview.css` — ~155 lines (article list, cards, metadata)
- `assets/css/404.css` — 9 lines (error container flex layout)

`$extraStyles` usage:

- `index.php:19` — `['/assets/css/main.css']`
- `404.php:8` — `['/assets/css/404.css']`
- `includes/articles.php:60` — `['/assets/css/article.css', '/vendor/highlight.css']`
- `includes/articles.php:80` — `['/assets/css/overview.css']`
- `templates/layout.php:53-56` — loops `$extraStyles` to emit `<link>` tags

## Resolved decisions

- `templates/404.php` renamed to `404-page.php` in `src/` to avoid clash with root `404.php` controller.
- `articles-latest.php` inlined into `home.php` rather than kept as separate file (used exactly once).
- `$extraStyles` removed entirely (~600 lines total CSS — no benefit to per-page loading).
- `highlight.css` loaded unconditionally (small, only affects `<pre><code>` elements).
- No `config.php` introduced — that's a separate improvement.

## Open questions / Risks

- `.htaccess` may reference `includes/` or `templates/` for deny rules — verify after Phase 1.

---

## Phase 1: Create `src/` and move all PHP files

**User stories**: 1, 2, 6, 7, 8, 10, 12, 13

### Context

- `index.php:2-5` — 4 require/include statements to update
- `404.php:2` — 1 require + 1 template path
- `sitemap.php:8` — 1 require
- `articles/index.php:7` — 1 require with `../` prefix
- `includes/render.php:20` — layout include path
- `includes/articles.php:2-7` — `TEMPLATES_DIR` constant + Parsedown require
- `includes/articles.php:47` — 404 fallback include
- `includes/articles.php:57,78` — template render paths
- `templates/home.php:17` — articles-latest include
- `templates/projects.php:37` — JSON file path

### What to build

Create a `src/` directory and move all files from `includes/` and `templates/` into it as a flat structure:

| Source | Destination |
|--------|-------------|
| `includes/articles.php` | `src/articles.php` |
| `includes/articles-latest.php` | `src/articles-latest.php` (temporary — removed in Phase 2) |
| `includes/render.php` | `src/render.php` |
| `includes/lang.php` | `src/lang.php` |
| `templates/layout.php` | `src/layout.php` |
| `templates/home.php` | `src/home.php` |
| `templates/projects.php` | `src/projects.php` |
| `templates/404.php` | `src/404-page.php` |
| `templates/articles/article.php` | `src/article.php` |
| `templates/articles/overview.php` | `src/overview.php` |

Then update every `require`, `require_once`, `include`, `define()` path, and template path reference across all affected files:

**Root controllers** → change `includes/` and `templates/` to `src/`:
- `index.php`: 4 requires + template path
- `404.php`: 1 require + template path (now `src/404-page.php`)
- `sitemap.php`: 1 require
- `articles/index.php`: `../includes/` → `../src/`

**Moved files** → update internal `__DIR__`-relative paths:
- `src/articles.php`: remove `TEMPLATES_DIR` constant; template refs → `__DIR__ . '/article.php'` and `__DIR__ . '/overview.php'`; Parsedown path unchanged (same depth)
- `src/render.php`: layout → `__DIR__ . '/layout.php'` (same directory now)
- `src/home.php`: articles-latest → `__DIR__ . '/articles-latest.php'`
- `src/articles-latest.php`: change to `require_once __DIR__ . '/articles.php'`
- `src/projects.php`: JSON path unchanged (same depth)

Delete the empty `includes/`, `templates/`, and `templates/articles/` directories.

### Acceptance criteria

- [x] `src/` directory exists with exactly 10 files
- [x] `includes/` and `templates/` directories no longer exist
- [x] `php -S 0.0.0.0:8080 router.php` — homepage loads without errors
- [x] `/articles` overview page loads without errors
- [x] `/articles/was-ist-event-sourcing` article page loads without errors
- [x] `/nonexistent` returns 404 page without errors
- [x] `/sitemap.xml` returns valid XML
- [x] `router.php` is unchanged

---

## Phase 2: Inline `articles-latest.php` into `home.php`

**User stories**: 3

### Context

- `src/articles-latest.php` (moved in Phase 1) — 23 lines: calls `getLatestArticles(3)`, renders a `<div class="articles">` with article cards
- `src/home.php` — `include __DIR__ . '/articles-latest.php'`
- `getLatestArticles()` is already loaded by `index.php` (which requires `src/articles.php`) before `home.php` is rendered via `render()`

### What to build

Replace the `include` statement in `src/home.php` with the actual HTML/PHP content from `src/articles-latest.php`. The `require_once` for `articles.php` can be dropped because `index.php` already requires it before calling `render()`. The `$lang` variable is already available in the template scope via `extract($vars)` in `render()`.

Delete `src/articles-latest.php`.

### Acceptance criteria

- [x] `src/articles-latest.php` no longer exists
- [x] `src/` contains exactly 9 files
- [x] Homepage displays the 3 latest article cards correctly
- [x] Article card links point to correct URLs (`/articles/{slug}`)
- [x] Language-aware "lesen" / "read" label renders correctly

---

## Phase 3: Consolidate CSS into 2 files

**User stories**: 4, 5, 9, 11

### Context

- `assets/css/base.css:1` — `@import "fonts.css"` pulls in font-face declarations
- `assets/css/fonts.css` — 29 lines of `@font-face` for Montserrat variants
- `assets/css/main.css` — ~112 lines (homepage styles)
- `assets/css/article.css` — ~325 lines (article reading styles)
- `assets/css/overview.css` — ~155 lines (article list styles)
- `assets/css/404.css` — 9 lines (error page layout)
- `src/layout.php:53-56` — `$extraStyles` loop emitting per-page `<link>` tags

### What to build

**Step A — Merge `fonts.css` into `base.css`:**
Replace `@import "fonts.css"` at the top of `base.css` with the actual `@font-face` declarations from `fonts.css`. Delete `fonts.css`.

**Step B — Create `pages.css`:**
Create `assets/css/pages.css` by concatenating all page-specific CSS with section comment separators:
- Homepage (from `main.css`)
- Article (from `article.css`)
- Articles Overview (from `overview.css`)
- 404 (from `404.css`)

Delete `main.css`, `article.css`, `overview.css`, `404.css`.

**Step C — Update `layout.php`:**
Replace the `$extraStyles` loop with hardcoded `<link>` tags for `pages.css` and `highlight.css`.

**Step D — Remove `extraStyles` from all render calls:**
Strip the `'extraStyles'` key from every `render()` call in `index.php`, `404.php`, and both render calls in `src/articles.php`.

### Acceptance criteria

- [x] `assets/css/` contains exactly 2 files: `base.css` and `pages.css`
- [x] `base.css` starts with `@font-face` declarations (no `@import`)
- [x] `pages.css` contains all page-specific styles with section comments
- [x] `src/layout.php` emits exactly 3 stylesheet links: `base.css`, `pages.css`, `highlight.css`
- [x] No render call passes `extraStyles` anymore
- [x] Homepage: project fade-in animation and article cards styled correctly
- [x] Article page: serif typography, code highlighting, breakout elements at 120% width desktop / 100% mobile
- [x] Overview page: article cards with hover effects, metadata, tags
- [x] 404 page: centered error container
- [x] All Montserrat font variants load correctly
