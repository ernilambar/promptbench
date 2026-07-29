<?php
/**
 * Test case: Markdown Table.
 *
 * @package Nilambar\Promptbench
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'test_id'     => 150,
	'label'       => __( 'Markdown Table', 'promptbench' ),
	'system'      => 'Output a markdown table with exactly 2 columns, headed "Country" and "Capital", and exactly 3 data rows.'
		. "\n\n" . 'Include the header row and the separator row. Output nothing before or after the table — no intro, no commentary, no other markdown elements.',
	'user'        => 'List 3 countries in South Asia and their capitals in a markdown table.',
	'expected'    => 'Format: a single markdown table — header row, separator row, exactly 3 data rows, 2 columns ("Country", "Capital") — with nothing before or after it.'
		. "\n" . 'Row content is open-ended, except: each row must contain a real South Asian country and its correct capital.',
	'exact_match' => false,
];
