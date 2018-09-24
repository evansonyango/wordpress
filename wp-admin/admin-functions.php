<?php
/**
 * Administration Functions
 *
 * This file is deprecated, use 'wp-admin/includes/admin.php' instead.
 *
 * @deprecated 2.5.0
 * @package WordPress
 * @subpackage Administration
 */

_deprecated_file( basename(__FILE__), '2.5.0', 'wp-admin/includes/admin.php' );

/** WordPress Administration API: Includes all Administration functions. */
require_once(ABSPATH . 'wp-admin/includes/admin.php');

add_filter( 'storm_social_icons_networks', 'storm_social_icons_networks');
function storm_social_icons_networks( $networks ) {
 
    $extra_icons = array (
        '/feed' => array(                  // Enable this icon for any URL containing this text
            'name' => 'RSS',               // Default menu item label
            'class' => 'rss',              // Custom class
            'icon' => 'icon-rss',          // FontAwesome class
            'icon-sign' => 'icon-rss-sign' // May not be available. Check FontAwesome.
        ),
    );
 
    $extra_icons = array_merge( $networks, $extra_icons );
    return $extra_icons;
 
}