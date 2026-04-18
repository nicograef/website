# nicograef.com — Copilot Instructions

# Personal portfolio and blog (vanilla PHP, no build tools).
# Full agent instructions: see `AGENTS.md` in the project root.

## Rules

1. No package managers or build tools (no Composer, no npm).
2. No framework refactoring — the vanilla PHP approach is deliberate.
3. Always `htmlspecialchars()` on user-facing dynamic output.
4. Each page has its own CSS file — add styles to the relevant per-page file, not `base.css` (unless truly shared).

## Commands

Dev server: `php -S 0.0.0.0:8080 router.php`
