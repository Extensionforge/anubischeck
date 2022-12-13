<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://extensionforge.com/
 * @since             1.0.0
 * @package           Vnr_Anubischeck
 *
 * @wordpress-plugin
 * Plugin Name:       VNR Anubischeck
 * Plugin URI:        https://extensionforge.com
 * Description:       Check VIP status with Apicall
 * Version:           1.0.0
 * Author:            Steve Kraft & Peter Mertzlin
 * Author URI:        https://https://extensionforge.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       vnr-anubischeck
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'VNR_ANUBISCHECK_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-vnr-anubischeck-activator.php
 */
function activate_vnr_anubischeck() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vnr-anubischeck-activator.php';
	Vnr_Anubischeck_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-vnr-anubischeck-deactivator.php
 */
function deactivate_vnr_anubischeck() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vnr-anubischeck-deactivator.php';
	Vnr_Anubischeck_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_vnr_anubischeck' );
register_deactivation_hook( __FILE__, 'deactivate_vnr_anubischeck' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-vnr-anubischeck.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_vnr_anubischeck() {

	$plugin = new Vnr_Anubischeck();
	$plugin->run();

}
run_vnr_anubischeck();
