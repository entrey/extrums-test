<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/entrey
 * @since             1.0.0
 * @package           Extrums_Test
 *
 * @wordpress-plugin
 * Plugin Name:       Extrums Test
 * Plugin URI:        https://github.com/entrey
 * Description:       Plugin description.
 * Version:           1.0.0
 * Author:            Roman Peniaz
 * Author URI:        https://github.com/entrey/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       extrums-test
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'EXTRUMS_TEST_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-extrums-test-activator.php
 */
function activate_extrums_test() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-extrums-test-activator.php';
	Extrums_Test_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-extrums-test-deactivator.php
 */
function deactivate_extrums_test() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-extrums-test-deactivator.php';
	Extrums_Test_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_extrums_test' );
register_deactivation_hook( __FILE__, 'deactivate_extrums_test' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-extrums-test.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_extrums_test() {

	$plugin = new Extrums_Test();
	$plugin->run();

}
run_extrums_test();
