<?php
/**
 * Test case: Nested JSON Extraction (Two-Level).
 *
 * @package Nilambar\Promptbench
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'test_id'        => 160,
	'label'          => __( 'Nested JSON Extraction', 'promptbench' ),
	'system'         => 'Extract the customer and order details from the message below.'
		. "\n\n" . 'Your response MUST be formatted strictly as a JSON object using this exact two-level schema:'
		. "\n" . '{"customer": {"name": "", "email": ""}, "order": {"id": "", "status": ""}}'
		. "\n\n" . 'Strict Rules:'
		. "\n" . '1. "customer" and "order" MUST be nested objects, not flattened top-level keys.'
		. "\n" . '2. Do NOT include any introductory or concluding text.'
		. "\n" . '3. Do NOT format it inside markdown code fences (```json ... ```).'
		. "\n" . '4. Output ONLY the raw JSON string.',
	'user'           => "Hi, this is Rita Thapa (rita.thapa@mailbox.com). I'm writing about order ORD-7734 — it says delayed and I need an update.",
	'expected'       => 'Format: raw JSON object, no fences, no other text, exactly 2 top-level keys ("customer", "order"), each a nested object with 2 keys ("name"/"email" and "id"/"status").'
		. "\n" . 'Exact values expected: customer.name: "Rita Thapa", customer.email: "rita.thapa@mailbox.com", order.id: "ORD-7734", order.status: "delayed"'
		. "\n" . '(a model that flattens the schema into a single-level object instead of nesting will fail this check).',
	'exact_match'    => true,
	'expected_value' => '{"customer":{"name":"Rita Thapa","email":"rita.thapa@mailbox.com"},"order":{"id":"ORD-7734","status":"delayed"}}',
];
