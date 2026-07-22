<?php
/**
 * Test case: Letter Counting.
 *
 * @package Nilambar\Promptbench
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'label'          => __( 'Letter Counting', 'promptbench' ),
	'system'         => 'Count the number of times the specified letter appears in the specified word. Output only the digit answer. No other text, no punctuation, no explanation.',
	'user'           => 'Count how many times the letter "r" appears in the word "strawberry".',
	'expected'       => 'Format: a single bare digit, no punctuation, no other text.'
		. "\n" . 'Exact value expected: 3 (s-t-r-a-w-b-e-r-r-y contains "r" at the 3rd, 8th, and 9th letters — a model relying on token-level shortcuts instead of counting characters often answers 2).',
	'exact_match'    => true,
	'expected_value' => '3',
];
