=== Promptbench ===

Contributors: nilambar
Tags: ai, prompt, ai client, connector, debug
Requires at least: 7.0
Tested up to: 7.0
Stable tag: 1.0.0
Requires PHP: 8.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Test AI prompts from the WordPress admin.

== Description ==

Promptbench is a WP-Admin tool for manually testing prompts against whichever AI provider and model is configured on your site, using WordPress core's AI Client.

= Features =

* Pick an AI provider and model from your site's configured connectors
* Edit system and user prompts and run them on demand
* View the response along with provider, model, and token-usage metadata
* Inspect the exact prompt sent and the raw provider response for debugging
* Load prebuilt test cases as a starting point

= Requirements =

* WordPress 7.0 or later
* PHP 8.0 or later
* At least one AI provider configured under Settings → AI → Connectors

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to Plugins → Add New Plugin
1. Search for "Promptbench"
1. Install and activate the plugin
1. Go to Tools → Promptbench

= Using FTP =

1. Extract 'promptbench.zip' to your computer
1. Upload the 'promptbench' directory to your '/wp-content/plugins/' directory
1. Activate the plugin on the WordPress Plugins dashboard
1. Go to Tools → Promptbench

== Frequently Asked Questions ==

= Do I need an API key? =

Promptbench does not use its own API key. It runs prompts through whichever AI provider is already configured under Settings → AI → Connectors. If none are configured, Promptbench shows "No AI providers configured."

= Where do I find it? =

Tools → Promptbench in wp-admin.

= Can other plugins add their own test cases? =

Yes, via the `promptbench_test_cases` filter.

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release.
