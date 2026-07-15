# Plan: Technical Improvements (PHP 8.3, stricter analysis, smoke tests, Lighthouse, Brotli)

> Source PRD: n/a (from task description + recommendation review)

## Goal

Modernize the site's tooling without betraying its zero-dependency, no-build-step design:

1. Move runtime + CI off end-of-life PHP 7.4 onto **PHP 8.3** (the version the webgo host runs).
2. Add `declare(strict_types=1)` and native types everywhere, then climb PHPStan from level 5 to **max (level 10)**.
3. Add a minimal **route smoke test** that catches silent breakage across all pretty-URL routes.
4. Add a **local, semi-automated Lighthouse** workflow (`make lighthouse` + npx) documented so agents can run it.
5. Add **Brotli** compression alongside the existing gzip in `.htaccess`.

Explicitly out of scope (evaluated and rejected as poor fit for a ~900-line, zero-dependency personal site): Composer, JS/CSS bundlers, asset minification, full Playwright E2E suites, and the SSH-deploy-key swap (deferred — it needs a new repo secret the owner sets manually).

## Architectural decisions

Durable decisions that apply across all phases:

- **Target PHP version**: **8.3** everywhere — `shivammathur/setup-php` in CI, `phpVersion: 80300` in `phpstan.neon`. Matches the webgo production host, so CI validates against what actually runs. A second CI matrix entry on **8.4** is a forward-compatibility check only; 8.3 is the gate that must pass.
- **No new committed dependencies.** Lighthouse runs via `npx` (Node + Chrome, developer-local, nothing added to the repo). Smoke tests use `bash` + `curl` + the existing `php -S` dev server. Both stay out of the deploy payload (`public/` only).
- **Routes under test** (dev via `router.php`, prod via `public/.htaccess`), the canonical list the smoke test asserts against:
  - `/` — homepage, bilingual (German default, English for non-German `Accept-Language`)
  - `/cv` — CV, bilingual
  - `/articles` — overview, German only
  - `/articles/{slug}` — article, German only; 404 for an unknown slug or a slug whose `.md` is missing
  - `/sitemap.xml` — XML sitemap
  - unknown path — 404
- **PHPStan target**: level `max` (currently level 10). The template `variable.undefined` ignore for `public/templates/*` stays (templates receive vars via `extract()` in `render()`). Residual `mixed`-access findings are resolved by typing the data at its source (array-shape PHPDoc on the JSON loaders) rather than by broad ignores; a committed `phpstan-baseline.neon` is the escape hatch of last resort for a small, documented remainder — not the primary tool.
- **Report/output locations**: Lighthouse reports write to `tmp/lighthouse/` (git-ignored). No generated artifact is ever committed or deployed.

## Inventory

Relevant existing files and symbols:

- `public/lib/lang.php — detectLang()` — reads `$_SERVER['HTTP_ACCEPT_LANGUAGE']`; returns `'de'`/`'en'`. Already typed.
- `public/lib/render.php — render()` — `extract($vars)` + output buffering + `layout.php`. Source of the template `variable.undefined` ignore.
- `public/lib/articles.php` — `getArticles()`, `getLatestArticles()`, `getArticle()`, `loadArticleMarkdown()`, `parseArticleMarkdown()`, `formatArticleDate()`, `groupArticlesByYear()`, `estimateReadingMinutes()`. `getArticles()` returns `json_decode(...)` — currently untyped `array` (the main `mixed` source for the article path).
- `public/lib/cv.php` — `loadCV()`, `resolveLocale()`, `formatCVDate()`, `cvLabels()`. `loadCV()`/`resolveLocale()` return decoded JSON — `mixed`-heavy.
- `public/lib/projects.php — loadProjects()` — decoded JSON with a `_de` locale-collapse pass; uses a `foreach (... as &$p)` reference loop.
- `public/index.php`, `public/cv.php`, `public/404.php`, `public/articles.php`, `public/sitemap.php` — entry points; `require_once` helpers then `render()`.
- `public/templates/*.php` — HTML-only templates consuming the decoded-JSON arrays (`$cv['basics']['name']`, `$article['slug']`, …) — the main consumers of `mixed` at level 9–10.
- `router.php` — dev-only router (blocks direct `.php`, serves static files, routes pretty URLs). Not deployed.
- `.github/workflows/deploy.yml` — `lint` job (`setup-php` @ 7.4 → `php -l` + PHPStan phar 2.1.17) then `deploy` job (rsync). **The lint job currently pins PHP 7.4.**
- `phpstan.neon` — `phpVersion: 70400`, `level: 5`, `paths: [public, router.php]`, `excludePaths: [public/vendor]`, `scanFiles: [public/vendor/Parsedown.php]`, template `variable.undefined` ignore.
- `Makefile` — `dev`, `lint`, `stan`, `check`, `help` targets.
- `public/.htaccess` — `mod_deflate` gzip block (`AddOutputFilterByType DEFLATE …`), cache-control headers, security rules.
- `AGENTS.md` — canonical agent-instruction file; "Commands" table is where a Lighthouse how-to belongs so agents discover it.
- `.gitignore` — already ignores `phpstan.phar`, `.env*`, logs.

## Resolved decisions

- Target PHP **8.3** (webgo host), not 8.4. 8.4 is a forward-check matrix entry only.
- Static-analysis depth: **max (level 10)** + `declare(strict_types=1)` + native types.
- Include **route smoke tests** and **Brotli**; **exclude** the SSH deploy-key swap from this plan.
- Lighthouse runs **locally via `npx lighthouse` behind a `make lighthouse` target**, documented in `AGENTS.md` — not wired into CI.

## Open questions / Risks

- **Level 10 cost concentrates in the JSON→template path.** `json_decode($json, true)` is `mixed`; every downstream array access (`$article['slug']`, `$cv['basics']['name']`, `loadProjects()` fields) trips level 9–10. The clean fix is array-shape PHPDoc on `getArticles()`, `loadCV()`/`resolveLocale()`, and `loadProjects()` describing the JSON structure, so precise types flow into the templates. If a handful of dynamic cases (e.g. `resolveLocale()`'s recursive locale collapse) resist precise typing, cap them with a **committed `phpstan-baseline.neon`** and a one-line comment — never a blanket `mixed` ignore. Phase 2 acceptance is "level max is green," with the baseline as an explicitly-documented, minimal remainder.
- **`resolveLocale()` recursion and `loadProjects()`'s `&$p` reference loop** are the least type-friendly spots; expect the most PHPDoc effort there.
- **Brotli depends on the host having `mod_brotli` enabled.** The `.htaccess` block is harmless if the module is absent (guarded by `<IfModule mod_brotli.c>`), but the actual win is unverifiable from the repo — confirm via response headers (`Content-Encoding: br`) against production after deploy.
- **Lighthouse needs Node + Chrome on the machine running it.** This is developer-local only; it adds nothing to the repo or the deploy. CI stays Lighthouse-free by decision.

---

## Phase 1: Move runtime and CI to PHP 8.3

### Context

- `.github/workflows/deploy.yml — lint job` — `setup-php` pinned to `"7.4"`; must become `8.3` (matrix: `8.3` gate + `8.4` forward-check).
- `phpstan.neon — phpVersion` — `70400` → `80300`.
- All `public/**/*.php` + `router.php` — must lint and run clean on 8.3.
- `Makefile — lint`, `stan` — unchanged in wording; they run against local PHP (documented assumption: local PHP is 8.x when contributing).

### What to build

Switch the analysis and CI baseline from dead 7.4 to supported 8.3. The `lint` job runs on a PHP matrix (`8.3`, `8.4`); the `8.3` leg is the required gate (matches prod). PHPStan analyses against `phpVersion: 80300`. Confirm the existing code is 8.3-clean (this codebase uses no known 7.4→8.x breakers, but verify: numeric-string handling, `strtotime`/`date` return handling, the `str_replace`/`preg_*` usages, and Parsedown under 8.3). No behavioral code change is expected; if a genuine incompatibility surfaces, fix it minimally in this phase.

### Acceptance criteria

- [ ] `deploy.yml` `lint` job runs a PHP matrix; `8.3` is present and its `php -l` + PHPStan steps pass.
- [ ] `deploy.yml` no longer references PHP `7.4` anywhere.
- [ ] `phpstan.neon` sets `phpVersion: 80300`.
- [ ] `php -l` passes for every file in `public/` (excluding `vendor`) and `router.php` on 8.3.
- [ ] PHPStan passes at the **current** level (5) under `phpVersion: 80300` (level increase is Phase 2).
- [ ] The dev server renders `/`, `/cv`, `/articles`, a known article, and `/sitemap.xml` without warnings/notices on local PHP 8.x.

---

## Phase 2: Strict types + native types + PHPStan level max

### Context

- `public/lib/*.php` — add `declare(strict_types=1)`; verify/complete native param + return types (most are already typed).
- `public/lib/articles.php — getArticles()`, `public/lib/cv.php — loadCV()`/`resolveLocale()`, `public/lib/projects.php — loadProjects()` — add array-shape PHPDoc describing the decoded JSON so precise types reach the templates.
- `public/*.php` (entry points), `router.php` — add `declare(strict_types=1)`.
- `public/templates/*.php` — consumers of the typed arrays; keep the existing `variable.undefined` ignore.
- `phpstan.neon — level` — `5` → `max`.
- (Conditional) `phpstan-baseline.neon` — only if a small, documented remainder can't be typed precisely; referenced from `phpstan.neon`.

### What to build

Make the codebase strictly typed end to end and raise the analysis bar to the maximum. Add `declare(strict_types=1)` as the first statement of every user PHP file. Fill in any missing native parameter/return types. Annotate the three JSON loaders with array-shape PHPDoc (`articles.json`, `cv.json`, `projects.json` structures) so the templates stop seeing `mixed`. Raise PHPStan to `max` and drive it to green — preferring precise types at the data source over ignores. If a minimal set of dynamic cases (notably `resolveLocale()` recursion) remains, capture them in a committed `phpstan-baseline.neon` with a comment explaining why, rather than weakening the level.

### Acceptance criteria

- [ ] Every user PHP file (`public/**` excluding `vendor`, plus `router.php`) starts with `declare(strict_types=1)`.
- [ ] `getArticles()`, `loadCV()`, `loadProjects()` carry array-shape PHPDoc matching their JSON.
- [ ] `phpstan.neon` sets `level: max`.
- [ ] `make check` (lint + PHPStan) passes at level max on PHP 8.3, both locally and in CI.
- [ ] Any residual findings live in a committed `phpstan-baseline.neon` that is **small and commented**; there is no broad `mixed`/`missingType` suppression in `phpstan.neon`.
- [ ] No runtime behavior change: all routes render identically to before (spot-check homepage, CV in both languages, a code article, a code-free article).

---

## Phase 3: Route smoke tests

### Context

- `router.php` — the dev server entry the smoke test boots (`php -S 0.0.0.0:8080 -t public router.php`).
- Routes list in **Architectural decisions** — the exact set the test asserts.
- `public/articles.php — $hasCode` gate — a code article must emit `vendor/highlight.css`/`highlight.js`; a code-free one must not. This is the highest-value silent-breakage check.
- `public/lib/lang.php — detectLang()` — language selection the test verifies via `Accept-Language`.
- `Makefile` — add a `smoke` target; fold into `check`.
- `.github/workflows/deploy.yml — lint job` — add a smoke-test step (rename job to reflect it covers lint + analysis + smoke, or add a sibling job that also gates `deploy`).

### What to build

A `tests/smoke.sh` script (bash + curl) that boots the PHP dev server on a fixed port, waits for readiness, exercises every route, asserts on status codes and a few content invariants, then tears the server down (trap-based cleanup, non-zero exit on any failure). Assertions:

- `/` → `200`; header-less request serves German (`<html lang="de"`); `Accept-Language: en` serves English (`<html lang="en"`).
- `/cv` → `200`; same German-default / English-on-`en` language switch.
- `/articles` → `200`; German.
- A **known code article** (slug from `articles.json` whose `.md` contains a fenced code block) → `200` and includes `vendor/highlight.` (CSS or JS).
- A **known code-free article** → `200` and does **not** include `vendor/highlight.`.
- `/articles/does-not-exist` → `404`.
- `/sitemap.xml` → `200` with `Content-Type: application/xml`.
- An unknown top-level path → `404`.

Wire it into `Makefile` (`smoke`, and add to `check`) and into CI so it gates `deploy`.

### Acceptance criteria

- [ ] `tests/smoke.sh` exists, is executable, boots and cleanly tears down the dev server, and exits non-zero if any assertion fails.
- [ ] All assertions above are implemented and pass locally on PHP 8.3.
- [ ] The article code-highlight assertions reference real slugs that exist in `public/content/articles.json` (one with code, one without), chosen so the test stays valid.
- [ ] `make smoke` runs the suite; `make check` includes it.
- [ ] CI runs the smoke test and blocks `deploy` on failure.

---

## Phase 4: Local Lighthouse workflow (semi-automated, documented for agents)

### Context

- `Makefile` — add a `lighthouse` target (self-documenting via the `##` help convention).
- `AGENTS.md — "Commands" table / a short "Lighthouse" note` — the surface agents read; documents how to run it, prerequisites, and where reports land. Single source of truth: detail lives here, the Makefile target references it.
- `.gitignore` — add `tmp/` so Lighthouse reports are never committed.
- `router.php` / dev server — the target boots the site the same way `make dev` does.

### What to build

A `make lighthouse` target that: boots the PHP dev server on a dedicated port, runs `npx --yes lighthouse` (headless Chrome) against the key routes (`/`, `/cv`, `/articles`, and one article URL), writes HTML + JSON reports into `tmp/lighthouse/`, then stops the server. It requires only developer-local Node + Chrome — nothing is added to the repo or the deploy payload. Document it in `AGENTS.md` under Commands: what it does, that it needs Node + Chrome and uses `npx` (no committed deps), which routes it audits, where reports are written, and that it is **local-only, not CI**. Keep the note concise and agent-actionable so a future agent can run a performance/a11y/SEO check on request.

### Acceptance criteria

- [ ] `make lighthouse` boots the dev server, audits the key routes via `npx lighthouse`, writes reports to `tmp/lighthouse/`, and tears the server down — even on failure (trap cleanup).
- [ ] `make help` lists the `lighthouse` target with a one-line description.
- [ ] `tmp/` is git-ignored; no report artifact is committed.
- [ ] `AGENTS.md` documents the workflow (purpose, prerequisites, routes, output location, local-only) clearly enough for an agent to run it unaided.
- [ ] The target adds nothing to `public/` and does not affect the rsync deploy payload.

---

## Phase 5: Brotli compression in .htaccess

### Context

- `public/.htaccess — mod_deflate block` — existing gzip via `AddOutputFilterByType DEFLATE …`; Brotli sits alongside it.

### What to build

Add a `<IfModule mod_brotli.c>` block to `public/.htaccess` enabling Brotli for the same text MIME types the gzip block already covers (HTML, CSS, JS, JSON, SVG, plain text, XML). The `<IfModule>` guard makes it a no-op if the host lacks `mod_brotli`, so it is safe to ship regardless. Leave the existing `mod_deflate` gzip block in place as the fallback for clients/hosts without Brotli.

### Acceptance criteria

- [ ] `public/.htaccess` contains a `<IfModule mod_brotli.c>` block covering the same text types as the existing `mod_deflate` block.
- [ ] The existing gzip (`mod_deflate`) block is unchanged and remains the fallback.
- [ ] The change is a no-op when `mod_brotli` is absent (guarded by `<IfModule>`), verified by reasoning about the guard.
- [ ] Post-deploy verification note recorded: confirm `Content-Encoding: br` on a CSS/JS response from production once shipped.

---

## Verification (all phases)

- `make check` green (lint + PHPStan max + smoke) on PHP 8.3, locally and in CI.
- CI `lint` matrix passes on 8.3 (gate) and 8.4 (forward-check); `deploy` still runs only on `push` to `main`.
- Manual: homepage + CV render correctly in German (header-less) and English (`Accept-Language: en`); a code article highlights, a code-free article stays JS-free.
- `make lighthouse` produces reports locally.
- Deploy payload unchanged in shape (`rsync ./public/` only) — no test/tooling files leak into production.
