# AGENTS.md

## Project Overview

Promptbench is a WordPress plugin that lets users test AI prompts directly from the WordPress admin. It requires PHP 8.0+, WordPress 7.0+, and an AI provider configured via the WordPress AI Client. The PHP backend lives in `app/` (PSR-4, namespace `Nilambar\Promptbench`), and the JS/CSS admin UI is built with Vite from `src/`.

## Setup

```bash
composer install
pnpm install
```

## Commands

- `pnpm build` — Build JS/CSS assets
- `pnpm format` — Format JS/CSS/JSON with Prettier
- `composer lint` — Lint PHP (parallel-lint + PHPCS)
- `composer format` — Auto-fix PHP with PHP Code Beautifier

## Code Style

- **PHP**: Tab indentation. Follows WordPress Coding Standards + `NilambarCodingStandard` + Slevomat rules. Use short array syntax `[]`. All `use` statements must be imported (no fully-qualified names in code), sorted alphabetically, no group use, no leading backslash. Text domain is `promptbench` — wrap all translatable strings in `__()`, `_e()`, etc.
- **JS/CSS/JSON**: Formatted by Prettier with `@wordpress/prettier-config`, `printWidth: 100`.
- **Autoloading**: PSR-4, `Nilambar\Promptbench\` maps to `app/`.
- **PHP minimum**: 8.0. Use typed properties, match expressions, named arguments, constructor promotion where appropriate.

## Quality Gate

Before submitting changes, both must pass with zero errors:

```bash
composer lint
pnpm build
```
