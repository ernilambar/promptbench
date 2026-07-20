<?php
/**
 * Plugin Name: Promptbench
 * Description: Promptbench plugin.
 * Version: 1.0.0
 * Requires at least: 7.0
 * Requires PHP: 8.0
 * Author: Nilambar Sharma
 * Author URI: https://nilambar.net/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: promptbench
 * Domain Path: /languages
 *
 * @package Nilambar\Promptbench
 */

use Nilambar\Promptbench\Core\Bootstrap;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PROMPTBENCH_FILE', __FILE__ );
define( 'PROMPTBENCH_DIR', plugin_dir_path( __FILE__ ) );

require_once PROMPTBENCH_DIR . 'vendor/autoload.php';

( new Bootstrap() )->init();
