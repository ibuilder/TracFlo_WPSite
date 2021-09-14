<?php

/**
 * @link              http://www.TracFlo.io/
 * @since             1.0.0
 * @package           TracFlo_Co
 *
 * @wordpress-plugin
 * Plugin Name:       TracFlo Subcontractor: Change Orders
 * Plugin URI:        http://www.TracFlo.io/
 * Description:       Adds change order functionality.
 * Version:           1.0.5
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

$class_name = 'TracFlo_Co';
if ( ! class_exists($class_name) && class_exists('TracFlo_Base') ) :

class TracFlo_Co extends TracFlo_Base
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
			"add-{$this->plugin_name}" => 'Add Change Order',
		];
		$this->settings['post_types'] = [
			'co'    => [
				'labels'              => [
					'name'               => __( 'Change Orders', $this->plugin_name ),
					'singular_name'      => __( 'Change Order', $this->plugin_name ),
					'all_items'          => __( 'All Change Orders', $this->plugin_name ),
					'add_new'            => __( 'Add New', $this->plugin_name ),
					'add_new_item'       => __( 'Add New Change Order', $this->plugin_name ),
					'edit'               => __( 'Edit', $this->plugin_name  ),
					'edit_item'          => __( 'Edit Change Orders', $this->plugin_name ),
					'new_item'           => __( 'New Change Order', $this->plugin_name ),
					'view_item'          => __( 'View Change Order', $this->plugin_name ),
					'search_items'       => __( 'Search Change Orders', $this->plugin_name ), 
					'not_found'          => __( 'Nothing found in the Database.', $this->plugin_name ), 
					'not_found_in_trash' => __( 'Nothing found in Trash', $this->plugin_name ),
					'parent_item_colon'  => '',
				],
				'description'         => __( 'Change Orders.', $this->plugin_name ),
				'menu_icon'           => 'dashicons-list-view',
				'public'              => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'show_ui'             => true,
				'query_var'           => true,
				'rewrite'	          => [ 'slug' => 'co', 'with_front' => false ],
				'has_archive'         => 'cos',
				'menu_position'       => 56,
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => [ 'title', 'author', 'revisions' ],
				'custom_enter_title'  => __( 'Change Order #', $this->plugin_name ),
				'columns'             => [
					#'delete'              => [ 'date', 'author' ],
					'add'                 => [
						'project'     => 'Project',
						'total'       => 'Total',
						'paid_total'  => 'Paid',
						'balance'     => 'Balance',
					],
				],
			],
		];
		$this->settings['taxonomies'] = [
			"{$this->plugin_name}_status" => [
				'post_types'          => [ $this->plugin_name ],
				'hierarchical'        => true,
				'labels'              => [
					'name'                       => _x( 'Change Order Statuses', 'taxonomy general name' ),
					'singular_name'              => _x( 'Change Order Status', 'taxonomy singular name' ),
					'search_items'               => __( 'Search Change Order Statuses', $this->plugin_name ),
					'popular_items'              => __( 'Popular Change Order Statuses', $this->plugin_name ),
					'all_items'                  => __( 'All Change Order Statuses', $this->plugin_name ),
					'parent_item'                => null,
					'parent_item_colon'          => null,
					'edit_item'                  => __( 'Edit Change Order Status', $this->plugin_name ),
					'update_item'                => __( 'Update Change Order Status', $this->plugin_name ),
					'add_new_item'               => __( 'Add New Change Order Status', $this->plugin_name ),
					'new_item_name'              => __( 'New Change Order Status Name', $this->plugin_name ),
					'separate_items_with_commas' => __( 'Separate Change Order Statuses with commas', $this->plugin_name ),
					'add_or_remove_items'        => __( 'Add or remove Change Order Statuses', $this->plugin_name ),
					'choose_from_most_used'      => __( 'Choose from the most used Change Order Statuses', $this->plugin_name ),
					'not_found'                  => __( 'No Change Order Statuses found.', $this->plugin_name ),
					'menu_name'                  => __( 'Change Order Statuses', $this->plugin_name ),
				],
				'show_ui'             => true,
				'show_admin_column'   => true,
				'query_var'           => true,
				'rewrite'             => [ 'slug' => 'co_status' ],
			],
		];
		$this->settings['notifications'][] = [
			'key'     => 'sent',
			'value'   => 'co',
			'message' => __( "Change Order sent.", 'tracflo' ),
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
		// WP Admin
		add_filter( 'custom_column_co_balance',             [ $this, 'column_co_balance' ], 10, 4 );
		add_filter( 'custom_column_co_paid_total',          [ $this, 'column_co_paid_total' ], 10, 4 );
		add_filter( 'custom_column_co_project',             [ $this, 'column_co_project' ], 10, 4 );
		add_filter( 'custom_column_co_total',               [ $this, 'column_co_total' ], 10, 4 );

		// Saving & Loading Posts
		add_action( 'pre_get_posts',                        [ $this, 'pre_get_posts' ] );
		add_action( 'save_post',                            [ $this, 'save_post' ], 20 );

		// ACF
		add_action( 'acf/save_post',                        [ $this, 'acf_save_post' ], 20 );
		add_filter( 'acf/fields/relationship/result/name=tickets', [ $this, 'add_project_to_ticket_picker' ], 10, 4 );
		add_filter( 'acf/fields/relationship/query/name=tickets',  [ $this, 'restrict_ticket_picker' ], 10, 3 );
		add_filter( 'acf/load_field/name=number',           [ $this, 'load_number' ] );

		// TracFlo
		add_action( "{$this->prefix}/co/update",            [ $this, 'update_co' ] );
		add_action( "{$this->prefix}/co/update_totals",     [ $this, 'update_totals' ] );
		add_action( "{$this->prefix}/co/site_totals",       [ $this, 'site_totals' ] );
		add_action( "{$this->prefix}/co/payment_info",      [ $this, 'payment_info' ] );
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

		if ( ! empty($_GET['action']) && is_singular( $this->plugin_name ) ) {

			if ( 'addpayment' == $_GET['action'] ) {
				if ( ! empty($_POST['payment']) && is_array($_POST['payment']) && ! empty($_POST['payment']['amount']) && ! empty($_POST['payment']['paid_at']) ) {
					$amount = preg_replace("/[^0-9.-]/", '', $_POST['payment']['amount'] );
					$amount = floatval( $amount );
					$attachment_id = '';

					if ( ! empty($_FILES['payment_file']) && ! $_FILES['payment_file']['error'] ) {
						$attachment_id  = trac_upload_po( $_FILES['payment_file']['tmp_name'], $_FILES['payment_file']['name'] );
					}

					$data = [
						'amount'    => esc_sql( $amount ),
						'close'     => ( ! empty($_POST['payment']['close']) ? 1 : 0 ),
						'date'      => esc_sql( current_time('timestamp') ),
						'date_paid' => esc_sql( strtotime($_POST['payment']['paid_at']) ),
						'file'      => $attachment_id,
						'notes'     => ( ! empty($_POST['payment']['notes']) ? esc_sql($_POST['payment']['notes']) : '' ),
						'po'        => ( ! empty($_POST['payment']['po']) ? esc_sql($_POST['payment']['po']) : '' ),
						'user'      => get_current_user_id(),
					];

					if ( ! empty($_POST['payment']['close']) ) {
						#update_post_meta( get_the_ID(), 'closed', true );
						wp_set_object_terms( get_the_ID(), 'closed', 'co_status', false );
						do_action( 'trac/history/add', get_the_ID(), 'closed' );
					}

					add_post_meta( get_the_ID(), 'payment', $data );
					do_action( 'trac/co/update', get_the_ID() );
					wp_safe_redirect( get_permalink() ); die;
				}

			} elseif ( 'close' == $_GET['action'] ) {
				#update_post_meta( get_the_ID(), 'closed', true );
				wp_set_object_terms( get_the_ID(), 'closed', 'co_status', false );
				do_action( 'trac/history/add', get_the_ID(), 'closed' );
				wp_safe_redirect( get_permalink() ); die;

			} elseif ( 'complete' == $_GET['action'] ) {
				#if ( has_term( 'complete', 'co_status', get_the_ID() ) ) {
				#	wp_remove_object_terms( get_the_ID(), 'complete', 'co_status' );
				#	do_action( 'trac/history/add', get_the_ID(), 'uncomplete' );
				#} else {
					wp_set_object_terms( get_the_ID(), 'complete', 'co_status', false );
					do_action( 'trac/history/add', get_the_ID(), 'complete' );
				#}
				wp_safe_redirect( get_permalink() ); die;

			} elseif ( 'deletecomplete' == $_GET['action'] && ! empty($_GET['id']) ) {
				// Remove the term
				wp_remove_object_terms( get_the_ID(), 'complete', 'co_status' );
				// Remove the history.
				$wpdb->delete( $wpdb->postmeta, [ 'meta_id' => esc_sql( $_GET['id'] ) ], [ '%d' ] );
				wp_safe_redirect( get_permalink() ); die;

			} elseif ( 'open' == $_GET['action'] ) {
				#update_post_meta( get_the_ID(), 'closed', false );
				wp_remove_object_terms( get_the_ID(), 'closed', 'co_status' );
				do_action( 'trac/history/add', get_the_ID(), 'opened' );
				wp_safe_redirect( get_permalink() ); die;

			} elseif ( 'deletepayment' == $_GET['action'] && ! empty($_GET['id']) ) {
				// Get the payment to store amount in delete history note
				$payment = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_id = %d AND post_id = %d", esc_sql($_GET['id']), get_the_ID() ) );
				if ( $payment ) {
					$payment = maybe_unserialize( $payment );
					// Delete the history
					$wpdb->delete( $wpdb->postmeta, [ 'meta_id' => esc_sql( $_GET['id'] ) ], [ '%d' ] );
					// Note the deleted history into new history
					do_action( 'trac/history/add', get_the_ID(), 'deletepayment', $payment['amount'] );
					// Update the totals
					do_action( 'trac/co/update', get_the_ID() );
				}
				wp_safe_redirect( get_permalink() ); die;
			} elseif ( 'delete' === $_GET['action'] && ! empty($_GET['confirm']) && wp_verify_nonce( $_GET['confirm'], 'delete_co_' . get_the_ID() ) ) {
				// Detach any tickets
				$tickets = get_post_meta( get_the_ID(), 'tickets', true );
				if ( $tickets ) {
					foreach ( $tickets as $ticket_id ) {
						wp_remove_object_terms( $ticket_id, 'attached', 'ticket_status' );
					}
				}
				// Trash the post
				wp_trash_post( get_the_ID() );
				do_action( 'trac/co/site_totals' );
				do_action( 'trac/project/totals', get_the_ID() );
				wp_safe_redirect( home_url( '/cos/' ) ); die;
			}

		}

	}

	/**
	 * Restrict the ticket to unattached and by project when project is available
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function restrict_ticket_picker( $args, $field, $post_id )
	{
		if ( $post_id && 'new_post' !== $post_id ) {
			$project_id = get_post_meta( $post_id, 'project', true );
			$args['meta_query'] = [
				[
					'key'     => 'project',
					'value'   => $project_id,
					'compare' => '=',
				],
			];
		}
		$args['tax_query'] = [
			[
				'taxonomy' => 'ticket_status',
				'field'    => 'slug',
				'terms'    => [ 'attached' ],
				'operator' => 'NOT IN',
			],
		];
		return $args;
	}

	/**
	 * Add the project to a create do form ticket picker. Since a
	 * project hasn't been selected when the form loads, this will
	 * give show the project next to the ticket number.
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function add_project_to_ticket_picker( $title, $post, $field, $post_id )
	{
		if ( ! $post_id || 'new_post' === $post_id ) {
			$project_id = get_post_meta( $post->ID, 'project', true );
			$title .= ' - ' . get_the_title( $project_id );
		}
		return $title;
	}

	/**
	 * Load next number into number field
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function load_number( $field )
	{
		if ( is_page( 'add-co' ) ) {
			$next_number = $this->next_number();
			$field['placeholder'] = $next_number;
		}
		return $field;
	}

	/**
	 * Sort items by a date col in array
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function sort_by_date( $items )
	{
		usort( $items, 'trac_order_items_by_date' );
		return $items;
	}

	/**
	 * Add last payment info to CO
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function payment_info( $post_id )
	{
		$payments = get_post_meta( $post_id, 'payment', false );
		$payments = apply_filters( 'trac/history/sort', $payments );
		$payment = end($payments);
		if ( $payment ) {
			update_post_meta( $post_id, 'paid_date', ( ! empty($payment['date_paid']) ? $payment['date_paid'] : '' ) );
			update_post_meta( $post_id, 'paid_po', ( ! empty($payment['po']) ? $payment['po'] : '' ) );
			update_post_meta( $post_id, 'paid_po_file', ( ! empty($payment['file']) ? $payment['file'] : '' ) );
		}
	}

	/**
	 * Update totals for CO
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function update_totals( $post_id )
	{
		$total      = $this->get_total( $post_id );
		$paid_total = $this->paid_total( $post_id );
		$balance    = $total - $paid_total;
		update_post_meta( $post_id, 'total', $total );
		update_post_meta( $post_id, 'paid_total', $paid_total );
		update_post_meta( $post_id, 'balance', $balance );
	}

	/**
	 * Get the CO total
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function get_total( $post_id )
	{
		$total = 0;
		if ( 'total' == get_field( 'type', $post_id ) ) {
			$total = get_post_meta( $post_id, 'manual_total', true );
		} elseif ( 'sum' == get_field( 'type', $post_id ) ) {
			$total = apply_filters( 'trac/breakdown/totals', $post_id );
		} elseif ( $tickets = get_field( 'tickets', $post_id ) ) {
			foreach ( $tickets as $ticket ) {
				$total += apply_filters( 'trac/breakdown/totals', $ticket->ID );
			}
		}
		return $total;
	}

	/**
	 * Get total amount paid
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function paid_total( $post_id )
	{
		$total = 0;
		if ( is_numeric($post_id) ) {
			$payments = get_post_meta( $post_id, 'payment', false );
		} elseif ( is_array($post_id) ) {
			$payments = $post_id;
		} else {
			return $total;
		}

		if ( is_array($payments) ) {
			foreach ( $payments as $payment ) {
				$total += ! empty($payment['amount']) ? $payment['amount'] : 0;
			}
		}
		return $total;
	}

	/**
	 * Update totals for all change orders when an item is saved
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function site_totals( $post_id )
	{
		$totals = [
			'total'   => 0,
			'paid'    => 0,
			'balance' => 0,
		];

		$cos = get_posts([
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'post_type'      => 'co',
		]);
		if ( $cos ) : foreach ( $cos as $co ) :
			$subtotal      = get_post_meta( $co->ID, 'total', true );
			$paid_subtotal = get_post_meta( $co->ID, 'paid_total', true );
			$balance       = $subtotal - $paid_subtotal;
			$closed        = has_term( 'closed', 'co_status', $co->ID );

			$totals['total'] += $subtotal;
			$totals['paid'] += $paid_subtotal;
			if ( ! $closed ) {
				$totals['balance'] += $balance;
			}
		endforeach; endif;

		update_option( 'co_totals', $totals );
	}
	
	/**
	 * Update the ticket meta
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function update_co( $post_id ) {
		// payment info
		do_action( 'trac/co/payment_info', $post_id );
		// totals
		do_action( 'trac/co/update_totals', $post_id );
		do_action( 'trac/co/site_totals' );
		do_action( 'trac/project/totals', $post_id );
	}

	/**
	 * Update the ticket when saved
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function save_post( $post_id ) {
		if ( $this->plugin_name === get_post_type( $post_id ) ) {
			// Add history
			do_action( 'trac/history/add', $post_id );
			// Create a title
			$this->create_number( $post_id );
			$this->create_title( $post_id );
			// Update info
			do_action( 'trac/co/update', $post_id );
		}
	}

	/**
	 * Create a number
	 *
	 * @author  Jake Snyder
	 * @since	1.0.4
	 * @return	void
	 */
	public function create_number( $post_id ) {
		$number  = get_post_meta( $post_id, 'number', true );
		if ( ! $number ) {
			$number = $this->next_number();
			update_post_meta( $post_id, 'number', $number );
		}
	}

	/**
	 * Create a title
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function create_title( $post_id ) {
		global $wpdb;

		$number  = get_post_meta( $post_id, 'number', true );
		$subject = get_post_meta( $post_id, 'subject', true );
		$wpdb->update(
			$wpdb->posts,
			[ 'post_title' => $number . ( $subject ? " - $subject" : '' ) ],
			[ 'ID' => $post_id ],
			[ '%s' ],
			[ '%d' ]
		);
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

		// bail early if no ACF data
		if ( empty($_POST['acf']) ) { return; }
	
		// Add default change order number (last number plus 1) if none provided
		if ( $this->plugin_name === get_post_type( $post_id ) ) {
			// Update tickets to attached
			if ( ! empty($_POST['acf']['field_58d52b4483165']) ) {
				foreach ( $_POST['acf']['field_58d52b4483165'] as $ticket_id ) {
					update_post_meta( $ticket_id, 'co', $post_id );
					wp_set_object_terms( $ticket_id, 'attached', 'ticket_status', false );
				}
			}
		}
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
		if ( ! is_admin() && $query->is_main_query() && is_post_type_archive('co') ) {
			/* Only TopRock * /
			if ( 2 === get_current_blog_id() ) {
				if ( ! empty($_COOKIE['year']) ) {
					$query->set( 'year', $_COOKIE['year'] );
				}
				if ( ! empty($_COOKIE['project']) && $post = get_post($_COOKIE['project']) ) {
					$query->set( 'meta_key', 'project' );
					$query->set( 'meta_value', esc_sql( $post->ID ) );
				}
			}
			/**/

			if ( ! empty($_GET['pid']) && 'reset' !== $_GET['pid'] ) {
				$current_project = $_GET['pid'];
				$query->set( 'meta_key', 'project' );
				$query->set( 'meta_value', esc_sql( $current_project ) );
			}

			if ( ! empty($_GET['yid']) && 'reset' !== $_GET['yid'] ) {
				$current_year = $_GET['yid'];
				$query->set( 'year', esc_sql( $current_year ) );
			}


			if ( empty($_GET['tab']) || 'open' === $_GET['tab'] ) {
				$query->set( 'posts_per_page', -1 );
				$query->set( 'tax_query', [
					[
						'taxonomy' => 'co_status',
						'field'    => 'slug',
						'terms'    => [ 'closed' ],
						'operator' => 'NOT IN',
					],
				]);
			} elseif ( ! empty($_GET['tab']) ) {
				$query->set( 'posts_per_page', -1 );

				if ( 'paid' === $_GET['tab'] ) {
					$query->set( 'tax_query', [
						[
							'taxonomy' => 'co_status',
							'field'    => 'slug',
							'terms'    => [ 'closed' ],
							'operator' => 'IN',
						],
					]);
				} elseif ( 'po' === $_GET['tab'] ) {
					$query->set( 'meta_query', [
						[
							'key'     => 'paid_po',
							'value'   => '',
							'compare' => '!=',
						],
					]);
				} elseif ( 'complete' === $_GET['tab'] ) {
					$query->set( 'tax_query', [
						[
							'taxonomy' => 'co_status',
							'field'    => 'slug',
							'terms'    => [ 'complete' ],
							'operator' => 'IN',
						],
					]);
					if ( ! empty($_GET['filter']) && 'nopo' === $_GET['filter'] ) {
						$query->set( 'meta_query', [
							[
								'key'     => 'paid_po',
								'value'   => '',
								'compare' => '=',
							],
						]);
					}
				}
			}
			$query->set( 'orderby', [ 'date' => 'DESC' ] );
			return;
		}
	}

	/**
	 * Adding the balance
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_co_balance( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? esc_html( trac_money_format( $value ) ) : "&mdash;";
	}

	/**
	 * Adding the paid
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_co_paid_total( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? esc_html( trac_money_format( $value ) ) : "&mdash;";
	}

	/**
	 * Adding the project
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_co_project( $value, $post_id, $name, $post_type )
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
	public function column_co_total( $value, $post_id, $name, $post_type )
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


if ( class_exists('WP_CLI') ) {

	class Trac_Cli extends WP_CLI_Command
	{
		public function pdfupdatepost( $post_id ) {
			if ( $file = trac_print_pdf( $post_id ) ) {
				WP_CLI::success( "PDF created for: $post_id at $file" );
			} else {
				WP_CLI::error( "PDF could not be created: $post_id !" );
			}
		}

		public function pdfupdate( $arg = [] ) {
			// Update the PDF for a single post
			if ( is_numeric($arg[0]) ) { 
				$this->pdfupdatepost( $arg[0] );
				return;
			// Update PDF post_type (CO or TICKET)
			} elseif ( is_string($arg[0]) ) {
				$query = new WP_Query([
					'post_type'      => $arg[0],
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				]);
				while ( $query->have_posts() ) {
					$query->the_post();
					$this->pdfupdatepost( get_the_ID() );
				}
				return;
			// Update PDF for all tickets and change orders
			} else {
				$query = new WP_Query([
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'post_type'      => ['co','ticket'],
				]);
				while ( $query->have_posts() ) {
					$query->the_post();
					$this->pdfupdatepost( get_the_ID() );
				}
				return;
			}
		}

		public function totals( $arg = [] ) {
			if ( 'update' == $arg[0] ) {
				$cos = get_posts([
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'post_type'      => 'co',
				]);
				if ( $cos ) : foreach ( $cos as $co ) :
					do_action( 'trac/co/update', $co->ID );
				endforeach; endif;
			}
		}
	}

	WP_CLI::add_command( 'trac', 'Trac_Cli' );

}

endif;
