<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.darkenergybiosphere.org
 * @since             1.0.0
 * @package           C_DEBI
 *
 * @wordpress-plugin
 * Plugin Name:       C-DEBI
 * Plugin URI:        https://www.darkenergybiosphere.org/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Matthew Janicak
 * Author URI:        https://www.darkenergybiosphere.org/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       c-debi
 * Domain Path:       /languages
 */

namespace C_DEBI;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Autoload Classes, via Composer.
 * @link https://getcomposer.org/doc/01-basic-usage.md#autoloading
 */
require_once(__DIR__ . '/vendor/autoload.php');

/**
 * Register Activation and Deactivation Hooks
 */
register_activation_hook( __FILE__, [ __NAMESPACE__ . '\Utilities\PluginActivator', 'activate' ] );

/**
 * The code that runs during plugin deactivation.
 */
register_deactivation_hook( __FILE__, [ __NAMESPACE__ . '\Utilities\PluginDeactivator', 'deactivate' ] );

$min_php = '7.3.0';

// Check the minimum required PHP version and run the plugin.
if ( version_compare( PHP_VERSION, $min_php, '>=' ) ) {
    ( new Init())->run_hook_loader();
}
