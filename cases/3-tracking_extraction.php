<?php
/**
 * Test case: Tracking Extraction (JSON).
 *
 * @package Nilambar\AIGround
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'label'       => __( 'Tracking Extraction (JSON)', 'aiground' ),
	'system'      => 'Extract the tracking number and delivery date from the email text provided below.'
		. "\n\n" . 'Your response MUST be formatted strictly as a JSON object containing exactly two keys: "tracking_number" and "delivery_date".'
		. "\n\n" . 'Strict Rules:'
		. "\n" . '1. Do NOT include any introductory or concluding text (e.g., do not say "Here is your JSON object:").'
		. "\n" . '2. Do NOT format it inside markdown code fences (```json ... ```).'
		. "\n" . '3. If the date is missing, set its value to null.'
		. "\n" . '4. Output ONLY the raw JSON string.',
	'user'        => 'Hey there, just wanted to update you that your package was shipped out. The carrier gave us tracking ref #983471029. Let us know if you have any questions!',
	'expected'    => 'Format: raw JSON object, no fences, no other text, exactly 2 keys: "tracking_number", "delivery_date".'
		. "\n" . 'Exact values expected: "tracking_number": "983471029", "delivery_date": null (no date mentioned in the email).',
	'exact_match' => true,
];
