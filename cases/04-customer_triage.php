<?php
/**
 * Test case: Customer Triage.
 *
 * @package Nilambar\Promptbench
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'label'          => __( 'Customer Triage', 'promptbench' ),
	'system'         => 'Rules:'
		. "\n" . '1. Read the following customer request.'
		. "\n" . '2. If the customer is asking for a refund, output "REFUND_REQUEST".'
		. "\n" . '3. If they are asking about order status, output "STATUS_CHECK".'
		. "\n" . '4. CRITICAL: If the customer uses an angry tone, ignore rules 2 and 3, and output "ESCALATE_IMMEDIATELY".'
		. "\n" . '5. Provide no other text or reasoning.',
	'user'           => "Where is my package? It was supposed to arrive two days ago for my daughter's birthday, and now it's late. This is completely unacceptable and I want my money back right now.",
	'expected'       => 'Format: a single bare token, no punctuation, no other text.'
		. "\n" . 'Exact value expected: ESCALATE_IMMEDIATELY (angry tone overrides the refund/status rules per rule 4 — the request mentions both status and refund, so a model that misses the tone override will likely answer REFUND_REQUEST or STATUS_CHECK instead).',
	'exact_match'    => true,
	'expected_value' => 'ESCALATE_IMMEDIATELY',
];
