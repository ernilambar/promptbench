<?php
/**
 * Plugin Name: Promptbench
 * Plugin URI: https://github.com/ernilambar/promptbench
 * Description: Test AI prompts from the WordPress admin.
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

use Nilambar\Gitvise\Updater;
use Nilambar\Promptbench\Core\Bootstrap;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PROMPTBENCH_VERSION', '1.0.0' );
define( 'PROMPTBENCH_BASE_NAME', basename( __DIR__ ) );
define( 'PROMPTBENCH_FILE', __FILE__ );
define( 'PROMPTBENCH_BASE_FILENAME', plugin_basename( __FILE__ ) );
define( 'PROMPTBENCH_DIR', plugin_dir_path( __FILE__ ) );
define( 'PROMPTBENCH_URL', plugin_dir_url( __FILE__ ) );

require_once PROMPTBENCH_DIR . 'vendor/autoload.php';
require_once PROMPTBENCH_DIR . 'vendor/ernilambar/gitvise/init.php';

( new Bootstrap() )->init();

$updater = new Updater( 'ernilambar/promptbench', __FILE__ );
$updater->init();
