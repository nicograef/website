# PRD: Simplify Repository Structure

## Problem Statement

The repository has too many top-level folders and scattered files for what is a simple vanilla PHP website. Currently there are **5 top-level directories** containing PHP code (`includes/`, `templates/`, `templates/articles/`, `articles/`), **6 CSS files** spread across `assets/css/`, and mixed responsibilities (e.g. a `Project` class living in `templates/`). This makes the codebase harder to navigate than necessary for a ~20-file project.

**Current structure (PHP/logic files only):**

```
/                          ← 4 entry-point controllers
├── articles/
│   └── index.php          ← 1 article controller
├── includes/              ← 4 utility/logic files
│   ├── articles.php
│   ├── articles-latest.php
│   ├── lang.php
│   └── render.php
├── templates/             ← 5 templates (mixed with logic)
│   ├── layout.php
│   ├── home.php
│   ├── projects.php       ← contains Project class (logic, not a template)
│   ├── 404.php
│   └── articles/
│       ├── article.php
│       └── overview.php
├── assets/css/            ← 6 CSS files
│   ├── base.css
│   ├── fonts.css
│   ├── main.css
│   ├── article.css
│   ├── overview.css
│   └── 404.css
└── content/               ← data (good as-is)
```

This means:
- **3 separate folders** for PHP includes, templates, and template sub-pages
- `templates/projects.php` violates separation of concerns (logic in a template folder)
- `includes/articles-latest.php` is a 30-line template fragment used exactly once
- 6 CSS files for 4 page types with significant per-page loading via `$extraStyles`
- A developer must look in 4+ directories to understand the rendering pipeline

## Solution

Consolidate `includes/` and `templates/` into a single `src/` folder (flat, no subdirectories). Merge page-specific CSS into two files. Remove the `articles-latest.php` partial by inlining it into `home.php`. Move the `Project` class from `templates/projects.php` to `src/projects.php`.

**Target structure:**

```
/                          ← entry-point controllers (unchanged)
├── index.php
├── router.php
├── 404.php
├── sitemap.php
├── robots.txt
├── articles/
│   └── index.php          ← stays for URL routing
├── src/                   ← ALL PHP logic + templates (flat)
│   ├── layout.php         ← master layout
│   ├── home.php           ← homepage template (now includes latest-articles inline)
│   ├── article.php        ← single article template
│   ├── overview.php       ← articles list template
│   ├── 404-page.php       ← 404 template (renamed to avoid clash with root 404.php)
│   ├── articles.php       ← article system (parse, render, list)
│   ├── projects.php       ← Project class + loadProjects()
│   ├── render.php         ← template renderer
│   └── lang.php           ← language detection
├── content/               ← unchanged
│   ├── articles.json
│   ├── projects.json
│   └── articles/
│       └── *.md
├── assets/
│   ├── css/
│   │   ├── base.css       ← global styles (absorbs fonts.css)
│   │   └── pages.css      ← all page-specific styles (merges main.css, article.css, overview.css, 404.css)
│   ├── fonts/
│   └── img/
└── vendor/                ← unchanged
```

**Summary of changes:**

| Change | Files Affected | Net Effect |
|--------|---------------|------------|
| Merge `includes/` + `templates/` → `src/` | 9 files moved | −2 directories |
| Remove `templates/articles/` subfolder | 2 files moved up | −1 directory |
| Inline `articles-latest.php` into `home.php` | 2 files → 1 | −1 file |
| Merge `fonts.css` into `base.css` | 2 files → 1 | −1 file |
| Merge 4 page CSS → `pages.css` | 4 files → 1 | −3 files |
| Move `Project` class to `src/projects.php` | 1 file | cleaner separation |
| Update all `require`/`include` paths | ~10 files | path consistency |
| Update `router.php` for new paths | 1 file | routing alignment |

**Result:** From **3 PHP subdirectories + 6 CSS files** to **1 PHP subdirectory + 2 CSS files**. Net reduction of ~5 files, ~3 directories.

## User Stories

1. As a **developer**, I want all PHP logic and templates in one flat `src/` folder, so that I can find any file without navigating multiple directories.
2. As a **developer**, I want the `Project` class in `src/` instead of `templates/`, so that logic and presentation are clearly separated.
3. As a **developer**, I want `articles-latest.php` inlined into `home.php`, so that I don't have a separate file for a fragment used exactly once.
4. As a **developer**, I want CSS consolidated into `base.css` (global) and `pages.css` (page-specific), so that I only need to manage 2 stylesheet files.
5. As a **developer**, I want `fonts.css` merged into `base.css`, so that font declarations aren't in a separate file unnecessarily.
6. As a **developer**, I want all `require`/`include` paths to use `src/` consistently, so that the include pattern is predictable.
7. As a **developer**, I want `router.php` updated to reflect the new file locations, so that the dev server works correctly after the restructure.
8. As a **developer**, I want the `articles/index.php` controller to still work for `/articles/*` URL routing, so that article URLs remain unchanged.
9. As a **visitor**, I want all pages to render identically after the restructure, so that the refactoring is invisible to end users.
10. As a **developer**, I want the 404 template renamed to `404-page.php` in `src/`, so that it doesn't conflict with the root `404.php` controller.
11. As a **developer**, I want `$extraStyles` references updated so that `base.css` is always loaded and `pages.css` replaces per-page stylesheet loading.
12. As a **developer**, I want the `render()` function to resolve templates from `src/` instead of `templates/`, so that the template pattern still works.
13. As a **developer**, I want zero build steps or package managers introduced during this refactoring, so that the vanilla PHP philosophy is preserved.

## Implementation Decisions

### Module 1: `src/` directory (merge includes + templates)

- **What:** Move all files from `includes/` and `templates/` into a flat `src/` directory.
- **Renames:**
  - `templates/404.php` → `src/404-page.php` (avoid name clash with root `404.php`)
  - `templates/articles/article.php` → `src/article.php`
  - `templates/articles/overview.php` → `src/overview.php`
  - All other files keep their names, just move to `src/`.
- **Delete:** `includes/articles-latest.php` (content moves into `src/home.php`).
- **Delete:** Empty `includes/`, `templates/`, `templates/articles/` directories.

### Module 2: Inline articles-latest into home.php

- **What:** Move the 30-line article preview loop from `includes/articles-latest.php` directly into `src/home.php`.
- **Details:** The `include 'includes/articles-latest.php'` call in `home.php` will be replaced with the actual HTML/PHP code from that file. The `getLatestArticles()` call remains in `src/articles.php`.

### Module 3: Project class relocation

- **What:** `templates/projects.php` already contains the `Project` class and `loadProjects()` factory. It moves to `src/projects.php` unchanged.
- **Update:** `index.php` changes its `include` path from `templates/projects.php` to `src/projects.php`.

### Module 4: CSS consolidation

- **What:** Merge 6 CSS files into 2.
  - `base.css` absorbs `fonts.css` (prepend `@font-face` declarations).
  - `pages.css` combines `main.css` + `article.css` + `overview.css` + `404.css` with section comments.
- **Layout change:** `layout.php` always loads `base.css` + `pages.css`. Remove the `$extraStyles` mechanism entirely (all page CSS is in one file — the total CSS is small enough for a single load).
- **Delete:** `fonts.css`, `main.css`, `article.css`, `overview.css`, `404.css`.

### Module 5: Path updates across all files

- **What:** Update every `require`, `require_once`, `include` statement that references `includes/` or `templates/` to use `src/`.
- **Files to update:**
  - `index.php`: paths to `lang.php`, `articles.php`, `projects.php`, `render.php`, template reference
  - `404.php`: path to `render.php`, template reference
  - `sitemap.php`: path to `articles.php`
  - `articles/index.php`: path to `articles.php`
  - `src/articles.php`: path to `render.php`, `Parsedown.php`, template references for article/overview
  - `src/render.php`: path to `layout.php`
  - `src/home.php`: path to `articles.php` (for inline latest articles)
  - `src/layout.php`: CSS `<link>` tags (remove `$extraStyles` loop, hardcode `base.css` + `pages.css`)

### Module 6: Router alignment

- **What:** `router.php` currently routes to `articles/index.php` which stays. No router changes needed for the `src/` move because controllers (root PHP files) still sit in the root.
- **Verify:** `articles/index.php` must update its `require` path from `includes/articles.php` to `src/articles.php` (relative path: `../src/articles.php`).

### Architectural decisions

- **No `config.php` added** — out of scope for this simplification.
- **No build step introduced** — CSS merging is done manually, not via tooling.
- **All URLs remain identical** — this is a pure internal restructure.
- **`vendor/` untouched** — Parsedown and highlight.js stay vendored as-is.
- **`content/` untouched** — JSON and Markdown files stay where they are.
- **Documentation files stay in root** — README.md, Agents.md, UBIQUITOUS_LANGUAGE.md, DOCUMENTATION.md remain.

## Testing Decisions

No automated tests will be written. The website is a static content site with minimal logic. Verification will be done manually:

1. Start dev server (`php -S 0.0.0.0:8080 router.php`)
2. Verify homepage loads correctly (English + German)
3. Verify `/articles` overview page loads
4. Verify individual article pages load (e.g. `/articles/was-ist-event-sourcing`)
5. Verify 404 page renders for unknown routes
6. Verify `/sitemap.xml` generates valid XML
7. Verify CSS renders correctly (fonts, colors, layout, code highlighting)

## Out of Scope

- Adding a `config.php` for centralized constants (separate improvement).
- Changes to article content, projects data, or Markdown files.
- Adding new features or pages.
- Changing URL structure or routing behavior.
- Introducing any build tools, package managers, or frameworks.
- Moving `articles/index.php` into root (kept for clean `/articles/*` URL routing).
- Documentation file reorganization (stays in root per user preference).
- Changes to `.htaccess`, `.gitignore`, `.editorconfig`, or deployment workflow.

## Further Notes

- The `$extraStyles` mechanism in `layout.php` currently allows per-page CSS loading. After consolidation into `pages.css`, this mechanism can be removed entirely since the total CSS payload is small (estimated <10KB combined). This simplifies the `render()` function signature and every `render()` call site.
- The `articles/index.php` controller will need a relative path adjustment (`../src/articles.php` instead of `../includes/articles.php`). This is the only case where a `../` relative path is used.
- The rename of `templates/404.php` to `src/404-page.php` is necessary because `404.php` already exists in the root as the error controller. Alternative names considered: `error.php`, `not-found.php`. `404-page.php` was chosen for clarity.
