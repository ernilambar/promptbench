<?php
/**
 * Test case: Country.
 *
 * @package Nilambar\AIGround
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'label'       => __( 'Country', 'aiground' ),
	'system'      => 'Be concise. No markdown, no code fences, no wrapping.',
	'user'        => 'Write two complete sentences, each with a subject and a verb. The first sentence states the capital of Nepal. The second sentence lists exactly 5 major cities (excluding the capital) separated by commas. Output only these two sentences.',
	'expected'    => 'Format: plain text, exactly 2 sentences, no markdown.'
		. "\n" . 'Sentence 1 — exact value expected: capital of Nepal is Kathmandu.'
		. "\n" . 'Sentence 2 — open-ended: exactly 5 major cities, comma-separated, excluding the capital.',
	'exact_match' => false,
];
