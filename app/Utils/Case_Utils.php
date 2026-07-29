<?php
/**
 * Case_Utils class.
 *
 * @package Nilambar\Promptbench
 */

namespace Nilambar\Promptbench\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test case utilities.
 *
 * @since 1.0.0
 */
class Case_Utils {

	/**
	 * Gets test cases loaded from the "cases" directory.
	 *
	 * @since 1.0.0
	 *
	 * @return array Test cases keyed by case ID.
	 */
	public static function get_test_cases(): array {
		$cases = [];

		foreach ( glob( PROMPTBENCH_DIR . 'cases/*.php' ) as $file ) {
			$id           = basename( $file, '.php' );
			$cases[ $id ] = require $file;
		}

		/**
		 * Filters the test cases.
		 *
		 * Allows other plugins to add, remove, or modify test cases without
		 * modifying this plugin. Cases are keyed by a unique case ID.
		 *
		 * @since 1.0.0
		 *
		 * @param array $cases Test cases keyed by case ID.
		 */
		$cases = apply_filters( 'promptbench_test_cases', $cases );

		uasort(
			$cases,
			static function ( $a, $b ) {
				return $a['test_id'] <=> $b['test_id'];
			}
		);

		return $cases;
	}
}
