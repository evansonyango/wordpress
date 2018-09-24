<?php

add_filter( 'map_meta_cap', 'cfd_map_meta_cap', 10, 4 );

function cfd_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'cfd_delete_entries_message' => 'edit_users',
		'cfd_delete_entries_messages' => 'edit_users',
		'cfd_spam_entries_message' => 'edit_users',
		'cfd_unspam_entries_message' => 'edit_users',
		'cfd_response_entries_message' => 'edit_users');

	$meta_caps = apply_filters( 'cfd_map_meta_cap', $meta_caps );

	$caps = array_diff( $caps, array_keys( $meta_caps ) );

	if ( isset( $meta_caps[$cap] ) ) {
		$caps[] = $meta_caps[$cap];
	}

	return $caps;
}