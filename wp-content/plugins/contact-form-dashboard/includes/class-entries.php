<?php

$GLOB = array(
    'post_type' => 'cfd_entries',
    'spam_status' => 'cfd_spam_status',
    'channel_taxonomy' => 'cfd_entries_channel',
    'found_items' => 0,
    'channel_id' => CFD_PLUGIN_DEFAULT_CHANNEL
);

class CFD_Entries {

	public $glob;

    public function __construct() {
    	global $GLOB;
        $this->glob =& $GLOB;
    }

    public function getGlob($key = '') {
        return $this->glob[$key];
    }

    public function setGlob($key, $value) {
    	if ($key != '' && $value != '') {
    		$this->glob[$key] = $value;
    	}
    }

    public function cfd_find( $args = '' ) {

    	$plugin_settings = get_option( "cfd_settings" );

		$defaults = array(
			'posts_per_page' => 10,
			'offset' => 0,
			'orderby' => 'ID',
			'order' => 'ASC',
			'meta_key' => '',
			'meta_value' => '',
			'post_status' => 'any',
			'tax_query' => array(),
			'meta_query' => array(),
			'channel' => '',
			'channel_id' => 0,
			'response_status' => '');

		$args = wp_parse_args( $args, $defaults );
		
		$args['post_type'] = $this->glob['post_type'];

		if ( isset( $plugin_settings['cfd_disable'] ) && empty( $args['channel_id'] )) {
			$args['tax_query'][] = array(
				'taxonomy' => $this->glob['channel_taxonomy'],
				'terms' => $plugin_settings['cfd_disable'],
				'field' => 'term_id',
				'operator' => 'NOT IN' );	
		}

		if ( ! empty( $args['channel_id'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => $this->glob['channel_taxonomy'],
				'terms' => absint( $args['channel_id'] ),
				'field' => 'term_id' );
		}

		/*if ( ! empty( $args['channel'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => $this->glob['channel_taxonomy'],
				'terms' => $args['channel'],
				'field' => 'slug' );
		}*/

		if (isset($args['s']) && $args['s'] != '') {
			$plugin_search_settings = get_option( "cfd_search_settings" );
			$term = $plugin_search_settings['cfd_'.absint( $args['channel_id'] )];

			if (count($term) > 0 && $term != '') {
				$args['meta_query'] = array ( 'relation' => 'OR' );
		        foreach ($term as $key => $value) {
		        	$meta_key_arr = array(
		                'key' => '_field_'.$value,
		                'value' => $args['s'],
		                'compare' => 'LIKE'
		        	);
					array_push($args['meta_query'], $meta_key_arr);
				}
				unset($args['s']);
			}
		}
		
		if ( isset($args['response_status']) && $args['response_status'] != '' ) {
			
			$response_status_arr = array(
                'key' => '_is_reviewed',
                'value' => (( $args['response_status'] ) ? 1 : ''),
                'compare' => (( $args['response_status'] ) ? '=' : 'NOT EXISTS')
        	);

			if (isset($args['meta_query']) && !empty( $args['meta_query'] )) {
				$temp_of_meta_query = $args['meta_query'];
				$args['meta_query'] = array ( 'relation' => 'AND' );
				array_push($args['meta_query'], $response_status_arr);
				array_push($args['meta_query'], $temp_of_meta_query);
			} else {
				$args['meta_query'] = array ( $response_status_arr );
			}
		}
		
		$q = new WP_Query();
		$posts = $q->query( $args );

		$this->setGlob('found_items', $q->found_posts);

		return $posts;
	}

	public function cfd_get_resource_diff( $post = '' ) {
		$diff = '';
		$_meta_arr = get_post_meta($post, "_meta", true);
		$_response_date_1 = get_post_meta($post, "_response_date_1", true);

		$FjY = date("F j, Y", strtotime($_meta_arr['date']));
		$date3 = strtotime($FjY.' '.$_meta_arr['time']);
		$date4 = strtotime($_response_date_1);
		
		$diff = abs($date4 - $date3); // sec
		return $diff;
	}

	public function cfd_get_time_formate( $sec = '', $after_point = 2) {
		
		if ( $sec >= 60 ) {
			$min = $sec/60;
			if ( $min >= 60) {
				$h = $min/60;
				if ( $h >= 24) {
					$d = $h/24;
					if ( $d >= 7) {
						$w = $d/7;
						if ($w >= 4) {
							$m = $w/4.34524;
							if ($m >= 12) {
								$y = $m/12;
								$final_formate = round($y, $after_point) . ' yr.';
							} else {
								$final_formate = round($m, $after_point) . ' mo.';
							}
						} else {
							$final_formate = round($w, $after_point) . ' wk.';
						}
					} else {
						$final_formate = round($d, $after_point) . ' day';
					}
				} else {
					$final_formate = round($h, $after_point) . ' hr.';
				}
			} else {
				$final_formate = round($min, $after_point) . ' min.';
			}
		} else {
			$final_formate = round($sec, $after_point) . ' sec.';
		}

		return $final_formate;
	}

	public function cfd_get_ava_response_time( $term_id = '' ) {
		
		$args = array(
			'post_type'  => $this->getGlob('post_type'),
			'post_status' => array( 'publish', 'trash', $this->getGlob('spam_status') ),//'any',
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => '_is_reviewed',
					'value'   => 1,
					'type'    => 'numeric',
					'compare' => '=',
				)
			)
		);

		if ( $term_id != '' ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => $this->getGlob('channel_taxonomy'),
					'field'    => 'term_id',
					'terms'    => array( $term_id ),
					'operator' => 'IN',
				),
			);
		}
		
		$query = new WP_Query( $args );
		$total_time = 0;
		$count = count($query->posts);
		
		$ava_response_time = '-';
		if ( $count > 0 ) {
			foreach ($query->posts as $post) {
				$diff = $this->cfd_get_resource_diff( $post->ID );
				$total_time = $total_time + $diff;
			}
			$ava_response_time = ($total_time/$count);
		}
		
		return $this->cfd_get_time_formate($ava_response_time, 2);
	}

	public function cfd_get_keywords( $term_id = '', $rt_string = false) {
		
		if ( $term_id != '' ) {
			
			$plugin_keyword_search_settings = get_option( "cfd_keyword_search_settings" );
			
			if ( isset( $plugin_keyword_search_settings['cfd_'.$term_id] ) ) {
				$args = array(
					'post_type'  => $this->getGlob('post_type'),
					'post_status' => array( 'publish', 'trash', $this->getGlob('spam_status') ),
					'posts_per_page' => -1,
					'tax_query' => array(
					    array(
					    'taxonomy' => $this->getGlob('channel_taxonomy'),
					    'field' => 'id',
					    'terms' => $term_id
					    )
					)
				);
				$query = new WP_Query( $args );
				$count = count($query->posts);

				$allbodystring = '';
				if ( $count > 0 ) {
					foreach ($query->posts as $post) {
						foreach ($plugin_keyword_search_settings['cfd_'.$term_id] as $field) {
							$body = get_post_meta( $post->ID, '_field_' . $field, true);
							$allbodystring .= $body . ' ';
						}
					}
				}
				$allbodystring = $this->cfd_clean($allbodystring);
				$exclude_str = $plugin_keyword_search_settings['cfd_exclude_'.$term_id];
				$keywords = $this->cfd_stringCount($allbodystring, $exclude_str);
				arsort($keywords);
				if ( $rt_string ) {
					$string_rt = '';
					$i = 0;
					foreach ($keywords as $k => $v) {
						if ($i < 10) {
							$string_rt .= "$k (<span class='color-green'>$v</span>), ";
						}
						$i++;
					}
					return rtrim($string_rt, ', ');
				} else {
					return $keywords;
				}
				
			}
		}
	}

	public function cfd_stringCount($string, $exclude_str) {
		
		$string = $this->cfd_clean($string);
		$extra_arr = explode(', ', strtolower(stripslashes($exclude_str)));
		
		$peices_b = array(); 
		$totals = array(); 
		// explode on a space we are going to try and start filtering
		$peices_b = explode(' ',$string);
		
		// now we do a little error checking on the new array 
		if (is_array($peices_b)) {
			$totals = array_count_values(array_map('strtolower', $peices_b));
		}

		// else we put together a string to show word count
		foreach ($totals as $k => $v) {
			if ( in_array(strtolower($k), $extra_arr) || $k == '') {
				unset($totals[$k]);
			}
		}

		return $totals;
		
	}

	public function cfd_clean($string) {
	   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
	   $string = preg_replace('/[^A-Za-z0-9\-]/', '-', $string); // Removes special chars.
	   $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
	   $string = str_replace('-', ' ', $string); // Replaces all hyphens with spaces.
	   return $string;
	}

    public function cfd_register_post_type() {

		register_post_type( $this->glob['post_type'] , array(
			'labels' => array(
				'name' => __( 'CFD Entries', CFD_PLUGIN_TEXT_DOMAIN ),
				'singular_name' => __( 'CFD Entries', CFD_PLUGIN_TEXT_DOMAIN ) ),
			'rewrite' => false,
			'query_var' => false ) );

		register_post_status( $this->glob['spam_status'] , array(
			'label' => __( 'Spam', CFD_PLUGIN_TEXT_DOMAIN ),
			'public' => false,
			'exclude_from_search' => true,
			'show_in_admin_all_list' => false,
			'show_in_admin_status_list' => true ) );

		register_taxonomy( 
			$this->glob['channel_taxonomy'], 
			array($this->glob['post_type']), 
			array(
				'labels' => array(
					'name' => __( 'CFD Entries Channels', CFD_PLUGIN_TEXT_DOMAIN ),
					'singular_name' => __( 'CFD Entries Channel', CFD_PLUGIN_TEXT_DOMAIN ) 
				),
				'public' => false,
				'hierarchical' => true,
				'rewrite' => true,
				'query_var' => false 
			) 
		);
	}

	public function cfd_add_channel( $slug, $name = '' ) {
		$parent = term_exists( 'contact-form-7', $this->glob['channel_taxonomy'] );
		$is_first = false;
		if ( ! $parent ) {

			$parent = wp_insert_term( __( 'Contact Form 7', 'contact-form-7' ),
				$this->glob['channel_taxonomy'],
				array( 'slug' => 'contact-form-7' ) );
			
			if ( is_wp_error( $parent ) ) {
				return false;
			}
			$is_first = true;
		}

		$parent = (int) $parent['term_id'];

		if ( ! is_taxonomy_hierarchical( $this->glob['channel_taxonomy'] ) ) {
			return $parent;
		}

		if ( empty( $name ) ) {
			$name = $slug;
		}

		$channel = term_exists( $name,
			$this->glob['channel_taxonomy'],
			$parent );

		if ( ! $channel ) {
			$channel = wp_insert_term( $name,
				$this->glob['channel_taxonomy'],
				array( 'slug' => $slug, 'parent' => $parent ) );

			if ( is_wp_error( $channel ) ) {
				return false;
			}

			if ( $is_first ) {
				update_option( "cfd_settings", array(
					'enable' => true,
					'default_channel' => $channel['term_id']
				) );
			}
		}

		return (int) $channel['term_id'];
	}

	public function cfd_save( $args ) {
		
		if ( ! empty( $args['subject'] ) )
			$post_title = $args['subject'];
		else
			$post_title = __( '(No Title)', CFD_PLUGIN_TEXT_DOMAIN );

		$post_status = $args['spam'] ? $this->glob['spam_status'] : 'publish';

		$postarr = array(
			'ID' => absint( $args['id'] ),
			'post_type' => $this->glob['post_type'],
			'post_status' => $post_status,
			'post_title' => $post_title,
			'post_content' => '' );

		$post_id = wp_insert_post( $postarr );

		if ( $post_id ) {
			update_post_meta( $post_id, '_subject', $args['subject'] );
			update_post_meta( $post_id, '_from', $args['from'] );
			update_post_meta( $post_id, '_from_name', $args['from_name'] );
			update_post_meta( $post_id, '_from_email', $args['from_email'] );

			foreach ( $args['fields'] as $key => $value ) {
				$meta_key = sanitize_key( '_field_' . $key );
				update_post_meta( $post_id, $meta_key, $value );
				$args['fields'][$key] = null;
			}

			update_post_meta( $post_id, '_fields', $args['fields'] );
			update_post_meta( $post_id, '_meta', $args['meta'] );
			update_post_meta( $post_id, '_properties', $args['properties'] );
			
			if ( term_exists( $args['channel'], $this->glob['channel_taxonomy'] ) ) {
				wp_set_object_terms( $post_id, $args['channel'], $this->glob['channel_taxonomy'] );
			}
		}

		return $post_id;
	}
}