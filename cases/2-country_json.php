<?php
/**
 * Test case: Country (JSON).
 *
 * @package Nilambar\Promptbench
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'label'       => __( 'Country (JSON)', 'promptbench' ),
	'system'      => 'Respond only with valid JSON. No markdown, no code fences, no commentary.',
	'user'        => 'Return a JSON object with the capital of Nepal and a list of exactly 3 major cities in Nepal whose names start with the letter "B" using this exact schema: {"capital": "", "cities": []}',
	'expected'    => 'Format: raw JSON object, no fences, schema {"capital": "", "cities": []}.'
		. "\n" . '"capital" — exact value expected: "Kathmandu".'
		. "\n" . '"cities" — open-ended: array of exactly 3 major cities, each name starting with the letter "B".',
	'exact_match' => false,
];
