<?php
/**
 * Test case: Ambiguous Item (JSON).
 *
 * @package Nilambar\AIGround
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'label'       => __( 'Ambiguous Item (JSON)', 'aiground' ),
	'system'      => 'You are an order processing assistant. You must parse ambiguous items. If the item name is ambiguous (e.g., matches multiple products), you must output a JSON error block with "error": "ambiguous_item" and a list of possible matches. Respond only with valid JSON. No markdown, no code fences, no commentary.',
	'user'        => 'Product Catalog:'
		. "\n" . '- Leather Jacket (Black)'
		. "\n" . '- Leather Jacket (Brown)'
		. "\n" . '- Denim Jacket (Blue)'
		. "\n\n" . 'Customer Order:'
		. "\n" . '"I want to purchase one leather jacket, size Medium. Bill it to my account."'
		. "\n\n" . 'Task: Process this order exactly according to the System Instructions.',
	'expected'    => 'Format: raw JSON error object, no fences, no commentary.'
		. "\n" . 'Exact value expected: "error": "ambiguous_item", with the possible-matches list containing exactly "Leather Jacket (Black)" and "Leather Jacket (Brown)" — and excluding "Denim Jacket (Blue)", which is a different product. Key names for the matches list may vary slightly by model.',
	'exact_match' => false,
];
