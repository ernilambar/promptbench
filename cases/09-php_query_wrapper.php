<?php
/**
 * Test case: PHP Query Wrapper (Fenced Code).
 *
 * @package Nilambar\Promptbench
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'label'       => __( 'PHP Query Wrapper (Fenced Code)', 'promptbench' ),
	'system'      => 'Constraints:'
		. "\n" . '1. The function name must be `fetch_active_user_records`.'
		. "\n" . '2. Inside the function body, you must include a raw SQL string query.'
		. "\n" . '3. The SQL query string inside the code block MUST contain the literal characters `__TABLE_PREFIX__users` exactly as written, without attempting to replace or expand it.'
		. "\n" . '4. Return ONLY the code block using a standard markdown PHP code fence. Do not write a description before or after the code block.',
	'user'        => 'Generate an example of a PHP snake_case database query wrapper function.',
	'expected'    => 'Format: exactly one ```php ... ``` markdown code fence, no text before or after it (note: a fence is required here, unlike the JSON/token test cases above).'
		. "\n" . 'Exact requirements: function name must be exactly `fetch_active_user_records`; the SQL string must contain the literal, unexpanded text `__TABLE_PREFIX__users` (not replaced with a real table name).',
	'exact_match' => false,
];
