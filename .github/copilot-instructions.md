# nicograef.com — Copilot Instructions

# Personal portfolio and blog (vanilla PHP, no build tools).
# Full agent instructions: see `AGENTS.md` in the project root.

## Rules

1. No package managers or build tools (no Composer, no npm).
2. No framework refactoring — the vanilla PHP approach is deliberate.
3. Always `htmlspecialchars()` on user-facing dynamic output.
4. No nested YAML — the frontmatter parser only supports flat `key: value` and `- listitem`.

## Commands

Dev server: `php -S 0.0.0.0:8080`
