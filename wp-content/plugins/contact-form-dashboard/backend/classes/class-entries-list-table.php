<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CFD_Entries_List_Table extends WP_List_Table {

	private $is_trash = false;
	private $is_spam = false;

	public static function define_columns() {
		$is_use_default = true;
		$default_columns = array( 
			'cb' => '<input type="checkbox" />', 
			'review' => '<span class="dashicons-before dashicons-email response-link" title="Reply"></span>',
			'subject' => __( 'Subject', CFD_PLUGIN_TEXT_DOMAIN ),
			'date' => __( 'Date', CFD_PLUGIN_TEXT_DOMAIN )
		);

		if (isset($_REQUEST['channel_id']) && $_REQUEST['channel_id'] != 0) {
			
			$glob_channel_id = $_REQUEST['channel_id'];
			$term = get_term_meta( $glob_channel_id, '_'.$glob_channel_id, true );
			$is_use_default = false;
		} else if (!isset($_REQUEST['channel_id']) && !isset($_REQUEST['mode']) && !isset($_REQUEST['post_status'])) {

			$entries = new CFD_Entries;
			$glob_channel_id = $entries->getGlob('channel_id');
			$term = get_term_meta( $glob_channel_id, '_'.$glob_channel_id, true );
			$is_use_default = false;
		}

		if ($is_use_default) {
			$columns = array(
				'channel' => __( 'Contact Form', CFD_PLUGIN_TEXT_DOMAIN ),
				'date' => __( 'Date', CFD_PLUGIN_TEXT_DOMAIN ) );
		} else {
			$columns = array();
			foreach ($term as $key => $value) {
				$col_name = $key;
				$columns[$col_name] = __( $key, CFD_PLUGIN_TEXT_DOMAIN );
			}
		}

		$columns = array_merge($default_columns, $columns);
		$columns = apply_filters( 'manage_cfd_posts_columns', $columns );

		return $columns;
	}

	public $entries;
	public $term;
	public $plugin_settings;

	function __construct() {
		parent::__construct( array(
			'singular' => 'post',
			'plural' => 'posts',
			'ajax' => false ) );

		$this->entries = new CFD_Entries;

		if (isset($_REQUEST['channel_id']) && !empty($_REQUEST['channel_id']) && $_REQUEST['channel_id'] != 0) {
			$glob_channel_id = $_REQUEST['channel_id'];

		} else if (!isset($_REQUEST['channel_id']) && !isset($_REQUEST['mode']) && !isset($_REQUEST['post_status'])) {
			$glob_channel_id = $this->entries->getGlob('channel_id');

		}

		$this->term = array('column_your_name');

		$this->plugin_settings = get_option( "cfd_settings" );
	}

	function prepare_items() {
		$current_screen = get_current_screen();
		
		$per_page = $this->get_items_per_page( 'cfd_per_page' );
		$this->_column_headers = $this->get_column_info();

		$args = array(
			'posts_per_page' => $per_page,
			'offset' => ( $this->get_pagenum() - 1 ) * $per_page,
			'orderby' => 'date',
			'order' => 'DESC' );
		
		if ( ! empty( $_REQUEST['s'] ) ) {
			$args['s'] = $_REQUEST['s'];
		}

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			if ( 'subject' == $_REQUEST['orderby'] ) {
				$args['meta_key'] = '_subject';
				$args['orderby'] = 'meta_value';
			} elseif ( 'from' == $_REQUEST['orderby'] ) {
				$args['meta_key'] = '_from';
				$args['orderby'] = 'meta_value';
			} else {
				$args['meta_key'] = '_field_' . $_REQUEST['orderby'];
				$args['orderby'] = 'meta_value';
			}
		}

		if ( ! empty( $_REQUEST['order'] ) && 'asc' == strtolower( $_REQUEST['order'] ) )
			$args['order'] = 'ASC';

		if ( ! empty( $_REQUEST['m'] ) ) {
			$args['m'] = $_REQUEST['m'];
		}

		if ( isset($_REQUEST['channel_id']) && ! empty( $_REQUEST['channel_id'] ) && $_REQUEST['channel_id'] != 0 ) {
			$args['channel_id'] = $_REQUEST['channel_id'];

		} else if ( isset($_REQUEST['channel_id']) && $_REQUEST['channel_id'] == 0 ) {
			
		} else if ( !isset($_REQUEST['mode']) && !isset($_REQUEST['post_status'])) {
			$args['channel_id'] = CFD_PLUGIN_DEFAULT_CHANNEL;
			
		}

		if ( ! empty( $_REQUEST['channel'] ) ) {
			$args['channel'] = $_REQUEST['channel'];
		}

		if ( ! empty( $_REQUEST['post_status'] ) ) {
			if ( 'trash' == $_REQUEST['post_status'] ) {
				$args['post_status'] = 'trash';
				$this->is_trash = true;
			} elseif ( 'spam' == $_REQUEST['post_status'] ) {
				$args['post_status'] = $this->entries->getGlob('spam_status');
				$this->is_spam = true;
			}
		}
		if ( isset($_REQUEST['response_status']) && $_REQUEST['response_status'] != '' ) {
			$args['response_status'] = $_REQUEST['response_status'];
		}
		
		$this->items = $this->entries->cfd_find( $args );
		
		$total_items = $this->entries->getGlob('found_items');
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page ) );
	}

	function get_views() {
		$status_links = array();
		$post_status = empty( $_REQUEST['post_status'] ) ? '' : $_REQUEST['post_status'];

		$per_page = $this->get_items_per_page( 'cfd_per_page' );
		
		if (isset($_REQUEST['channel_id']) && !empty($_REQUEST['channel_id']) && $_REQUEST['channel_id'] != 0) {
			$glob_channel_id = $_REQUEST['channel_id'];

		} else if (!isset($_REQUEST['channel_id']) && !isset($_REQUEST['mode']) && !isset($_REQUEST['post_status'])) {
			$glob_channel_id = CFD_PLUGIN_DEFAULT_CHANNEL;

		}

		$args = array(
			'posts_per_page' => -1,
			'orderby' => 'date',
			'order' => 'DESC' );

		// Inbox
		$publish_args = array_merge($args, array('post_status' => 'publish', 'channel_id' => $glob_channel_id));
		$publish = $this->entries->cfd_find( $publish_args );
		$posts_in_inbox = count($publish);

		$inbox = sprintf(
			_nx( 'Inbox <span class="count">(%s)</span>',
				'Inbox <span class="count">(%s)</span>',
				$posts_in_inbox, 'posts', CFD_PLUGIN_TEXT_DOMAIN ),
			number_format_i18n( $posts_in_inbox ) );

		$status_links['inbox'] = sprintf( '<a href="%1$s"%2$s>%3$s</a>',
			admin_url( 'admin.php?page=wts_wp_cfd_messages' ),
			( $this->is_trash || $this->is_spam ) ? '' : ' class="current"',
			$inbox );

		// Spam
		$spam_args = array_merge($args, array('post_status' => $this->entries->getGlob('spam_status')));
		$spam = $this->entries->cfd_find( $spam_args );
		
		$posts_in_spam = count($spam);

		$spam = sprintf(
			_nx( 'Spam <span class="count">(%s)</span>',
				'Spam <span class="count">(%s)</span>',
				$posts_in_spam, 'posts', CFD_PLUGIN_TEXT_DOMAIN ),
			number_format_i18n( $posts_in_spam ) );

		$status_links['spam'] = sprintf( '<a href="%1$s"%2$s>%3$s</a>',
			admin_url( 'admin.php?page=wts_wp_cfd_messages&post_status=spam' ),
			'spam' == $post_status ? ' class="current"' : '',
			$spam );

		// Trash
		$trash_args = array_merge($args, array('post_status' => 'trash'));
		$trash = $this->entries->cfd_find( $trash_args );
		$posts_in_trash = count($trash);

		if ( empty( $posts_in_trash ) )
			return $status_links;

		$trash = sprintf(
			_nx( 'Trash <span class="count">(%s)</span>',
				'Trash <span class="count">(%s)</span>',
				$posts_in_trash, 'posts', CFD_PLUGIN_TEXT_DOMAIN ),
			number_format_i18n( $posts_in_trash ) );

		$status_links['trash'] = sprintf( '<a href="%1$s"%2$s>%3$s</a>',
			admin_url( 'admin.php?page=wts_wp_cfd_messages&post_status=trash' ),
			'trash' == $post_status ? ' class="current"' : '',
			$trash );

		return $status_links;
	}

	function get_columns() {
		return get_column_headers( get_current_screen() );
	}

	function get_sortable_columns() {
		
		$plugin_search_settings = get_option( "cfd_search_settings" );

		$is_use_default = true;
		$default_columns = array( 
			'subject' => array( 'subject', false ),
			'date' => array( 'date', false ),
		);

		if (isset($_REQUEST['channel_id']) && $_REQUEST['channel_id'] != 0) {
			
			$glob_channel_id = $_REQUEST['channel_id'];
			$term = get_term_meta( $glob_channel_id, '_'.$glob_channel_id, true );
			$is_use_default = false;
		} else if (!isset($_REQUEST['channel_id']) && !isset($_REQUEST['mode']) && !isset($_REQUEST['post_status'])) {

			$entries = new CFD_Entries;
			$glob_channel_id = CFD_PLUGIN_DEFAULT_CHANNEL;
			$term = get_term_meta( $glob_channel_id, '_'.$glob_channel_id, true );
			$is_use_default = false;
		}
		
		if ($is_use_default) {
			$columns = array();
		} else {
			$columns = array();
			if (count($term) > 0 && $term != '') {
				foreach ($term as $key => $value) {
					$columns[$key] = array( $key, false );
				}
			}
		}

		$columns = array_merge($default_columns, $columns);
		return $columns;
	}

	function get_bulk_actions() {
		$actions = array();

		if ( $this->is_trash ) {
			$actions['untrash'] = __( 'Restore', CFD_PLUGIN_TEXT_DOMAIN );
		}

		if ( $this->is_trash || ! EMPTY_TRASH_DAYS ) {
			$actions['delete'] = __( 'Delete Permanently', CFD_PLUGIN_TEXT_DOMAIN );
		} else {
			$actions['trash'] = __( 'Move to Trash', CFD_PLUGIN_TEXT_DOMAIN );
		}

		if ( $this->is_spam ) {
			$actions['unspam'] = __( 'Not Spam', CFD_PLUGIN_TEXT_DOMAIN );
		} else {
			$actions['spam'] = __( 'Mark as Spam', CFD_PLUGIN_TEXT_DOMAIN );
		}

		return $actions;
	}

	function extra_tablenav( $which ) {
		if ((isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'all') || ( isset($_REQUEST['channel_id']) && $_REQUEST['channel_id'] == 0)) {
			$channel = 0;
		} else {
			$channel = $this->entries->getGlob('channel_id');
		}

		if ( ! empty( $_REQUEST['channel_id'] ) ) {
			$term = get_term( $_REQUEST['channel_id'], $this->entries->getGlob('channel_taxonomy') );

			if ( ! empty( $term ) && ! is_wp_error( $term ) )
				$channel = $term->term_id;

		} elseif ( ! empty( $_REQUEST['channel'] ) ) {
			$term = get_term_by( 'slug', $_REQUEST['channel'],
				$this->entries->getGlob('channel_taxonomy') );

			if ( ! empty( $term ) && ! is_wp_error( $term ) )
				$channel = $term->term_id;
		}

		?>
		<div class="alignleft actions full-width">
		<?php
				if ( 'top' == $which ) {

					if ( ! $this->is_spam && ! $this->is_trash ) {

						$this->months_dropdown( $this->entries->getGlob('post_type') );

						echo '<select name="response_status" id="response_status">
							<option selected="selected" value="">All</option>
							<option value="0" '. (($_REQUEST['response_status'] != '' && $_REQUEST['response_status'] == 0) ? 'selected="selected"' : '' ) .'>Pending</option>
							<option value="1" '. (($_REQUEST['response_status'] != '' && $_REQUEST['response_status'] == 1) ? 'selected="selected"' : '' ) .'>Replied</option>
						</select>';

						$terms = get_terms( array(
						    'taxonomy' => $this->entries->getGlob('channel_taxonomy'),
						    'show_count' => 1,
							'hide_empty' => 1,
							'hide_if_empty' => 1,
							'orderby' => 'name',
							'hierarchical' => 0
						) );

						echo '<select name="channel_id" id="channel_id" class="postform">
							<option value="0">' . __( 'All Forms', CFD_PLUGIN_TEXT_DOMAIN ) . '</option>';

							foreach ($terms as $term) {
								
								if ( isset( $this->plugin_settings['cfd_disable'] ) && in_array($term->term_id, $this->plugin_settings['cfd_disable']) ) {
			                        continue;
			                    }

								echo '<option class="level-0" value="'. esc_attr( $term->term_id ) .'" '.esc_attr( (( $term->term_id == $channel ) ? 'selected="selected"' : '') ).'>'. $term->name .'&nbsp;&nbsp;('. esc_html( $term->count ) .')</option>';	
							}
							
						echo '</select>';

						echo '<span class="filter-icon"><input type="submit" id="post-query-submit" class="button" value="' . esc_html( __( 'Filter', CFD_PLUGIN_TEXT_DOMAIN ) ) . '"></span>';

						echo '<span class="mark-icon float-right"><input type="submit" name="replied" id="replied" class="button" value="Mark as Replied"></span>';

						echo sprintf(
							'<a href="javascript:void(0)" class="button bulk-response float-right" data-toggle="modal" data-target="#cfd_msg_modal"><i class="dashicons-before dashicons-email"></i> Bulk Response</a>');
					}

					if ( ! $this->is_spam && ! $this->is_trash ) {
						echo '<span class="export-icon float-right"><input type="submit" name="export" id="export" class="button" value="Export"></span>';

					}
				}

				if ( $this->is_trash && current_user_can( 'cfd_delete_inbound_messages' ) ) {
					submit_button( __( 'Empty Trash', CFD_PLUGIN_TEXT_DOMAIN ),
						'button-secondary apply', 'delete_all', false );
				}
		?>
		</div>
		<?php
	}

	function column_default( $item, $column_name ) {
		$_fields = get_post_meta($item->ID, '_field_'.$column_name, true);
		return '<div class="inner-div">' . esc_html( (( $_fields ) ? $_fields : '-') ) .'</div>';
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			esc_html( $this->_args['singular'] ),
			esc_html( $item->ID ) );
	}

	function column_review( $item ) {
		
		$is_reviewed = get_post_meta($item->ID, '_is_reviewed', true);
		if ($is_reviewed) {
			$icon_color = 'color-green';
			$title = 'Already Replied';
		} else {
			$icon_color = 'color-red';
			$title = 'Reply';
		}
		return sprintf(
			'<a href="javascript:void(0)" title="%3$s" class="response-link" data-toggle="modal" data-target="#cfd_msg_modal" data-item="%2$s"><div class="dashicons-before dashicons-email %1$s"></div></a>',
			esc_html( $icon_color ),
			esc_html( $item->ID ),
			esc_html( $title ) );
	}

	function column_subject( $item ) {

		if ( $this->is_trash ) {
			$from = esc_html( __( get_post_meta($item->ID, '_from', true), CFD_PLUGIN_TEXT_DOMAIN ) );
			if ($from != '') {
				$final_return = '<strong>' . $from . '</strong> <br>' .'<strong>' . esc_html( $item->post_title ) . '</strong>';
			} else {
				$final_return = '<strong>' . esc_html( $item->post_title ) . '</strong>';
			}

			return $final_return;
		}

		$actions = array();
		$url = admin_url( 'admin.php?page=wts_wp_cfd_messages&post=' . absint( $item->ID ) . (isset($_REQUEST['channel_id']) ? '&channel_id='.$_REQUEST['channel_id'] : '') );
		
		$view_link = add_query_arg( array( 'action' => 'view' ), $url );

		if ( !$this->is_trash ) {
			$link = add_query_arg( array( 'action' => 'trash' ), $url );
			$link = wp_nonce_url( $link, 'cfd-trash-entries-message_' . $item->ID );

			$actions['trash'] = '<a href="' . $link . '">'
				. esc_html( __( 'Trash', CFD_PLUGIN_TEXT_DOMAIN ) ) . '</a>';
		}

		if ( $this->is_spam ) {
			$link = add_query_arg( array( 'action' => 'unspam' ), $url );
			$link = wp_nonce_url( $link, 'cfd-unspam-entries-message_' . $item->ID );

			$actions['unspam'] = '<a href="' . $link . '">'
				. esc_html( __( 'Not Spam', CFD_PLUGIN_TEXT_DOMAIN ) ) . '</a>';
			$from = esc_html( __( get_post_meta($item->ID, '_from', true), CFD_PLUGIN_TEXT_DOMAIN ) );
		} else {
			$link = add_query_arg( array( 'action' => 'spam' ), $url );
			$link = wp_nonce_url( $link, 'cfd-spam-entries-message_' . $item->ID );

			$actions['spam'] = '<a href="' . $link . '">'
				. esc_html( __( 'Spam', CFD_PLUGIN_TEXT_DOMAIN ) ) . '</a>';
		}

		$a = sprintf( '<a class="row-title" href="%1$s" title="%2$s">%3$s</a>',
			$view_link,
			esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', CFD_PLUGIN_TEXT_DOMAIN ), $item->post_title ) ),
			esc_html( $item->post_title ) );

		
		if ($from != '') {
			$final_return = '<strong>' . $from . '</strong> <br>' .'<strong>' . $a . '</strong> ' . $this->row_actions( $actions );
		} else {
			$final_return = '<strong>' . $a . '</strong> ' . $this->row_actions( $actions );
		}
		return $final_return;
	}

	function column_channel( $item ) {
		
		if ( empty( $item->ID ) )
			return '';

		$terms = get_the_terms($item->ID,  $this->entries->getGlob('channel_taxonomy'));
		
		$output .= sprintf( '<a href="%1$s" title="%2$s">%3$s</a>',
			$link, esc_attr( $terms[0]->name ), esc_html( $terms[0]->name ) );

		return $output;
	}

	function column_date( $item ) {
		$post = get_post( $item->ID );

		if ( ! $post )
			return '';

		$t_time = get_the_time( __( 'j F, Y g:i:s A', CFD_PLUGIN_TEXT_DOMAIN ), $item->ID );
		$m_time = $post->post_date;
		$time = get_post_time( 'G', true, $item->ID );

		$time_diff = time() - $time;

		if ( $time_diff > 0 && $time_diff < 24*60*60 )
			$h_time = sprintf( __( '%s ago', CFD_PLUGIN_TEXT_DOMAIN ), human_time_diff( $time ) );
		else
			$h_time = mysql2date( __( 'j F, Y', CFD_PLUGIN_TEXT_DOMAIN ), $m_time );

		return '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
	}
}