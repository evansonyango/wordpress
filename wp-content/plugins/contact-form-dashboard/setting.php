<?php

require_once CFD_PLUGIN_DIR . '/includes/functions.php';
require_once CFD_PLUGIN_DIR . '/includes/class-entries.php';
require_once CFD_PLUGIN_DIR . '/includes/access.php';
require_once CFD_PLUGIN_DIR . '/backend/classes/class-entries-list-table.php';
require_once CFD_PLUGIN_DIR . '/backend/classes/class-setting.php';

if ( is_admin() ) {
	require_once CFD_PLUGIN_DIR . '/backend/backend.php';
}

if ( CFD_PLUGIN_ENABLE ) {
	add_action('wpcf7_before_send_mail', 'cfd_save_form' );
	add_action('wpcf7_mail_failed', 'cfd_mail_failed' );
}

function cfd_save_form( $wpcf7 ) {
	
	$entries = new CFD_Entries;
	$submission = WPCF7_Submission::get_instance();
	
	if ( $submission ) {
		
		$posted_data = $submission->get_posted_data();
		
		$defaults = array(
			'channel' => '',
			'subject' => '',
			'from' => '',
			'from_name' => '',
			'from_email' => '',
			'fields' => array(),
			'meta' => array(),
			'properties' => array(),
			'spam' => false );
		
		foreach ( $posted_data as $key => $value ) {
			if ( '_' == substr( $key, 0, 1 ) ) {
				unset( $posted_data[$key] );
			}
		}

		$channel_id = $entries->cfd_add_channel( $wpcf7->name, $wpcf7->title );

		if ( $channel_id ) {
			$channel = get_term( $channel_id, $entries->getGlob('channel_taxonomy') );

			if ( ! $channel || is_wp_error( $channel ) ) {
				$channel = 'contact-form-7';
			} else {
				update_term_meta( $channel->term_id, '_'.$channel->term_id, $posted_data );
				$channel = $channel->slug;
			}
		} else {
			$channel = 'contact-form-7';
		}
		
		$meta = array();

		$special_mail_tags = array( 'remote_ip', 'user_agent', 'url',
			'date', 'time', 'post_id', 'post_name', 'post_title', 'post_url',
			'post_author', 'post_author_email' );

		foreach ( $special_mail_tags as $smt ) {
			$meta[$smt] = apply_filters( 'wpcf7_special_mail_tags',
				'', '_' . $smt, false );
		}

		$sender = $wpcf7->get_properties()['mail']['sender'];
		$sender = strtr($sender, $posted_data);

		$subject = $wpcf7->get_properties()['mail']['subject'];
		$subject = strtr($subject, $posted_data);
		
		$properties = $wpcf7->get_properties();
		unset($properties['form']); unset($properties['messages']);

		$args = array(
			'channel' => $channel,
			'subject' => $subject,
			'from' => trim( $sender ),
			'fields' => $posted_data,
			'meta' => $meta,
			'properties' => $properties
		);
		
		$args = wp_parse_args( $args, $defaults );

		$entries->cfd_save($args);
	}

}

function cfd_mail_failed( $wpcf7 ) {
	
	$entries = new CFD_Entries;
	$parent = term_exists( $wpcf7->title, $entries->getGlob('channel_taxonomy') );
	if ($parent) {
		
		$args = array( 'posts_per_page' => 1, 'orderby' => 'ID', 'order' => 'DESC', 'post_status' => 'publish', 'channel_id' => $parent['term_id'] );

		$publish = $entries->cfd_find( $args );
		if ($publish) {
			$save_data = array(
			    'ID'           => $publish[0]->ID,
			    'post_status' => $entries->getGlob('spam_status')
			);
			wp_update_post($save_data , true );
		}
	}
}

function cfd_get_string_between($string, $start, $end){
    
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function cfd_set_screen_option($status, $option, $value) {
	if ( 'cfd_per_page' == $option ) return $value;
}
add_filter('set-screen-option', 'cfd_set_screen_option', 10, 3);