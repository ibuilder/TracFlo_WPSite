<?php

/**
 * @link              http://www.TracFlo.io/
 * @since             1.0.0
 * @package           TracFlo_Tickets
 *
 * @wordpress-plugin
 * Plugin Name:       TracFlo Subcontractor: Tickets
 * Plugin URI:        http://www.TracFlo.io/
 * Description:       Adds ticket functionality.
 * Version:           1.0.10
 * Author:            TracFlo
 * Author URI:        http://www.TracFlo.io/
 * Contributor:       Jake Snyder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists('TracFlo_Base') && file_exists(__DIR__ . '/tracflo-base.php') ) {
	include( __DIR__ . '/tracflo-base.php' );
}

$class_name = 'TracFlo_Ticket';
if ( ! class_exists($class_name) && class_exists('TracFlo_Base') ) :

class TracFlo_Ticket extends TracFlo_Base
{
	public function __construct() {
		parent::__construct( strtolower(__CLASS__), __FILE__ );
	}

	/**
	 * Class settings
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function settings()
	{
		$this->settings['pages'] = [
			"add-{$this->plugin_name}" => 'Add Ticket',
		];
		$this->settings['post_types'] = [
			'ticket'    => [
				'labels'              => [
					'name'               => __( 'Tickets', $this->plugin_name ),
					'singular_name'      => __( 'Ticket', $this->plugin_name ),
					'all_items'          => __( 'All Tickets', $this->plugin_name ),
					'add_new'            => __( 'Add New', $this->plugin_name ),
					'add_new_item'       => __( 'Add New Ticket', $this->plugin_name ),
					'edit'               => __( 'Edit', $this->plugin_name  ),
					'edit_item'          => __( 'Edit Ticket', $this->plugin_name ),
					'new_item'           => __( 'New Ticket', $this->plugin_name ),
					'view_item'          => __( 'View Ticket', $this->plugin_name ),
					'search_items'       => __( 'Search Tickets', $this->plugin_name ), 
					'not_found'          => __( 'Nothing found in the Database.', $this->plugin_name ), 
					'not_found_in_trash' => __( 'Nothing found in Trash', $this->plugin_name ),
					'parent_item_colon'  => '',
				],
				'description'         => __( 'Tickets.', $this->plugin_name ),
				'menu_icon'           => 'dashicons-list-view',
				'public'              => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'show_ui'             => true,
				'query_var'           => true,
				'rewrite'	          => [ 'slug' => 'ticket', 'with_front' => false ],
				'has_archive'         => 'tickets',
				'menu_position'       => 53,
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => [ 'title', 'author', 'revisions' ],
				'custom_enter_title'  => __( 'Ticket #', $this->plugin_name ),
				'columns'             => [
					#'delete'              => [ 'date', 'author' ],
					'add'                 => [
						'project'     => 'Project',
						'co'          => 'Change Order',
						'total'       => 'Total',
					],
				],
			],
		];
		$this->settings['taxonomies'] = [
			"{$this->plugin_name}_status"      => [
				'post_types'                      => [ $this->plugin_name ],
				'hierarchical'                    => true,
				'labels'                          => [
					'name'                           => _x( 'Ticket Statuses', 'taxonomy general name' ),
					'singular_name'                  => _x( 'Ticket Status', 'taxonomy singular name' ),
					'search_items'                   => __( 'Search Ticket Statuses', $this->plugin_name ),
					'popular_items'                  => __( 'Popular Ticket Statuses', $this->plugin_name ),
					'all_items'                      => __( 'All Ticket Statuses', $this->plugin_name ),
					'parent_item'                    => null,
					'parent_item_colon'              => null,
					'edit_item'                      => __( 'Edit Ticket Status', $this->plugin_name ),
					'update_item'                    => __( 'Update Ticket Status', $this->plugin_name ),
					'add_new_item'                   => __( 'Add New Ticket Status', $this->plugin_name ),
					'new_item_name'                  => __( 'New Ticket Status Name', $this->plugin_name ),
					'separate_items_with_commas'     => __( 'Separate Ticket Statuses with commas', $this->plugin_name ),
					'add_or_remove_items'            => __( 'Add or remove Ticket Statuses', $this->plugin_name ),
					'choose_from_most_used'          => __( 'Choose from the most used Ticket Statuses', $this->plugin_name ),
					'not_found'                      => __( 'No Ticket Statuses found.', $this->plugin_name ),
					'menu_name'                      => __( 'Ticket Statuses', $this->plugin_name ),
				],
				'show_ui'                         => true,
				'show_admin_column'               => true,
				'query_var'                       => true,
				'rewrite'                         => [ 'slug' => 'ticket-status' ],
				'terms'                           => [
					'approve'                        => 'Approve',
					'archive'                        => 'Archive',
					'attached'                       => 'Attached',
					'reject'                         => 'Reject',
					'revise'                         => 'Revise',
					'submitted'                      => 'Submitted',
					'void'                           => 'Void',
				],
			],
		];
/** /
T&M Stage: submitted, revise
Closed: reject, void
old/incorrect--Rates Stage: approvetm, rates
old/incorrect--Locked: approverate (can be attached, but not edtied)
Archive = temporary lock
/**/
		$this->settings['terms'] = [
			
		];
		$this->settings['notifications'][] = [
			'key'     => 'sent',
			'value'   => 'ticket',
			'message' => __( "Ticket sent.", 'tracflo' ),
			'args'    => 'dismiss=true',
		];
		$this->settings['notifications'][] = [
			'key'     => 'action',
			'value'   => 'ticketapprove',
			'message' => __( "Ticket approved.", 'tracflo' ),
			'args'    => 'dismiss=true',
		];
		$this->settings['notifications'][] = [
			'key'     => 'action',
			'value'   => 'ticketreject',
			'message' => __( "Ticket rejected.", 'tracflo' ),
			'args'    => 'dismiss=true',
		];
		$this->settings['notifications'][] = [
			'key'     => 'action',
			'value'   => 'ticketrevise',
			'message' => __( "Ticket request sent.", 'tracflo' ),
			'args'    => 'dismiss=true',
		];
	}

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function init()
	{
		$this->rewrites();

		// WP Admin
		add_filter( 'custom_column_ticket_co',              [ $this, 'column_ticket_co' ], 10, 4 );
		add_filter( 'custom_column_ticket_project',         [ $this, 'column_ticket_project' ], 10, 4 );
		add_filter( 'custom_column_ticket_total',           [ $this, 'column_ticket_total' ], 10, 4 );

		// Saving & Loading Posts
		add_action( 'pre_get_posts',                        [ $this, 'pre_get_posts' ] );
		add_action( 'save_post',                            [ $this, 'save_post' ], 20 );

		add_filter( 'the_posts',                            [ $this, 'add_public_private_post' ], 1 );

		// ACF
		add_action( 'acf/save_post',                        [ $this, 'acf_save_post' ], 20 );
		#add_filter( 'acf/load_field/name=number',           [ $this, 'load_number' ] );
		add_filter( 'acf/load_field/name=type',             [ $this, 'load_type' ] );
		add_filter( 'acf/fields/post_object/query/key=field_58d4c271742df', [ $this, 'limit_project_posts' ], 10, 3 );
		add_filter( 'acf/load_field/key=field_58d4c271742df', [ $this, 'load_project' ] );
		add_filter( 'acf/load_field/key=field_58d4c2de742e1', [ $this, 'default_start_date' ] );
		add_filter( 'acf/load_field/key=field_58d4c65a742e7',  [ $this, 'default_hours' ], 10, 3 );

		// TracFlo
		add_action( "{$this->prefix}/ticket/update",        [ $this, 'update_ticket' ] );
		add_action( "{$this->prefix}/ticket/update_totals", [ $this, 'update_totals' ] );

		// Ajax number
		add_action( 'wp_ajax_next_ticket_number',           [ $this, 'ajax_next_number' ] );
	}

	/**
	 * Use site default hours option setting
	 *
	 * @author  Jake Snyder
	 * @since	1.0.10
	 * @return	void
	 */
	public function default_hours( $field ) {
		$field['default_value'] = get_option( 'options_default_hours', '8' );
		return $field;
	}

	/**
	 * This adds support for the public posts with password URLs and for masked PDF urls
	 * eg: /client/ticket/23874ioj32io42u389rpj34
	 * eg: /ticket/141.pdf
	 *
	 * @author  Jake Snyder
	 * @since	1.0.7
	 * @param	array $posts An array of current posts selected if any
	 * @return	array $posts An array of current posts selected if any
	 */
	public function add_public_private_post( $posts )
	{
		global $wpdb, $wp_query;

		// Check if the requested page matches our target, and no posts have been retrieved
		if ( ! is_admin() && ! $posts && ! empty($wp_query->query_vars['post_type']) && 'ticket' === $wp_query->query_vars['post_type'] && ! empty($wp_query->query_vars['ticket']) && ! is_numeric($wp_query->query_vars['ticket']) && empty($wp_query->in_session) ) {
			$wp_query->in_session = 1;
			$token      = $wp_query->query_vars['ticket'];
			$pdf        = false;
			$post_id    = null;
			$contact_id = null;
			if ( false !== strpos($token, '.pdf') ) {
				$token = str_replace('.pdf', '', $token);
				$pdf   = true;
			}

			$info = $this->get_token_info( $token );
			if ( $info && is_array($info) ) {
				$post_id    = $info['post_id'];
				$contact_id = $info['contact_id'];
			} else {
				// Generic password
				$query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_value = '%s' LIMIT 1";
				$post_id = $wpdb->get_var( $wpdb->prepare($query, $token) );
			}

/** /
			if ( is_numeric($id) ) {
				$post_id = $id;
			} else {
				$query = "SELECT post_id, meta_key FROM $wpdb->postmeta WHERE meta_value = '%s' LIMIT 1";
				$post_id = $wpdb->get_var( $wpdb->prepare($query, $id) );
			}
/**/
			if ( $pdf ) {
				$upload_dir = wp_upload_dir();
				$up_path    = $upload_dir['basedir'];
				$up_url     = $upload_dir['baseurl'];
				$printed    = trac_print_pdf( $post_id );

				$pdf_url    = get_post_meta( $post_id, 'pdf', true );
				$pdf_path   = str_replace($up_url, $up_path, $pdf_url);
				// Load the PDF
				if ( file_exists($pdf_path) ) {
					header("Content-Length: " . filesize ( $pdf_path ) ); 
					header("Content-type: application/pdf"); 
					header("Content-disposition: inline; filename=" . basename( $pdf_path ) );
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

					ob_clean();
					flush();
					readfile( $pdf_path );
				}
				die;
			}

			// Add the the post deciphered from password
			$posts   = [];
			$post    = get_post( $post_id );
			if ( ! $post ) {
				wp_safe_redirect( home_url( '/' ) );
			}
			$posts[] = $post;

			$wp_query->query_vars['contact_id'] = $contact_id;

			// Adjust settings to support the fake post
			$wp_query->is_post             = true;
			$wp_query->is_singular         = true;
			$wp_query->is_home             = false;
			$wp_query->is_archive          = false;
			$wp_query->is_category         = false;
			unset( $wp_query->query['error'] );
			$wp_query->query_vars['error'] = '';
			$wp_query->query['post_type']  = 'ticket';
			$wp_query->query['post']       = $posts[0]->post_name;
			$wp_query->is_404              = false;
		}
		return $posts;
	}

	/**
	 * See if no compose email page exists, and add it
	 *
	 * @author  Jake Snyder
	 * @since	1.0.3
	 * @return	object $posts Modified $posts with the new register post
	 * /
	public function add_post( $posts )
	{
		global $wp, $wp_query;
/** /
echo '<pre style="clear:both;font-size:0.7em;text-align:left;width:100%;">';
print_r($wp->request);
print_r($wp_query->query_vars);
echo "</pre>\n";
exit;
/** /
		// Check if the requested page matches our target, and no posts have been retrieved
		if ( ! $posts && ! empty($this->settings['pages']) && array_key_exists( strtolower($wp->request), $this->settings['pages'] ) ) {
			// Add the fake post
			$posts   = [];
			$posts[] = $this->create_post( strtolower($wp->request) );

			// Adjust settings to support the fake post
			$wp_query->is_page             = true;
			$wp_query->is_singular         = true;
			$wp_query->is_home             = false;
			$wp_query->is_archive          = false;
			$wp_query->is_category         = false;
			unset( $wp_query->query['error'] );
			$wp_query->query_vars['error'] = '';
			$wp_query->query['post_type']  = 'page';
			$wp_query->query['page']       = $posts[0]->post_name;
			$wp_query->is_404              = false;
/** /
		} elseif ( false !== strpos($wp->request, 'clients/tickets/') ) {
			$tid = str_replace('clients/tickets/', '', $wp->request);
			if ( $tid ) {
				$post_id = base64_decode($tid);
				if ( $post = get_post( $post_id ) ) {
					$wp_query->is_page             = false;
					$wp_query->is_singular         = true;
					$wp_query->is_home             = false;
					$wp_query->is_archive          = false;
					$wp_query->is_category         = false;
					unset( $wp_query->query['error'] );
					$wp_query->query_vars['error'] = '';
					$wp_query->query['post_type']  = 'ticket';
					$wp_query->query['post']       = $post->post_name;
					$wp_query->is_404              = false;
				}
			}
/** /
		}
		return $posts;
	}

	/**
	 * Custom Rewrite Rules
	 *
	 * @author  Jake Snyder
	 * @since	1.0.5
	 * @return	void
	 */
	public function rewrites()
	{
		/**
		 * Support the public view for tickets
		 * /
		add_rewrite_rule(
			"clients/tickets/([^/]*)/?$",
			'index.php?ticket=1&tid=$matches[1]&',
			'top'
		);
		/**
		 * Support the public view for tickets
		 */
		add_rewrite_rule(
			"client/ticket/([^/]*)/?$",
			'index.php?ticket=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			"client/ticket/([^/]*).pdf$",
			'index.php?ticket=$matches[1]&pdf_print=1',
			'top'
		);
		add_filter( 'query_vars', [ $this, 'query_vars' ] );
	}
	public function query_vars( $vars )
	{
		$vars[] = 'pdf_print';
		return $vars;
	}

	/**
	 * Update actions
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function update_actions()
	{
		global $wpdb;

		if ( ! empty($_GET['action']) && is_singular( 'ticket' ) ) {//is_singular( $this->plugin_name )

			if ( 'delete' === $_GET['action'] && ! empty($_GET['confirm']) && wp_verify_nonce( $_GET['confirm'], 'delete_ticket_' . get_the_ID() ) ) {
				// Trash the post
				wp_trash_post( get_the_ID() );
				$this->update_ticket( get_the_ID() );
				wp_safe_redirect( add_query_arg( 'delete', 'ticket', home_url( '/tickets/' ) ) ); die;
			} elseif ( 'sendticket' == $_GET['action'] ) {
				if ( has_term( [ 'attached', 'reject', 'void' ], 'ticket_status', get_the_ID() ) ) {
					wp_safe_redirect( add_query_arg( 'sent', 'false', get_permalink() ) ); die;
				}
				if ( empty($_POST['message']) || empty($_POST['message']['subject']) || empty($_POST['message']['body']) ) { return; }

				$current_user = wp_get_current_user();
				$body = '';

				$contacts = [];
				$pm = '';
				$subject = $_POST['message']['subject'];
				$body .= $_POST['message']['body'];
				$body .= 'Submitted by: ' . $current_user->display_name . ', ' . trac_option('name');
				$attach_pdf = ( ! empty($_POST['message']['attach_pdf']) ? $_POST['message']['attach_pdf'] : false );

				foreach ( $_POST as $key => $value ) {
					if ( false !== strpos($key, 'send-to-') ) {
						$post_id = str_replace('send-to-', '', $key);
						if ( $email = get_field('email', $post_id) ) {
							$contacts[] = [
								'email' => $email,
								'id'    => $post_id,
								'name'  => get_the_title( $post_id ),
							];
						}
					} elseif ( false !== strpos($key, 'send-pm-') ) {
						$user_id = str_replace('send-pm-', '', $key);
						if ( $pm_user = get_user_by( 'id', $user_id ) ) {
							$pm = $pm_user->user_email;
						}
					}
				}

				// Add history and udpate status
				wp_set_object_terms( get_the_ID(), 'submitted', 'ticket_status', false );
				$history_message = 'Submitted to ';
				foreach ( $contacts as $contact ) {
					$history_message .= $contact['name'] . ' &lt;' . $contact['email'] . '&gt;, ';
				}
				$history_message  = rtrim($history_message, ', ');
				$history_message .= " from " . $current_user->display_name . ' &lt;' . get_option( 'admin_email' ) . '&gt;';
				do_action( 'trac/history/add', get_the_ID(), $history_message );

				$attachments = null;
				if ( $attach_pdf ) {
					$upload_dir = wp_upload_dir();
					$up_path    = $upload_dir['basedir'];
					$up_url     = $upload_dir['baseurl'];
					$pdf_url    = get_post_meta( get_the_ID(), 'pdf', true );
					$pdf_path   = str_replace($up_url, $up_path, $pdf_url);
					if ( $pdf_path ) {
						$attachments = [ $pdf_path ];
					}
				}

				$headers = [
					'Content-Type: text/html; charset=UTF-8',
					'From: ' . $current_user->display_name . ' <info@tracflo.io>',#trac_option( 'name' )
					'Reply-To: ' . $current_user->display_name . ' <' . get_option( 'admin_email' ) . '>',
				];

				foreach ( $contacts as $contact ) {
					if ( ! empty($_POST['message']['include_link_to_client_invoice']) ) {
						$token = get_post_meta( get_the_ID(), 'token_' . $contact['id'], true );
						if ( ! $token ) {
							$token = $this->create_token( get_the_ID(), $contact['id'] );
						}
						$send_body = '<a href="' . home_url() . '/client/ticket/' . $token . '/">View and approve ticket online</a>' . "\r\n" . "\r\n" . $body;
					}
 					$send_body = nl2br( $send_body );
					wp_mail( $email, $subject, $send_body, $headers, $attachments );
				}

				if ( $pm ) {
					if ( ! empty($_POST['message']['include_link_to_client_invoice']) ) {
						$password = get_post_meta( get_the_ID(), 'password', true );
						$send_body = '<a href="' . home_url() . '/client/ticket/' . $password . '/">View and approve ticket online</a>' . "\r\n" . "\r\n" . $body;
					}
 					$send_body = nl2br( $send_body );
					wp_mail( $pm, $subject, $send_body, $headers, $attachments );
				}

				if ( ! empty($_POST['message']['send_me_a_copy']) ) {
					if ( ! empty($_POST['message']['include_link_to_client_invoice']) ) {
						$password = get_post_meta( get_the_ID(), 'password', true );
						$send_body = '<a href="' . home_url() . '/client/ticket/' . $password . '/">View and approve ticket online</a>' . "\r\n" . "\r\n" . $body;
					}
 					$send_body = nl2br( $send_body );
					$current_user = wp_get_current_user();
					wp_mail( $current_user->user_email, $subject, $send_body, $headers, $attachments );
				}

				wp_safe_redirect( add_query_arg( 'sent', 'ticket', get_permalink() ) ); die;
			} elseif ( 'approveticket' == $_GET['action'] ) {
				if ( has_term( [ 'attached', 'reject', 'void' ], 'ticket_status', get_the_ID() ) ) {
					wp_safe_redirect( add_query_arg( 'approve', 'false', get_permalink() ) ); die;
				}
				// Add history and udpate status
				$history = "Approved Time &amp; Materials by: ";
				$history .= ( ! empty($_POST['message']['name']) ) ? esc_html($_POST['message']['name']) : '';
				$history .= ( ! empty($_POST['message']['email']) ) ? ' &lt;' . esc_html($_POST['message']['email']) . '&gt;' : '';
				if ( ! empty($_POST['signature']) ) {
					$history .= '<br><img src="' . esc_attr( $_POST['signature'] ) . '" alt="">';
				}
				do_action( 'trac/history/add', get_the_ID(), $history );
				wp_set_object_terms( get_the_ID(), 'approve', 'ticket_status', false );

				// Email and update BIC
				$project_id = get_post_meta( get_the_ID(), 'project', true );
				$user_ids = get_post_meta( $project_id, 'manager', true );
				if ( $user_ids && ! is_array($user_ids) ) {
					$user_ids = [ $user_ids ];
				}

				$contacts = [];
				$pm = '';
				$subject = 'Ticket approved: ' . get_the_title();
				$body = 'Ticket approved: ' . get_the_title();
				$headers = ['Content-Type: text/html; charset=UTF-8', 'From: ' . get_option( 'options_company_name' ) . ' <' . get_option( 'admin_email' ) . '>' . "\r\n"];
				$attachments = '';
				if ( $user_ids ) {
					foreach ( $user_ids as $user_id ) {
						update_post_meta( get_the_ID(), 'bic', $user_id );
						$user_info = get_userdata($user_id);
						wp_mail( $user_info->user_email, $subject, $body, $headers );
					}
				}
				$foreman_ids = get_post_meta( $project_id, 'foreman', true );
				if ( $foreman_ids ) {
					foreach ( $foreman_ids as $user_id ) {
						$user_info = get_userdata($user_id);
						wp_mail( $user_info->user_email, $subject, $body, $headers );
					}
				}

				// Redirect
				$password = get_post_meta( get_the_ID(), 'password', true );
				wp_safe_redirect( add_query_arg( 'action', 'ticketapprove', home_url( '/client/ticket/' . $password . '/' ) ) ); die;
			} elseif ( 'rejectticket' == $_GET['action'] ) {

				// Add history and udpate status
				$history  = "Rejected by: ";
				$history .= ( ! empty($_POST['message']['name']) ) ? esc_html($_POST['message']['name']) : '';
				$history .= ( ! empty($_POST['message']['email']) ) ? ' &lt;' . esc_html($_POST['message']['email']) . '&gt;' : '';
				if ( ! empty($_POST['message']['body']) ) {
					$history .= '<br><br>Comment:<br><br>' . str_replace( ["\r","\n","\r\n"], '', nl2br( $_POST['message']['body'] ) ) . '<br><br>';
				}
				do_action( 'trac/history/add', get_the_ID(), $history, null, ( ! empty($_POST['message']['name']) ? $_POST['message']['name'] : '' ) );
				wp_set_object_terms( get_the_ID(), 'reject', 'ticket_status', false );


				// Email and update BIC
				$project_id = get_post_meta( get_the_ID(), 'project', true );
				$foreman_ids = get_post_meta( $project_id, 'foreman', true );
				if ( $foreman_ids && ! is_array($foreman_ids) ) {
					$foreman_ids = [ $foreman_ids ];
				}
				$manager_ids = get_post_meta( $project_id, 'manager', true );
				if ( $manager_ids && ! is_array($manager_ids) ) {
					$manager_ids = [ $manager_ids ];
				}

				$headers = [
					'Content-Type: text/html; charset=UTF-8',
					'From: ' . $_POST['message']['name'] . ' <info@tracflo.io>',#trac_option( 'name' )
					'Reply-To: ' . $_POST['message']['name'] . ' <' . ( ! empty( $_POST['message']['email'] ) ? $_POST['message']['email'] : '' ) . '>',
				];

				$contacts = [];
				$pm = [];
				$subject = 'Ticket rejected: ' . get_the_title();#$_POST['message']['subject'];
				$body .= $_POST['message']['body'];
				$body .= 'Submitted by: ' . $current_user->display_name . ', ' . trac_option('company') . "\r\n" . "\r\n" . $body;
				$attachments = '';
				$attach_pdf = ( ! empty($_POST['message']['attach_pdf']) ? $_POST['message']['attach_pdf'] : false );


				if ( $foreman_ids ) {
					foreach ( $foreman_ids as $user_id ) {
						update_post_meta( get_the_ID(), 'bic', $user_id );
						$user_info = get_userdata($user_id);
						$contacts[] = [
							'email' => $user_info->user_email,
							'id'    => $user_info->ID,
							'name'  => $user_info->display_name,
						];
					}
				}

				foreach ( $contacts as $contact ) {
 					$send_body = nl2br( $body );
					wp_mail( $contact['email'], $subject, $send_body, $headers, $attachments );
				}


				if ( $manager_ids ) {
					foreach ( $manager_ids as $user_id ) {
						$user_info = get_userdata($user_id);
						$pm[] = [
							'email' => $user_info->user_email,
							'id'    => $user_info->ID,
							'name'  => $user_info->display_name,
						];
					}
				}

				if ( $pm ) {
					foreach ( $pm as $contact ) {
	 					$send_body = nl2br( $body );
						wp_mail( $contact['email'], $subject, $send_body, $headers, $attachments );
					}
				}



				// Redirect
				$password = get_post_meta( get_the_ID(), 'password', true );
				wp_safe_redirect( add_query_arg( 'action', 'ticketreject', home_url( '/client/ticket/' . $password . '/' ) ) ); die;

			} elseif ( 'reviseticket' == $_GET['action'] ) {

				// Add history and udpate status
				$history  = "Requested revision &amp; resubmit by: ";
				$history .= ( ! empty($_POST['message']['name']) ) ? esc_html($_POST['message']['name']) : '';
				$history .= ( ! empty($_POST['message']['email']) ) ? ' &lt;' . esc_html($_POST['message']['email']) . '&gt;' : '';
				if ( ! empty($_POST['message']['body']) ) {
					$history .= '<br><br>Comment:<br><br>' . str_replace( ["\r","\n","\r\n"], '', nl2br( $_POST['message']['body'] ) ) . '<br><br>';
				}
				do_action( 'trac/history/add', get_the_ID(), $history, null, ( ! empty($_POST['message']['name']) ? $_POST['message']['name'] : '' ) );
				wp_set_object_terms( get_the_ID(), 'revise', 'ticket_status', false );


				// Email and update BIC
				$project_id = get_post_meta( get_the_ID(), 'project', true );
				$foreman_ids = get_post_meta( $project_id, 'foreman', true );
				if ( $foreman_ids && ! is_array($foreman_ids) ) {
					$foreman_ids = [ $foreman_ids ];
				}
				$manager_ids = get_post_meta( $project_id, 'manager', true );
				if ( $manager_ids && ! is_array($manager_ids) ) {
					$manager_ids = [ $manager_ids ];
				}

				$headers = [
					'Content-Type: text/html; charset=UTF-8',
					'From: ' . $_POST['message']['name'] . ' <info@tracflo.io>',#trac_option( 'name' )
					'Reply-To: ' . $_POST['message']['name'] . ' <' . ( ! empty( $_POST['message']['email'] ) ? $_POST['message']['email'] : '' ) . '>',
				];

				$contacts = [];
				$pm = [];
				$subject = 'Ticket requires revision: ' . get_the_title();#$_POST['message']['subject'];
				$body .= $_POST['message']['body'];
				$body .= 'Submitted by: ' . $current_user->display_name . ', ' . trac_option('company') . "\r\n" . "\r\n" . $body;
				$attachments = '';
				$attach_pdf = ( ! empty($_POST['message']['attach_pdf']) ? $_POST['message']['attach_pdf'] : false );


				if ( $foreman_ids ) {
					foreach ( $foreman_ids as $user_id ) {
						update_post_meta( get_the_ID(), 'bic', $user_id );
						$user_info = get_userdata($user_id);
						$contacts[] = [
							'email' => $user_info->user_email,
							'id'    => $user_info->ID,
							'name'  => $user_info->display_name,
						];
					}
				}

				foreach ( $contacts as $contact ) {
 					$send_body = nl2br( $body );
					wp_mail( $contact['email'], $subject, $send_body, $headers, $attachments );
				}


				if ( $manager_ids ) {
					foreach ( $manager_ids as $user_id ) {
						$user_info = get_userdata($user_id);
						$pm[] = [
							'email' => $user_info->user_email,
							'id'    => $user_info->ID,
							'name'  => $user_info->display_name,
						];
					}
				}

				if ( $pm ) {
					foreach ( $pm as $contact ) {
	 					$send_body = nl2br( $body );
						wp_mail( $contact['email'], $subject, $send_body, $headers, $attachments );
					}
				}



				// Redirect
				$password = get_post_meta( get_the_ID(), 'password', true );
				wp_safe_redirect( add_query_arg( 'action', 'ticketrevise', home_url( '/client/ticket/' . $password . '/' ) ) ); die;
			}
		}
	}

	/**
	 * Update the ticket total into meta
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function update_totals( $post_id ) {
		$total = apply_filters( 'trac/breakdown/totals', $post_id );
		update_post_meta( $post_id, 'total', $total );
	}

	/**
	 * Update the ticket meta
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function update_ticket( $post_id ) {
		$this->update_totals( $post_id );
	
		// If attached, update the change order
		if ( has_term( 'attached', 'ticket_status', $post_id ) ) {
			$change_orders = get_posts([
				'post_type'  => 'co',
				'meta_query' => [
					[
						'key'      => 'tickets',
						'value'    => "\"$post_id\"",
						'compare'  => 'LIKE',
					],
				],
			]);
			if ( $change_orders ) {
				foreach ( $change_orders as $change_order ) {
					do_action( 'trac/co/update_totals', $change_order->ID );
					do_action( 'trac/co/site_totals', $change_order->ID );
					do_action( 'trac/project/totals', $change_order->ID );
				}
			}
		}
	}

	/**
	 * Update the ticket when saved
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function save_post( $post_id ) {
		// Add default ticket number (last number plus 1) if none provided
		if ( 'ticket' === get_post_type( $post_id ) ) {
			// Update project id for users with a single project
			if ( current_user_can( 'trac_foreman' ) || current_user_can( 'trac_pm' ) ) {
				if ( ! empty($_POST['acf']['field_58d4c271742df']) && is_string($_POST['acf']['field_58d4c271742df']) ) {
					$projects = get_posts([
						'meta_query' => [
							[
								'key'     => 'foreman',
								'value'   => '"' . get_current_user_id() . '"',
								'compare' => 'LIKE',
							]
						],
						'post_type' => 'project',
						'posts_per_page' => -1,
					]);
					if ( $projects ) {
						update_post_meta( $post_id, 'project', $projects[0]->ID );
					}
				}
			}
			$this->create_title( $post_id );
			$this->create_password( $post_id );
			$this->create_public_id( $post_id );
			$this->update_ticket( $post_id );
			$this->add_bic( $post_id );
		}
	}

	/**
	 * Update attached tickets when co is saved
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function acf_save_post( $post_id ) {
		$this->save_post( $post_id );
	}

	/**
	 * Pre get posts, adjust ticket archives
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function pre_get_posts( $query )
	{
		if ( ! is_admin() && $query->is_main_query() && is_post_type_archive('ticket') ) {
			$query->set( 'posts_per_page', -1 );
			$query->set( 'meta_key', 'project' );

			if ( ! empty($_REQUEST['yid']) ) {
				#$query->set( 'year', esc_sql( $_REQUEST['yid'] ) );
			}
			if ( current_user_can( 'trac_foreman' ) ) {
				$projects = get_posts([
					'meta_query' => [
						[
							'key' => 'foreman',
							'value' => '"' . get_current_user_id() . '"',
							'compare' => 'LIKE',
						]
					],
					'post_type' => 'project',
					'posts_per_page' => -1,
				]);
				if ( $projects ) {
					$ids = [];
					foreach ( $projects as $project ) {
						$ids[] = $project->ID;
					}
					if ( ! empty($_REQUEST['pid']) && in_array($_REQUEST['pid'], $ids) && $post = get_post(esc_sql( $_REQUEST['pid'] )) ) {
						$query->set( 'meta_value_num', esc_sql( $post->ID ) );
					} else {
						$query->set( 'meta_value', $ids );
						$query->set( 'meta_compare', 'IN' );
					}
				}
			}

			$query->set( 'orderby', [ 'meta_value_num' => 'ASC', 'title' => 'DESC' ] );
			return;
		}
	}

	/**
	 * Update the ticket title with number and subject if one exists
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function create_title( $post_id ) {
		global $wpdb;

		$number  = get_post_meta( $post_id, 'number', true );
		$subject = get_post_meta( $post_id, 'subject', true );
		if ( ! $number ) {
			$number = $this->next_number();
			update_post_meta( $post_id, 'number', $number );
		}

		$wpdb->update(
			$wpdb->posts,
			[ 'post_title' => $number . ( $subject ? " - $subject" : '' ) ],
			[ 'ID' => $post_id ],
			[ '%s' ],
			[ '%d' ]
		);
	}

	/**
	 * Update the ticket with a password for public privacy urls.
	 *
	 * @author  Jake Snyder
	 * @since	1.0.7
	 * @return	void
	 */
	public function create_password( $post_id ) {
		$password = get_post_meta( $post_id, 'password', true );
		if ( ! $password ) {
			update_post_meta( $post_id, 'password', random_bytes(32) );
		}
	}

	/**
	 * Update the ticket with a token to securely regulate and track user access to public version
	 *
	 * @author  Jake Snyder
	 * @since	1.0.8
	 * @return	void
	 */
	public function create_token( $post_id, $contact_id ) {
		$key   = "token_{$contact_id}";
		$token = get_post_meta( $post_id, $key, true );
		if ( ! $token ) {
			$token = bin2hex(random_bytes(16));#random_bytes(32);
			update_post_meta( $post_id, $key, $token );#hash('sha256', $token) );
		}
		return $token;#bin2hex($token);
	}

	/**
	 * Get the post id and user id associated with a token from the database
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function get_token_info( $token ) {
		global $wpdb;
		$query  = "SELECT post_id, meta_key FROM $wpdb->postmeta WHERE meta_value = '%s' AND meta_key LIKE 'token_%' LIMIT 1";
		$output = $wpdb->get_row( $wpdb->prepare($query, esc_sql( $token )), ARRAY_A );
		if ( $output ) {
			$output['contact_id'] = str_replace('token_', '', $output['meta_key']);
		}
		return $output;
	}


	/**
	 * Update the ticket with a public password
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function create_public_id( $post_id ) {
		if ( ! ($post_id = get_post_meta( $post_id, 'public_id', true )) ) {
			$public_id = md5( $post_id );
			update_post_meta( $post_id, 'public_id', $public_id );
		}
	}

	/**
	 * When the number field on the form loads, add the next number
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function load_number( $field ) {
		if ( is_page( 'add-ticket' ) ) {
			$next_number = $this->next_number();
			$field['placeholder'] = $next_number;
		}
		return $field;
	}

	/**
	 * Get the next number to use based on previous highest number
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function next_number( $project_id=null ) {
		global $wpdb;
		if ( ! empty($project_id) ) {
			$query = "SELECT m.meta_value FROM $wpdb->posts AS p
				LEFT JOIN $wpdb->postmeta AS m ON p.ID = m.post_id
				LEFT JOIN $wpdb->postmeta AS proj ON p.ID = proj.post_id
				WHERE p.post_type = '{$this->plugin_name}'
				AND p.post_status = 'publish'
				AND m.meta_key = 'number'
				AND proj.meta_key = 'project'
				AND proj.meta_value = " . esc_sql( $project_id ) . "
				ORDER BY CAST(m.meta_value AS SIGNED) DESC
				LIMIT 1";
		} else {
			$query = "SELECT m.meta_value FROM $wpdb->posts AS p
				LEFT JOIN $wpdb->postmeta AS m ON p.ID = m.post_id
				WHERE p.post_type = '{$this->plugin_name}'
				AND p.post_status = 'publish'
				AND m.meta_key = 'number'
				ORDER BY CAST(m.meta_value AS SIGNED) DESC
				LIMIT 1";
		}

		$last_number = $wpdb->get_var($query);
		return apply_filters( "{$this->prefix}/format/last_number", $last_number );
	}

	/**
	 * ajax next number
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function ajax_next_number() {
		$project = null;
		$number = '';

		if ( ! empty($_POST['projectName']) ) {
			$project = get_page_by_title( esc_sql($_POST['projectName']), OBJECT, 'project' );
		} elseif ( ! empty($_POST['projectId']) ) {
			$project = get_post( esc_sql($_POST['projectId']) );
		}
		if ( ! empty($project->ID) ) {
			$number = $this->next_number( $project->ID );
		}

		echo json_encode([
			'number' => $number,
			'projectId' => ( ! empty($project->ID) ? $project->ID : 0 ),
		]);
		die;
	}

	/**
	 * When the number field on the form loads, add the next number
	 *
	 * @author  Jake Snyder
	 * @since	1.0.4
	 * @return	void
	 */
	public function load_type( $field ) {
		if ( 'Labor Type' === $field['label'] ) {
			$field['choices'] = [];
			$choices = get_field( 'labor_types', 'options' );
			if ( is_array($choices) ) {
				foreach ( $choices as $choice ) {
					$field['choices'][ $choice['title'] ] = $choice['title'];
				}
			}
		}
		return $field;
	}

	/**
	 * If only one project, select it
	 *
	 * @author  Jake Snyder
	 * @since	1.0.6
	 * @return	void
	 */
	public function load_project( $field ) {
		if ( ! is_admin() && is_page() && (current_user_can( 'trac_foreman' ) || current_user_can( 'trac_pm' )) ) {
			$projects = get_posts([
				'meta_query' => [
					[
						'key'     => 'foreman',
						'value'   => '"' . get_current_user_id() . '"',
						'compare' => 'LIKE',
					]
				],
				'post_type' => 'project',
				'posts_per_page' => -1,
			]);

			if ( 1 === count($projects) ) {
				$field['type']          = 'text';
				$field['required']      = 0;
				$field['readonly']      = 1;
				$field['default_value'] = $projects[0]->post_title;
				$field['prepend']       = '';
				$field['append']        = '';
				$field['placeholder']   = '';
				$field['maxlength']     = '';
/*
		        {
		            "key": "field_59126dba699af",
		            "label": "Subject",
		            "name": "subject",
		            "type": "text",
		            "instructions": "",
		            "required": 0,
		            "conditional_logic": 0,
		            "wrapper": {
		                "width": "",
		                "class": "",
		                "id": ""
		            },
		            "default_value": "",
		            "placeholder": "",
		            "prepend": "",
		            "append": "",
		            "maxlength": ""
		        },
*/
			}
		}
		return $field;
	}

	/**
	 * Pick today's date for start date picker
	 *
	 * @author  Jake Snyder
	 * @since	1.0.10
	 * @return	void
	 */
	public function default_start_date( $field ) {
		$field['default_value'] = date('Ymd');
		return $field;
	}

	/**
	 * When the project field on the form loads restrict projects to user, adjust query
	 *
	 * @author  Jake Snyder
	 * @since	1.0.4
	 * @return	void
	 */
	public function limit_project_posts( $args, $field, $post ) {
		if ( current_user_can( 'trac_foreman' ) || current_user_can( 'trac_pm' ) ) {
			$projects = get_posts([
				'meta_query' => [
					[
						'key'     => 'foreman',
						'value'   => '"' . get_current_user_id() . '"',
						'compare' => 'LIKE',
					]
				],
				'post_type' => 'project',
				'posts_per_page' => -1,
			]);
/**/
			if ( $projects ) {
				$ids = [];
				foreach ( $projects as $project ) {
					$ids[] = $project->ID;
				}
				$args['post__in'] = $ids;
			}
/**/
		}
		return $args;
	}

	/**
	 * Adding the co
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_ticket_co( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? '<a href="' . esc_url( get_edit_post_link( $value ) ) . '">' . esc_html( get_the_title( $value ) ) . '</a>' : "&mdash;";
	}

	/**
	 * Adding the project
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_ticket_project( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? '<a href="' . esc_url( get_edit_post_link( $value ) ) . '">' . esc_html( get_the_title( $value ) ) . '</a>' : "&mdash;";
	}

	/**
	 * Adding the total
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_ticket_total( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? esc_html( trac_money_format( $value ) ) : "&mdash;";
	}

	/**
	 * Get posts formatted for select field options
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function get_dropdown_posts( $query_args )
	{
		$args = wp_parse_args( $query_args, [
			'post_type'	=> 'post',
			'posts_per_page' => 10,
		]);

		$posts = get_posts( $args );

		$post_options = [];
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$post_options[ $post->ID ] = $post->post_title;
			}
		}
		return $post_options;
	}

	/**
	 * Get the project posts for the dropdown
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function get_project_posts_option()
	{
		return $this->get_dropdown_posts( [
			'post_type' => 'project',
			'posts_per_page' => -1,
		]);
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;
