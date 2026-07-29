# Promptbench

Test AI prompts from the WordPress admin.

Requires an AI provider configured via the WordPress AI Client.

## Requirements

- PHP 8.0+
- WordPress 7.0+

## Installation

1. Install and activate the plugin.
2. Install and configure a compatible AI provider plugin.
3. Access the tool under **Tools > Promptbench**.

## Development

```bash
composer install
pnpm install
pnpm build
```

Run linting:

```bash
composer lint        # check
composer format      # auto-fix
```

## Contributing

1. Fork the repository and create a feature branch.
2. Run `composer install && pnpm install` to set up the environment.
3. Make your changes. Run `pnpm build` and `composer lint` — both must pass with zero errors.
4. Open a pull request against `main` with a clear description of what and why.

Bug reports are welcome via [GitHub Issues](https://github.com/ernilambar/promptbench/issues).

## Releasing

1. Update `readme.txt`'s `== Changelog ==` section.
2. Bump `version` in `package.json`, then run `pnpm run version`.
3. Commit the version bump, then tag and push (matching the bumped version, e.g. `v1.0.1`):

```bash
git tag v1.0.1
git push origin v1.0.1
```

Pushing the tag triggers `.github/workflows/release.yml`, which builds the plugin and attaches the zip to a GitHub release.

## License

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html)
