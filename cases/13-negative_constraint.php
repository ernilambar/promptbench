<?php
/**
 * Test case: Negative Constraint.
 *
 * @package Nilambar\Promptbench
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'label'       => __( 'Negative Constraint', 'promptbench' ),
	'system'      => 'Write exactly two complete sentences describing what a content management system plugin is.'
		. "\n\n" . 'Under no circumstances may the words "plugin" or "WordPress" appear anywhere in your output, in any form or capitalization. Do not use markdown.',
	'user'        => 'Explain what a WordPress plugin is, without ever writing the words "plugin" or "WordPress".',
	'expected'    => 'Format: plain text, exactly 2 sentences, no markdown.'
		. "\n" . 'Content is open-ended, except: the words "plugin" and "WordPress" (in any capitalization) must NOT appear anywhere in the output — a model that leaks a forbidden word despite the explicit exclusion demonstrates weak negative-constraint following.',
	'exact_match' => false,
];
