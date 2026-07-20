<?php
/**
 * Bootstrap class.
 *
 * @package Nilambar\AIGround
 */

namespace Nilambar\AIGround\Core;

use Nilambar\AIGround\Admin\Admin_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin entrypoint.
 *
 * @since 1.0.0
 */
class Bootstrap {

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		( new Admin_Page() )->init();
	}
}
