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
			$id           = preg_replace( '/^\d+-/', '', basename( $file, '.php' ) );
			$cases[ $id ] = require $file;
		}

		return $cases;
	}
}
