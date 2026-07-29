# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Quality gate

**All gates MUST pass before any task is marked complete. No exceptions.**

- `composer format` — auto-fixes PHPCS violations (must run before lint)
- `composer lint` — must exit with zero errors; fix all errors and re-run until clean
- `pnpm format` — For auto-formatting
- `pnpm build` — must complete with zero errors

If a step fails: fix the issue, then re-run from that step.

## Other commands

- `composer lint-php` — parallel-lint syntax check only (subset of `lint`)
- `composer phpcs` — PHPCS only, no parallel-lint (subset of `lint`)
- `composer pot` — regenerate `languages/promptbench.pot` via WP-CLI i18n
- `composer po` — update `.po` files from the `.pot`
- `composer mo` — compile `.po` files to `.mo`

No automated test suite exists in this repo.

## Architecture

Promptbench is a WP-Admin tool (Tools > Promptbench) for manually testing prompts against
whichever AI provider/model is configured on the site, using WordPress core's AI Client
infrastructure (`wp_ai_client_prompt()`, `wp_get_connectors()`, `WordPress\AiClient\AiClient`).
These are **not** Composer dependencies — they come from WordPress core/another plugin at
runtime, so all call sites guard with `function_exists()` / `class_exists()` and degrade to
"No AI providers configured" rather than fatal.

Flow: `promptbench.php` → `Core\Bootstrap::init()` → `Admin\Admin_Page::init()`, which is the
only class wired into WordPress hooks (`admin_menu`, `wp_ajax_promptbench_prompt`).

- **`Admin\Admin_Page`** — renders the admin page and handles the `promptbench_prompt` AJAX
  action. `get_page_data()` assembles providers, test cases, and a nonce once per request
  (memoized in a static) and localizes them into `promptbenchData` for `src/main.js`.
  `handle_prompt()` reads the posted `exact_match` flag (set by the active test case),
  builds a prompt via `AI_Utils::build_prompt()`, runs it, and returns output + metadata +
  debug info (raw system/user prompt and raw response) as JSON.
- **`Utils\AI_Utils`** — all interaction with the AI Client registry: listing configured
  providers/models (`get_providers_with_models()`, merging connector-registered and
  directly-registered providers), constructing the prompt builder with the chosen
  system instruction/provider/model (`build_prompt()`), and normalizing a result object's
  provider/model/token-usage metadata (`extract_meta()`) via `method_exists()` checks since
  the result object's shape isn't controlled by this plugin. `build_prompt()` sets
  temperature `0.0` for exact-match test cases (deterministic single-token/JSON output) and
  `0.2` otherwise, so exact-match runs aren't flaky due to sampling noise.
- **`Utils\Case_Utils`** — loads test cases by `glob()`-ing `cases/*.php` and `require`-ing
  each file, keying by filename (no extension). Sorts the result by each case's `test_id`.
  Each case file returns an array with `test_id`, `label`, `system`, `user`, `expected`,
  `exact_match`.
- **`cases/*.php`** — the filename is just a slug (used as the case ID elsewhere); display
  order and the identifier referenced in reports both come from the `test_id` field inside
  the file, not the filename. Adding a new benchmark case is just adding a new file here with
  a `test_id` that doesn't collide (leave gaps of 10 to slot cases in later).
- **`src/main.js`** — built via `vite` (`pnpm build`) into `build/main.js`, which is what
  `Admin_Page` actually enqueues (not committed — `/build/` is gitignored). Populates the
  model `<select>` from `providerModels` when the provider changes, remembers the last-used
  provider/model in `localStorage`, swaps system/user/expected text and the `exact_match`
  flag when a test-case pill is clicked, and POSTs to `admin-ajax.php` on submit (including
  that `exact_match` flag). Renders a "Final Prompt (Debug)" panel showing the exact
  system/user prompt sent and the raw provider response JSON.

## Coding standard notes

PHPCS ruleset (`.phpcs.xml.dist`) extends `NilambarCodingStandard` + `WordPress-Extra` with
project overrides — notably: short array syntax is enforced (not WPCS default), nonce
verification sniffs are excluded (handled manually via `check_ajax_referer()`), and `use`
statements must be alphabetically sorted with no group/leading-backslash usage (Slevomat).
