<?php
/**
 * Test case: Country (JSON).
 *
 * @package Nilambar\AIGround
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'label'       => __( 'Country (JSON)', 'aiground' ),
	'system'      => 'Respond only with valid JSON. No markdown, no code fences, no commentary.',
	'user'        => 'Return a JSON object with the capital of Nepal and a list of its other 5 major cities (excluding the capital) using this exact schema: {"capital": "", "cities": []}',
	'expected'    => 'Format: raw JSON object, no fences, schema {"capital": "", "cities": []}.'
		. "\n" . '"capital" — exact value expected: "Kathmandu".'
		. "\n" . '"cities" — open-ended: array of exactly 5 major cities, excluding the capital.',
	'exact_match' => false,
];
