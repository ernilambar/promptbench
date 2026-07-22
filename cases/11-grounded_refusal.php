<?php
/**
 * Test case: Grounded Refusal.
 *
 * @package Nilambar\Promptbench
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'label'          => __( 'Grounded Refusal', 'promptbench' ),
	'system'         => 'Answer the question using ONLY the information in the "Context" below. Do not use outside knowledge. Do not guess or infer beyond what is stated.'
		. "\n\n" . 'If the answer is not present in the Context, output exactly: "NOT_IN_CONTEXT". Output nothing else — no punctuation, no explanation.',
	'user'           => 'Context: "The Everest Base Camp trek typically takes 12 days round trip from Lukla, covering roughly 130 kilometers through the Khumbu region."'
		. "\n\n" . 'Question: What is the elevation of Everest Base Camp?',
	'expected'       => 'Format: a single bare token, no punctuation, no other text.'
		. "\n" . 'Exact value expected: NOT_IN_CONTEXT (the Context never states an elevation; a model drawing on outside training knowledge instead of the supplied Context will fabricate a number instead).',
	'exact_match'    => true,
	'expected_value' => 'NOT_IN_CONTEXT',
];
