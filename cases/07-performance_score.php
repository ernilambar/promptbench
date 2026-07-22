<?php
/**
 * Test case: Performance Score.
 *
 * @package Nilambar\Promptbench
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'label'          => __( 'Performance Score', 'promptbench' ),
	'system'         => 'Rules:'
		. "\n" . '1. Scan the text below for "Execution Time" (in ms) and "Memory Limit" (in MB).'
		. "\n" . '2. Calculate the performance score: (Memory Limit divided by 2) minus Execution Time.'
		. "\n" . '3. If the final performance score is greater than 100, output exactly: "PERFORMANCE_PASS".'
		. "\n" . '4. If the score is 100 or less, output exactly: "PERFORMANCE_FAIL".'
		. "\n" . '5. Output nothing else. No math steps, no explanations.',
	'user'           => 'The localized routine spun up at 14:02:01. System diagnostics indicated a memory limit allocated at 512 MB for the wrapper execution. The job concluded successfully with a recorded execution time of 120 ms before terminating the sub-process.',
	'expected'       => 'Format: a single bare token, no punctuation, no math steps, no explanations.'
		. "\n" . 'Exact value expected: PERFORMANCE_PASS (score = 512/2 - 120 = 136, which is greater than 100).',
	'exact_match'    => true,
	'expected_value' => 'PERFORMANCE_PASS',
];
