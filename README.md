# Promptbench

A WordPress admin tool for manually testing prompts against configured AI providers/models, using WordPress core's AI Client infrastructure.

## Features

- Pick an AI provider and model from your site's configured connectors.
- Edit system and user prompts and run them on demand.
- View the response along with provider, model, and token-usage metadata.
- Inspect the exact prompt sent and the raw provider response for debugging.
- Load prebuilt test cases as a starting point.

## Requirements

- WordPress 7.0+
- PHP 8.0+

## Usage

Go to **Tools > Promptbench** in wp-admin, choose a provider/model, enter a system and user prompt (or select a test case), and submit.

## License

GPLv2 or later. See [https://www.gnu.org/licenses/old-licenses/gpl-2.0.html](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).
