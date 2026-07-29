# CLAUDE.md

Guidance for Claude Code when working in this repository.

## Quality gate

All must pass before a task is complete:

- `composer format` — auto-fix PHPCS violations (run before lint)
- `composer lint` — zero errors; fix and re-run until clean
- `pnpm format` — auto-format JS/CSS
- `pnpm build` — zero errors

On failure: fix, then re-run from that step.

## Other commands

- `composer lint-php` — syntax check only (subset of `lint`)
- `composer phpcs` — PHPCS only, no syntax check (subset of `lint`)
- `composer pot` / `po` / `mo` — regenerate/update/compile translations

No automated test suite exists.

## Architecture

WP-Admin tool (Tools > Promptbench) for manually testing prompts against whichever AI
provider/model is configured, via WordPress core's AI Client (`wp_ai_client_prompt()`,
`wp_get_connectors()`, `WordPress\AiClient\AiClient`). Not Composer deps — provided by
WP core/another plugin at runtime, so call sites guard with `function_exists()` /
`class_exists()` and degrade to "No AI providers configured" instead of fataling.

Flow: `promptbench.php` → `Core\Bootstrap::init()` → `Admin\Admin_Page::init()` (only
class hooked into WordPress: `admin_menu`, `wp_ajax_promptbench_prompt`).

- **`Admin\Admin_Page`** — renders the admin page; handles the `promptbench_prompt`
  AJAX action, building a prompt via `AI_Utils::build_prompt()` and returning
  output + metadata + debug info (raw prompt/response) as JSON.
- **`Utils\AI_Utils`** — all AI Client registry interaction: listing providers/models,
  building the prompt (temperature `0.0` for exact-match cases, `0.2` otherwise), and
  normalizing result metadata via `method_exists()` checks.
- **`Utils\Case_Utils`** — loads `cases/*.php` via `glob()`, sorted by each case's
  `test_id`. Each file returns `test_id`, `label`, `system`, `user`, `expected`,
  `exact_match`.
- **`cases/*.php`** — filename is just a slug; display order and the ID used in
  reports come from `test_id`, not the filename. New cases: use a `test_id` with gaps
  of 10 for later slotting.
- **`src/main.js`** — built via `pnpm build` into `build/main.js` (gitignored),
  enqueued by `Admin_Page`. Populates model `<select>` from `providerModels`,
  remembers last-used provider/model in `localStorage`, swaps test-case pill content,
  POSTs to `admin-ajax.php`, renders a "Final Prompt (Debug)" panel.

## Coding standard notes

PHPCS (`.phpcs.xml.dist`) extends `NilambarCodingStandard` + `WordPress-Extra`.
Overrides: short array syntax enforced, nonce sniffs excluded (handled manually via
`check_ajax_referer()`), `use` statements alphabetically sorted, no group/leading-backslash.
