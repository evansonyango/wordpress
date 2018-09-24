<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

function cfd_delete_plugin() {
	
	global $wpdb;
	$taxonomy = 'cfd_entries_channel';
	$post_type = 'cfd_entries';

	delete_option( 'cfd' );
	delete_option( 'cfd_settings' );
	delete_option( 'cfd_entries_channel_children' );
	delete_option( 'cfd_search_settings' );
	delete_option( 'cfd_keyword_search_settings' );

	$query = 'SELECT t.name, t.term_id
            FROM ' . $wpdb->terms . ' AS t
            INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
            ON t.term_id = tt.term_id
            WHERE tt.taxonomy = "' . $taxonomy . '"';
	$terms = $wpdb->get_results($query);

    foreach ($terms as $term) {
    	
    	$term_metas = get_term_meta( $term->term_id );
    	foreach( $term_metas as $key => $value ) {
	       delete_term_meta( $term->term_id, $key);
	    }
        wp_delete_term( $term->term_id, $taxonomy );
    }

	$posts = get_posts(
		array(
			'numberposts' => -1,
			'post_type' => $post_type,
			'post_status' => 'any',
		)
	);

	foreach ( $posts as $post ) {

		$metas = get_post_meta( $post->ID );
	    foreach( $metas as $key => $value ) {
	       delete_post_meta( $post->ID, $key);
	    }
		wp_delete_post( $post->ID, true );
	}
}

cfd_delete_plugin();