<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Social Media Links
 * Plugin URI:        https://wordpress.org/plugins/social-media-links/
 * Description:       Adds links to all of your social media links profiles.
 * Version:           1.0.3
 * Author:            wpdesigncoding
 * Author URI:        http://designncoding.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       social-media
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_social_media() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-social-media-activator.php';
	Social_Media_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_social_media() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-social-media-deactivator.php';
	Social_Media_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_social_media' );
register_deactivation_hook( __FILE__, 'deactivate_social_media' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-social-media.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_social_media() {

	$plugin = new Social_Media();
	$plugin->run();

}
run_social_media();
