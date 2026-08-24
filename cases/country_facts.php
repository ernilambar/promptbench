<?php
/**
 * Test case: Country.
 *
 * @package Nilambar\Promptbench
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'test_id'     => 10,
	'label'       => __( 'Country Facts', 'promptbench' ),
	'system'      => 'Respond only in plain text key-value lines. No markdown, no code fences, no commentary.',
	'user'        => 'Return the capital of Nepal and a list of exactly 3 major cities whose names start with the letter "B" using this exact format:' . "\n" . 'Capital: [city]' . "\n" . 'Cities: [city], [city], [city]',
	'expected'    => 'Format: plain text key-value lines, no markdown, no fences.'
		. "\n" . '"Capital" — exact value expected: "Kathmandu".'
		. "\n" . '"Cities" — open-ended: exactly 3 major cities, comma-separated, each name starting with the letter "B".',
	'exact_match' => false,
];
