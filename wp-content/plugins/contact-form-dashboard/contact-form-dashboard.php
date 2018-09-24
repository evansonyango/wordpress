<?php

/*
Plugin Name: Contact Form Dashboard
Description: CFD stores, organizes and presents all the submissions of the Contact Form 7 in a simplest way. It supports other interesting features like - Dashboard Analytics, Bulk emails / replies handling; Search, sort and export messages.
Author: WTS
Author URI: http://wingstechsolutions.com/
Text Domain: cfd
Domain Path: /languages/
Version: 1.0.1
*/

define( 'CFD_VERSION', '1.0.1' );

define( 'CFD_PLUGIN_TEXT_DOMAIN', 'cfd' );

define( 'CFD_PLUGIN_BASENAME',	plugin_basename( __FILE__ ) );

define( 'CFD_PLUGIN_DIR',	untrailingslashit( dirname( __FILE__ ) ) );

$plugin_settings = get_option( "cfd_settings" );

if ($plugin_settings != '') {
	
	if (isset($plugin_settings['enable']))
		define( 'CFD_PLUGIN_ENABLE', TRUE );
	else
		define( 'CFD_PLUGIN_ENABLE', FALSE );

	define( 'CFD_PLUGIN_DEFAULT_CHANNEL', $plugin_settings['default_channel'] );

} else {
	
	update_option( 'cfd_settings', array( 'enable' => TRUE ) );
	define( 'CFD_PLUGIN_ENABLE', TRUE );
}

require_once CFD_PLUGIN_DIR . '/setting.php';

class CFD {

	public static function cfd_get_option( $key, $default = false ) {
		$opt = get_option( 'cfd' );

		if ( false === $opt ) {
			return $default;
		}

		if ( isset( $opt[$key] ) ) {
			return $opt[$key];
		} else {
			return $default;
		}
	}

	public static function cfd_update_option( $key, $value ) {
		$opt = get_option( 'cfd' );
		$opt = ( false === $opt ) ? array() : (array) $opt;
		$opt = array_merge( $opt, array( 
			$key => $value 
		) );
		update_option( 'cfd', $opt );
	}
}


/* Setup with default settings */

add_action( 'activate_' . CFD_PLUGIN_BASENAME, 'cfd_setup' );

function cfd_setup() {
	if ( $opt = get_option( 'cfd' ) ) {
		return;
	}

	CFD::cfd_update_option( 'bulk_validate',
		array(
			'timestamp' => current_time( 'timestamp' ),
			'version' => CFD_VERSION,
		)
	);
}

/* Init */

add_action( 'init', 'cfd_init' );

function cfd_init() {

	/* languages */
	load_plugin_textdomain( CFD_PLUGIN_TEXT_DOMAIN, false, CFD_PLUGIN_BASENAME . '/languages' );

	$entries = new CFD_Entries;
	$entries->cfd_register_post_type();

	do_action( 'cfd_init' );
}

/* register jquery and style on initialization */

add_action('init', 'cfd_register_script');

function cfd_register_script() {
	wp_register_script( 'custom_jquery', plugins_url('/assets/js/custom.js', __FILE__), array('jquery'), '2.5.1' );
    wp_register_style( 'new-style', plugins_url('/assets/css/style.css', __FILE__), false, '1.0.0', 'all');

    wp_register_script( 'bootstrap-jquery', plugins_url('/assets/bootstrap/js/bootstrap.js', __FILE__), array('jquery'), '2.5.1' );
    
   	wp_enqueue_script('bootstrap-jquery');
    wp_enqueue_script('custom_jquery');
   	wp_enqueue_style( 'new-style' );
}