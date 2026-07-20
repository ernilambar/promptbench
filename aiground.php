<?php
/**
 * Plugin Name: AIGround
 * Description: AIGround plugin.
 * Version: 1.0.0
 * Requires at least: 7.0
 * Requires PHP: 8.0
 * Author: Nilambar Sharma
 * Author URI: https://nilambar.net/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: aiground
 * Domain Path: /languages
 *
 * @package Nilambar\AIGround
 */

use Nilambar\AIGround\Core\Bootstrap;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AIGROUND_FILE', __FILE__ );
define( 'AIGROUND_DIR', plugin_dir_path( __FILE__ ) );

require_once AIGROUND_DIR . 'vendor/autoload.php';

( new Bootstrap() )->init();
