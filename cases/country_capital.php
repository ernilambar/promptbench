<?php
/**
 * Test case: Country Capitals.
 *
 * @package Nilambar\Promptbench
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'test_id'        => 25,
	'label'          => __( 'Country Capitals (JSON)', 'promptbench' ),
	'system'         => 'Respond only with valid JSON. No markdown, no code fences, no commentary.',
	'user'           => 'Return a JSON object mapping each country to its capital city using this exact schema: {"Bhutan": "", "Laos": "", "Malawi": "", "Mongolia": "", "Sri Lanka": ""}',
	'expected'       => 'Format: raw JSON object, no fences, schema {"Bhutan": "", "Laos": "", "Malawi": "", "Mongolia": "", "Sri Lanka": ""}.'
		. "\n" . 'Exact values expected: Bhutan -> Thimphu, Laos -> Vientiane, Malawi -> Lilongwe, Mongolia -> Ulaanbaatar, Sri Lanka -> Sri Jayawardenepura Kotte.',
	'exact_match'    => true,
	'expected_value' => '{"Bhutan":"Thimphu","Laos":"Vientiane","Malawi":"Lilongwe","Mongolia":"Ulaanbaatar","Sri Lanka":"Sri Jayawardenepura Kotte"}',
];
