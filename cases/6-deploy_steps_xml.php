<?php
/**
 * Test case: Deploy Steps (XML).
 *
 * @package Nilambar\AIGround
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'label'       => __( 'Deploy Steps (XML)', 'aiground' ),
	'system'      => 'Constraints:'
		. "\n" . '1. You must provide exactly 4 steps.'
		. "\n" . '2. Each step must be wrapped in a separate custom XML tag named `<Step>` with a property `id` equal to the step number (e.g., <Step id="1">...</Step>).'
		. "\n" . '3. The very last word of Step 4 MUST be the word "Done" and it must be capitalized.'
		. "\n" . '4. Do not include any other markdown headers or standard lists.',
	'user'        => 'Generate a sequence of steps to deploy a localized staging database.',
	'expected'    => 'Format: exactly 4 `<Step id="1">` ... `<Step id="4">` tags, no markdown headers or lists.'
		. "\n" . 'Step content is open-ended, except: the very last word inside `<Step id="4">` must be exactly "Done" (capitalized).',
	'exact_match' => false,
];
