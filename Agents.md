# Agents.md

Personal portfolio and blog for **Nico Gräf** (nicograef.com). Zero-dependency, no-build-step, file-based vanilla PHP website. Homepage is English (portfolio), blog articles are German (software architecture). Dev server: `php -S 0.0.0.0:8080`

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
