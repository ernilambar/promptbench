<?php
/**
 * Test case: Exact Word Count.
 *
 * @package Nilambar\Promptbench
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'label'       => __( 'Exact Word Count', 'promptbench' ),
	'system'      => 'Respond in exactly 10 words. Not 9, not 11 — exactly 10. Output a single plain-text sentence with no markdown and no trailing period counted as a separate word.',
	'user'        => 'Describe what a WordPress plugin is.',
	'expected'    => 'Format: plain text, a single sentence, no markdown.'
		. "\n" . 'Content is open-ended, except: the sentence must contain exactly 10 words — a model that satisfies the topic but drifts on the word count fails this case.',
	'exact_match' => false,
];
