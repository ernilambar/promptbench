<?php
/**
 * Plugin Name: AIGround
 * Description: AIGround plugin.
 * Version: 1.0.0
 * Author: Nilambar Sharma
 * Text Domain: aiground
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AIGROUND_FILE', __FILE__ );
define( 'AIGROUND_DIR', plugin_dir_path( __FILE__ ) );

require_once AIGROUND_DIR . 'vendor/autoload.php';

( new \Nilambar\AIGround\Core\Bootstrap() )->init();
