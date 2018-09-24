<?php

add_action( 'admin_menu', 'cfd_admin_menu', 8 );

function cfd_admin_menu() {
	global $_wp_last_object_menu;
	$_wp_last_object_menu++;

	$icon_svg = untrailingslashit( plugins_url( '', CFD_PLUGIN_BASENAME ) ) . '/assets/icon.png';
	add_menu_page(
		esc_html( __( 'Dashboard', CFD_PLUGIN_TEXT_DOMAIN ) ),
		esc_html( __( 'CFD', CFD_PLUGIN_TEXT_DOMAIN ) ),
		'manage_options', 
		'wts_wp_cfd_dashboard',
		'', 
		$icon_svg,
		$_wp_last_object_menu);

	$wts_wp_cfd_dashboard = add_submenu_page( 'wts_wp_cfd_dashboard',
		esc_html( __( 'Dashboard', CFD_PLUGIN_TEXT_DOMAIN ) ),
		esc_html( __( 'Dashboard', CFD_PLUGIN_TEXT_DOMAIN ) ),
		'manage_options', 
		'wts_wp_cfd_dashboard',
		'wts_wp_cfd_dashboard');

	$cfd_entries = add_submenu_page( 'wts_wp_cfd_dashboard',
		esc_html( __( 'Messages', CFD_PLUGIN_TEXT_DOMAIN ) ),
		esc_html( __( 'Messages', CFD_PLUGIN_TEXT_DOMAIN ) ),
		'manage_options', 
		'wts_wp_cfd_messages',
		'cfd_entries');
	add_action( 'load-' . $cfd_entries, 'cfd_load_entries' );
	
	$wts_wp_cfd_setting = add_submenu_page( 'wts_wp_cfd_dashboard',
		esc_html( __( 'Settings', CFD_PLUGIN_TEXT_DOMAIN ) ),
		esc_html( __( 'Settings', CFD_PLUGIN_TEXT_DOMAIN ) ),
		'manage_options', 
		'wts_wp_cfd_setting',
		'wts_wp_cfd_setting');
	add_action( 'load-' . $wts_wp_cfd_setting, 'cfd_load_setting' );

}

/* Updated Message */

add_action( 'cfd_message', 'cfd_message' );

function cfd_message() {

	if ( ! empty( $_REQUEST['message'] ) ) {
		
		if ( 'messagetrashed' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Messages trashed.', CFD_PLUGIN_TEXT_DOMAIN ) );
		elseif ( 'messageuntrashed' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Messages restored.', CFD_PLUGIN_TEXT_DOMAIN ) );
		elseif ( 'messagedeleted' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Messages deleted.', CFD_PLUGIN_TEXT_DOMAIN ) );
		elseif ( 'messagespammed' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Messages got marked as spam.', CFD_PLUGIN_TEXT_DOMAIN ) );
		elseif ( 'messageunspammed' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Messages got marked as not spam.', CFD_PLUGIN_TEXT_DOMAIN ) );
		elseif ( 'messagereplied' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Messages marked as replied.', CFD_PLUGIN_TEXT_DOMAIN ) );
		elseif ( 'messageresponsed' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Message sent.', CFD_PLUGIN_TEXT_DOMAIN ) );
		elseif ( 'settingssaved' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Contact Form Dashboard Plugin settings saved.', CFD_PLUGIN_TEXT_DOMAIN ) );
		else
			return;
	} else {
		return;
	}

	if ( empty( $updated_message ) ) {
		return;
	}

?>
<div id="message" class="updated"><p><?php echo $updated_message; ?></p></div>
<?php
}


/* Dashboard */

function wts_wp_cfd_dashboard() {
	$redirect_to_dashboard = admin_url( 'admin.php?page=wts_wp_cfd_dashboard' );

	$entries = new CFD_Entries();
	$forms = get_terms( $entries->getGlob('channel_taxonomy'), array( 'hide_empty' => false) );

	?>
	<div class="wrap">

		<h1><?php
			echo esc_html( __( 'Welcome to Contact Form Dashboard', CFD_PLUGIN_TEXT_DOMAIN ) );
		?></h1>
		<p class="about-description"><?php
			echo esc_html( __( 'CFD stores, organizes and presents all the submissions of the Contact Form 7 in a simplest way. It supports other interesting features like - Dashboard Analytics, Bulk emails / replies handling; Search contacts, Sort contacts and Export contacts.', CFD_PLUGIN_TEXT_DOMAIN ) );
		?></p>
		
		<div class="welcome-panel-column">
			<a class="button button-primary button-hero load-customize hide-if-no-customize" href="<?php echo admin_url( 'admin.php?page=wts_wp_cfd_messages' ); ?>">Go to Messages</a>
		</div>
		<?php  if ( count($forms) > 1 ) { 

				$args = array(
					'posts_per_page' => -1,
					'orderby' => 'date',
					'order' => 'DESC' 
				);
				$plugin_settings = get_option( "cfd_settings" );
				foreach ( $forms as $term ) {
					if ( $term->slug  != 'contact-form-7' ) {

						if ( isset( $plugin_settings['cfd_disable'] ) && in_array($term->term_id, $plugin_settings['cfd_disable']) ) {
	                        continue;
	                    }

						$url = admin_url( 'admin.php?page=wts_wp_cfd_messages&channel_id=' . $term->term_id );
					?>
						<div class="welcome-panel dashboard-section">
							<div class="welcome-panel-content">
								<a class="title-a-tag" href="<?php echo esc_html( $url ); ?>"><h2><?php echo esc_html( $term->name ); ?></h2></a>
								<div class="welcome-panel-column-container">
									
									<?php 
											 
										$publish_args = array_merge($args, array('post_status' => array( 'publish', 'trash', $entries->getGlob('spam_status') ), 'channel_id' => $term->term_id));
										$publish_post = $entries->cfd_find( $publish_args );

										$replied_args = array_merge($args, array(
											'channel_id' => $term->term_id,
											'post_status' => array( 'publish', 'trash', $entries->getGlob('spam_status') ),//'any',
											'meta_query' => array(
												array(
													'key'     => '_is_reviewed',
													'value'   => 1,
													'type'    => 'numeric',
													'compare' => '=',
												),
											), 
										));
										$replied_post = $entries->cfd_find( $replied_args );
										
										?>
										
										<div class="statistics-panel-column small-panel">
											<div class="inner-statistics-box">
												<div class="statistics-box-content">
													<h1 class="statistics-number" style="display: inline-block;">
														<?php echo esc_html( ((count($publish_post) >= 0) ? count($publish_post) : '-' ) ); ?>
													</h1>
												</div>
												<h2>Received</h2>
											</div>
										</div>
										<div class="statistics-panel-column small-panel">
											<div class="inner-statistics-box">
												<div class="statistics-box-content">
													<h1 class="statistics-number color-green" style="display: inline-block;">
													<?php echo esc_html( ((count($replied_post) >= 0) ? count($replied_post) : '-' ) ); ?>
													</h1>
												</div>
												<h2>Replied</h2>
											</div>
										</div>
										<div class="statistics-panel-column small-panel">
											<div class="inner-statistics-box">
												<div class="statistics-box-content">
													<h1 class="statistics-number color-orange" style="display: inline-block;">
													<?php echo esc_html( $entries->cfd_get_ava_response_time( $term->term_id ) ); ?>
													</h1>
												</div>
												<h2>Response Time</h2>
											</div>
										</div>
										<?php
										$keywords = ''; 
										$keywords = $entries->cfd_get_keywords( $term->term_id, true); 
										if ( $keywords != '' ) { ?>
											
										<div class="statistics-panel-column small-panel keywords-panel">
											<div class="inner-statistics-box">
												<div class="statistics-box-content"><?php echo $keywords; ?></div>
												<h2>Top Keywords</h2>
											</div>
										</div>
										<?php } ?>
								</div>
							</div>
						</div>
			<?php 
					}
				}
			} 
		?>
	</div>
	<?php
}

/* Entries Messages */

function cfd_load_entries() {

	$method_data = $_REQUEST;
	$action = $method_data['action'];

	if ( isset($method_data['channel_id']) && ! intval( $method_data['channel_id'] ) )
		$channel_id = intval( $method_data['channel_id'] );
	
	$redirect_to = admin_url( 'admin.php?page=wts_wp_cfd_messages'.(isset($channel_id) ? '&channel_id='.$channel_id : '') );

	if ( ! empty( $method_data['post'] ) && $action == 'spam' ) {
		if ( ! is_array( $method_data['post'] ) ) {
			check_admin_referer(
				'cfd-spam-entries-message_' . $method_data['post'] );
		} else {
			check_admin_referer( 'bulk-posts' );
		}

		$submitted = 0;

		foreach ( (array) $method_data['post'] as $post ) {
			
			if ( empty( $post ) ) {
				continue;
			}

			if ( ! current_user_can( 'cfd_spam_entries_message', $post ) ) {
				wp_die( __( 'You are not allowed to spam this item.', CFD_PLUGIN_TEXT_DOMAIN ) );
			}

			$entries = new CFD_Entries();
			$save_data = array(
			    'ID'           => $post,
			    'post_status' => $entries->getGlob('spam_status')
			);

			wp_update_post($save_data , true );            
			if (!is_wp_error($post_id)) {
				$submitted += 1;
			}

		}

		if ( ! empty( $submitted ) ) {
			$redirect_to = add_query_arg(
				array( 'message' => 'messagespammed' ), $redirect_to );
		}
		
		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( ! empty( $method_data['post'] ) && $action == 'unspam' ) {
		if ( ! is_array( $method_data['post'] ) ) {
			check_admin_referer(
				'cfd-unspam-entries-message_' . $method_data['post'] );
		} else {
			check_admin_referer( 'bulk-posts' );
		}

		$submitted = 0;

		foreach ( (array) $method_data['post'] as $post ) {
			
			if ( empty( $post ) ) {
				continue;
			}

			if ( ! current_user_can( 'cfd_unspam_entries_message', $post ) ) {
				wp_die( __( 'You are not allowed to unspam this item.', CFD_PLUGIN_TEXT_DOMAIN ) );
			}

			$save_data = array(
			    'ID'           => $post,
			    'post_status' => 'publish'
			);
			
			wp_update_post($save_data , true );            
			if (!is_wp_error($post_id)) {
				$submitted += 1;
			}

		}

		if ( ! empty( $submitted ) ) {
			$redirect_to = add_query_arg(
				array( 'message' => 'messageunspammed' ), $redirect_to );
		}

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( ! empty( $method_data['post'] ) && $action == 'trash' ) {
		if ( ! is_array( $method_data['post'] ) ) {
			check_admin_referer(
				'cfd-trash-entries-message_' . $method_data['post'] );
		} else {
			check_admin_referer( 'bulk-posts' );
		}

		$trashed = 0;

		foreach ( (array) $method_data['post'] as $post ) {
			
			if ( empty( $post ) ) {
				continue;
			}

			if ( ! current_user_can( 'cfd_delete_entries_message', $post ) ) {
				wp_die( __( 'You are not allowed to move this item to the Trash.', CFD_PLUGIN_TEXT_DOMAIN ) );
			}

			if ( ! wp_trash_post( $post ) ) {
				wp_die( __( 'Error in moving to Trash.', CFD_PLUGIN_TEXT_DOMAIN ) );
			}

			$trashed += 1;

		}

		if ( ! empty( $trashed ) ) {
			$redirect_to = add_query_arg(
				array( 'message' => 'messagetrashed' ), $redirect_to );
		}

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( ! empty( $method_data['post'] ) && $action == 'untrash' ) {
		if ( ! is_array( $method_data['post'] ) ) {
			check_admin_referer(
				'cfd-untrash-entries-message_' . $method_data['post'] );
		} else {
			check_admin_referer( 'bulk-posts' );
		}

		$untrashed = 0;

		foreach ( (array) $method_data['post'] as $post ) {
			
			if ( empty( $post ) ) {
				continue;
			}

			if ( ! current_user_can( 'cfd_delete_entries_message', $post ) ) {
				wp_die( __( 'You are not allowed to restore this item from the Trash.', CFD_PLUGIN_TEXT_DOMAIN ) );
			}

			if ( ! wp_untrash_post( $post ) ) {
				wp_die( __( 'Error in restoring from Trash.', CFD_PLUGIN_TEXT_DOMAIN ) );
			}

			$untrashed += 1;

		}

		if ( ! empty( $untrashed ) ) {
			$redirect_to = add_query_arg(
				array( 'message' => 'messageuntrashed' ), $redirect_to );
		}

		wp_safe_redirect( $redirect_to );
		exit();
	}	

	if ( ! empty( $method_data['post'] ) && $action == 'delete' ) {
		if ( ! is_array( $method_data['post'] ) ) {
			check_admin_referer(
				'cfd-delete-entries-message_' . $method_data['post'] );
		} else {
			check_admin_referer( 'bulk-posts' );
		}

		$deleted = 0;

		foreach ( (array) $method_data['post'] as $post ) {
			
			if ( empty( $post ) ) {
				continue;
			}

			if ( ! current_user_can('cfd_delete_entries_message', $post->id ) ) {
				wp_die( __( 'You are not allowed to delete this item.', CFD_PLUGIN_TEXT_DOMAIN ) );
			}

			if ( ! wp_delete_post( $post, true ) ) {
				wp_die( __( 'Error in deleting.', CFD_PLUGIN_TEXT_DOMAIN ) );
			}

			$deleted += 1;

		}
		
		if ( ! empty( $deleted ) ) {
			$redirect_to = add_query_arg(
				array( 'message' => 'messagedeleted' ), $redirect_to );
		}

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( ! empty( $method_data['post'] ) && isset( $method_data['replied'] ) ) {

		$replied = 0;

		foreach ( $method_data['post'] as $post ) {
			
			if ( empty( $post ) ) {
				continue;
			}
			
			$_is_reviewed = get_post_meta($post, "_is_reviewed", true);

			if ( $_is_reviewed ) {
				continue;
			}

			if ( ! current_user_can('cfd_response_entries_message', $post ) ) {
				wp_die( __( 'You are not allowed to change status of this item.', CFD_PLUGIN_TEXT_DOMAIN ) );
			}
			
			update_post_meta( $post, '_is_reviewed', 1);
			update_post_meta( $post, '_total_response', 1);
			update_post_meta( $post, '_response_manualy', 1);
			update_post_meta( $post, '_response_1', '');
			update_post_meta( $post, '_response_date_1', date(get_option('links_updated_date_format')));
			
			$replied += 1;
		}

		if ( ! empty( $replied ) ) {
			$redirect_to = add_query_arg(
				array( 'message' => 'messagereplied' ), $redirect_to );
		}
		
		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( ( ! empty( $method_data['item-id'] ) || ! empty( $method_data['post'] ) ) && isset($method_data['action-response']) && $method_data['action-response'] == 'response' ) { 
		
		if (trim($method_data['response']) != '') {
			
			$response = 0;
			$postlist = (! empty( $method_data['post'] ) ? $method_data['post'] : $method_data['item-id']);

			foreach ( (array) $postlist as $post ) {
				$sent = false;
				
				if ( empty( $post ) ) {
					continue;
				}

				if ( ! current_user_can( 'cfd_response_entries_message', $post ) ) {
					wp_die( __( 'You are not allowed to response about this item.', CFD_PLUGIN_TEXT_DOMAIN ) );
				}

				$_total_response = get_post_meta($post, "_total_response", true);
				if ($_total_response == '') {
					$_total_response = 0;
				}

				$_fields_arr = get_post_meta($post, "_fields", true);
				$_properties_arr = get_post_meta($post, "_properties", true);
				$_meta_arr = get_post_meta($post, "_meta", true);
				
				$subject = $_properties_arr['mail']['subject']; 
				$sender = $_properties_arr['mail']['sender'];
				$additional_headers = $_properties_arr['mail']['additional_headers'];

				$body = (isset($_properties_arr['mail']['body']) && $_properties_arr['mail']['body'] != '') ? $_properties_arr['mail']['body'] : '';

				$recipient = (isset($_properties_arr['mail']['recipient']) && $_properties_arr['mail']['recipient'] != '') ? $_properties_arr['mail']['recipient'] : '';

				foreach ($_fields_arr as $key => $value) {
			        
			        $subject = str_replace('['.$key.']', get_post_meta($post, "_field_".$key, true), $subject);
			        $sender = str_replace('['.$key.']', get_post_meta($post, "_field_".$key, true), $sender);
			        $additional_headers = str_replace('['.$key.']', get_post_meta($post, "_field_".$key, true), $additional_headers);

			        if ( $body != '' ) {
					     $body = str_replace('['.$key.']', get_post_meta($post, "_field_".$key, true), $body);
					}
			    }
			    
			    $mail_response = $method_data['response'];
			    $current_date = current_time(get_option('links_updated_date_format'));
			    $message_prefix = '<strong>On ' . $current_date . ', ' . $recipient . ' wrote: </strong><br><br>';

			    if ( $body != '' ) {

			    	$mail_response .= '<pre><blockquote style="border-left: 1px solid #ccc; padding-left: 12px; margin-left: 12px;">';
			    		
			    		if ($_total_response == 0) {
			    			$mail_response .= '<strong>On ' . $_meta_arr['date'] . ' ' . $_meta_arr['time'] . ', ' . htmlspecialchars($sender) . ' wrote: </strong><br><br>';
			    			$mail_response .= htmlspecialchars($body);
			    		}
			    		
			    		if ($_total_response > 0) {
			    			$last_response = get_post_meta($post, "_response_" . $_total_response , true);
			    			$mail_response .= $last_response;
			    		}
			    	$mail_response .= '</blockquote></pre>';
			    }

			    $reply_prefix = 'Re: ';
			    $headers[] = 'Content-Type: text/html; charset=UTF-8';
			    $headers[] = $additional_headers;
			    
				$sent = wp_mail( $sender, $reply_prefix . $subject, $mail_response, $headers);

				if ($sent) {
					update_post_meta( $post, '_is_reviewed', $sent);
					update_post_meta( $post, '_total_response', ($_total_response+1));
					update_post_meta( $post, '_response_'.($_total_response+1), $message_prefix . $mail_response);
					update_post_meta( $post, '_response_date_'.($_total_response+1), $current_date);
				}
				$response += 1;

			}
		}

		if ( ! empty( $response ) ) {
			$redirect_to = add_query_arg(
				array( 'message' => 'messageresponsed' ), $redirect_to );
		}
		
		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( ! empty( $method_data['export'] ) ) {
		
		$entries = new CFD_Entries;

		$filename = sprintf( 'cfd-%s.csv', date( 'Y-m-d' ) );

		header( 'Content-Description: File Transfer' );
		header( "Content-Disposition: attachment; filename=$filename" );
		header( 'Content-Type: text/csv; charset=' . get_option( 'blog_charset' ) );

		$args = array(
			'posts_per_page' => -1,
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

		$items = $entries->cfd_find( $args );

		if ( empty( $items ) ) {
			exit();
		}

		$labels[] = __( 'Subject', CFD_PLUGIN_TEXT_DOMAIN );
		$labels[] = __( 'Contact Form', CFD_PLUGIN_TEXT_DOMAIN );
		if ($_REQUEST['channel_id'] != 0) {
			$fields = get_post_meta( $items[0]->ID, '_fields', true );
			$labels = array_merge($labels, array_keys( $fields ));
		}
		$labels[] = __( 'Date', CFD_PLUGIN_TEXT_DOMAIN );
		
		echo cfd_msg_csv_row( $labels );
		foreach ( $items as $item ) {
			$item_fields = get_post_meta( $item->ID, '_fields', true );
			$row = array();

			foreach ( $labels as $label ) {
				
				if ($_REQUEST['channel_id'] != 0) {
					$field_val = get_post_meta( $item->ID, '_field_' . $label, true );
				}

				if ($label == __( 'Subject', CFD_PLUGIN_TEXT_DOMAIN )) {
					$field_val = get_post_meta( $item->ID, '_subject', true );
				}
				if ($label == __( 'Contact Form', CFD_PLUGIN_TEXT_DOMAIN )) {
					$terms = get_the_terms( $item->ID,  $entries->getGlob('channel_taxonomy'));
					$field_val = $terms[0]->name;
				}
				
				$col = isset( $field_val ) ? $field_val : '';
				if ($label != __( 'Date', CFD_PLUGIN_TEXT_DOMAIN )) {
					$row[] = $col;
				}
			}

			$row[] = get_post_time( 'c', true, $item->ID );
			echo "\r\n" . cfd_msg_csv_row( $row );
		}

		exit();
	}

	if ( ! class_exists( 'CFD_Entries_List_Table' ) )
		require_once CFD_PLUGIN_DIR . '/backend/classes/class-entries-list-table.php';

	$current_screen = get_current_screen();

	add_filter( 'manage_' . $current_screen->id . '_columns', array( 'CFD_Entries_List_Table', 'define_columns' ) );

	$args = array(
		'label' => __('Messages per page', CFD_PLUGIN_TEXT_DOMAIN),
		'default' => 20,
		'option' => 'cfd_per_page'
	);
	add_screen_option( 'per_page', $args );

	$current_screen->add_help_tab( array( 
	   'id' => 001,            //unique id for the tab
	   'title' => 'Overview',      //unique visible title for the tab
	   'content' => '<p>This screen provides access to all of your messages. You can customize the display of this screen to suit your workflow.</p>',  //actual help text
	) );

	$current_screen->add_help_tab( array( 
	   'id' => 002,            //unique id for the tab
	   'title' => 'Screen Content',      //unique visible title for the tab
	   'content' => '<p>You can customize the display of this screen’s contents in a number of ways:</p><p>You can hide/display columns based on your needs and decide how many messages to list per screen using the Screen Options tab.</p><p>You can refine the list to show only messages of a specific form or from a specific month by using the dropdown menus above the messages list. Click the Filter button after making your selection.</p><p>You can change sorting order by clicking on column titles.</p>',  //actual help text
	) );

	$current_screen->add_help_tab( array( 
	   'id' => 003,            //unique id for the tab
	   'title' => 'Available Actions',      //unique visible title for the tab
	   'content' => '<p>Hovering over a row in the messages list will display action links that allow you to manage your message. You can perform the following actions:</p>
			<ul><li>Spam marks message as Spam and moves it to spam folder.</li>
			<li>Not Spam moves the message to inbox folder.</li>
			<li>Trash removes your message from this list and places it in the trash, from which you can permanently delete it.</li>
			<li>You can view message detail by clicking on message text.</li></ul>',  //actual help text
	) );

	$current_screen->add_help_tab( array( 
	   'id' => 004,            //unique id for the tab
	   'title' => 'Bulk Actions',      //unique visible title for the tab
	   'content' => '<p>You can also mark multiple messages as spam or move  to the trash at once. Select the messages you want to act on using the checkboxes, then select the action you want to take from the Bulk Actions menu and click Apply.</p><p>You can reply to multiple messages at once. Select the messages you want to reply to, then click the Bulk Response button, type your reply and click Send Email button.</p><p>You can mark multiple messages as replied at once. Select the messages you want to reply to, then click the Mark as Replied button.</p>',  //actual help text
	) );

}

function cfd_entries() { 

	$post_id = ! empty( $_REQUEST['post'] ) ? $_REQUEST['post'] : '';
	$entries = new CFD_Entries();

	if ( $entries->getGlob('post_type') == get_post_type( $post_id ) && $_REQUEST['action'] == 'view' ) {
		cfd_entries_view();
		return;
	}
	
	$list_table = new CFD_Entries_List_Table();
	$list_table->prepare_items();

	?>
	<div class="wrap cfd-section">

	<h1><?php
		echo esc_html( __( 'Messages', CFD_PLUGIN_TEXT_DOMAIN ) );

		if ( ! empty( $_REQUEST['s'] ) ) {
			echo sprintf( '<span class="subtitle">'
				. __( 'Search results for &#8220;%s&#8221;', CFD_PLUGIN_TEXT_DOMAIN )
				. '</span>', esc_html( $_REQUEST['s'] ) );
		}
	?></h1>

	<?php do_action( 'cfd_message' ); ?>

	<?php $list_table->views(); ?>

	<form method="get" action="">
		<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
		<?php $list_table->search_box( __( 'Search Messages', CFD_PLUGIN_TEXT_DOMAIN ), 'cfd' ); ?>
		<?php $list_table->display(); ?>
		<?php echo cfd_response_model(); ?>
	</form>
	
	</div>
	<?php
}

function cfd_entries_view() {
	$post = $_REQUEST['post'];
	if ( ! empty( $post ) && ( $post = get_post( $post ) ) ) { 
		
		include CFD_PLUGIN_DIR . '/backend/view-entries.php';

	} else {
		return;
	}
}

function cfd_response_model() {
	
	$model = '<!-- Modal -->
	<div class="modal fade" id="cfd_msg_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display:none;">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      	<input type="hidden" name="action-response" value="response"/>
	      	<div class="modal-header">
	        	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        	<h2> Send Email Response </h2>
	      	</div>
	      	<div class="modal-body">
				
		        <div class="form-group">
		            <legend>Write your message here.</legend>
		            <textarea class="large-text response-body" name="response" id="response-body" onkeyup="cfd_checkbody()"></textarea>
		        </div>
	      		<input class="item-id" type="hidden" name="item-id"/>
	      	</div>
	      	<div class="modal-footer">
	        	<button type="button" class="button-primary" data-dismiss="modal">Close</button>
	        	<input type="submit" name="email-send" id="email-send" class="button-primary" value="Send Email" disabled="disabled" />
	      	</div>
	    </div>
	  </div>
	</div>';
	
	return $model;
}

/* Plugin Setting */

function cfd_load_setting() {
	
	$submit = sanitize_text_field( $_POST['submit'] );
	$search_setting = sanitize_text_field( $_POST['search_setting'] );
	$keyword_search_setting = sanitize_text_field( $_POST['keyword_search_setting'] );
	$redirect_to = admin_url( 'admin.php?page=wts_wp_cfd_setting' );
	
	if ( isset( $submit ) && $submit != '' ) {
		unset($_POST['submit']);
		if ( isset($search_setting) && $search_setting != '' ) {
			unset($_POST['search_setting']);
			update_option( "cfd_search_settings", $_POST );
		} else if ( isset($keyword_search_setting) && $keyword_search_setting != '' ) {
			unset($_POST['keyword_search_setting']);
			update_option( "cfd_keyword_search_settings", $_POST );
		} else {
			$updated = update_option( "cfd_settings", $_POST );
   		}

   		$redirect_to = add_query_arg(
				array( 'message' => 'settingssaved' ), $redirect_to );

   		wp_safe_redirect( $redirect_to );
		exit();
	}
}

function wts_wp_cfd_setting() {
	$setting = new CFD_Setting();
	
	?>
	<div class="wrap">

	<h1><?php
		echo esc_html( __( 'Settings', CFD_PLUGIN_TEXT_DOMAIN ) );
	?></h1>
	<?php 

		do_action( 'cfd_message' );

		//generic HTML and code goes here
		$setting->cfd_settings_page();

	?>
	</div>
	<?php
}