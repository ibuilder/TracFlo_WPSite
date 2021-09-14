<?php

/**
 * @link              http://www.TracFlo.io/
 * @since             1.0.0
 * @package           TracFlo_Client
 *
 * @wordpress-plugin
 * Plugin Name:       TracFlo Subcontractor: Clients
 * Plugin URI:        http://www.TracFlo.io/
 * Description:       Adds client functionality.
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

$class_name = 'TracFlo_Client';
if ( ! class_exists($class_name) && class_exists('TracFlo_Base') ) :

class TracFlo_Client extends TracFlo_Base
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
			"add-{$this->plugin_name}" => 'Add Client',
		];
		$this->settings['post_types'] = [
			$this->plugin_name    => [
				'labels'              => [
					'name'               => __( 'Clients', $this->plugin_name ),
					'singular_name'      => __( 'Client', $this->plugin_name ),
					'all_items'          => __( 'All Clients', $this->plugin_name ),
					'add_new'            => __( 'Add New', $this->plugin_name ),
					'add_new_item'       => __( 'Add New Client', $this->plugin_name ),
					'edit'               => __( 'Edit', $this->plugin_name  ),
					'edit_item'          => __( 'Edit Clients', $this->plugin_name ),
					'new_item'           => __( 'New Client', $this->plugin_name ),
					'view_item'          => __( 'View Client', $this->plugin_name ),
					'search_items'       => __( 'Search Clients', $this->plugin_name ), 
					'not_found'          => __( 'Nothing found in the Database.', $this->plugin_name ), 
					'not_found_in_trash' => __( 'Nothing found in Trash', $this->plugin_name ),
					'parent_item_colon'  => '',
				],
				'description'         => __( 'Clients.', $this->plugin_name ),
				'menu_icon'           => 'dashicons-universal-access-alt',
				'public'              => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'show_ui'             => true,
				'query_var'           => true,
				'rewrite'	          => [ 'slug' => 'client', 'with_front' => false ],
				'has_archive'         => 'clients',
				'menu_position'       => 51,
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => [ 'title', 'author', 'revisions' ],
				'custom_enter_title'  => __( 'Client Name', $this->plugin_name ),
				'columns'             => [
					#'delete'              => [ 'date', 'author' ],
					'add'                 => [
						'projects'          => 'Projects',
						'contacts'          => 'Contacts',
					],
				],
			],
		];
		$this->settings['taxonomies'] = [
			"{$this->plugin_name}_status" => [
				'post_types'          => [ $this->plugin_name ],
				'hierarchical'        => true,
				'labels'              => [
					'name'                       => _x( 'Client Statuses', 'taxonomy general name' ),
					'singular_name'              => _x( 'Client Status', 'taxonomy singular name' ),
					'search_items'               => __( 'Search Client Statuses', $this->plugin_name ),
					'popular_items'              => __( 'Popular Client Statuses', $this->plugin_name ),
					'all_items'                  => __( 'All Client Statuses', $this->plugin_name ),
					'parent_item'                => null,
					'parent_item_colon'          => null,
					'edit_item'                  => __( 'Edit Client Status', $this->plugin_name ),
					'update_item'                => __( 'Update Client Status', $this->plugin_name ),
					'add_new_item'               => __( 'Add New Client Status', $this->plugin_name ),
					'new_item_name'              => __( 'New Client Status Name', $this->plugin_name ),
					'separate_items_with_commas' => __( 'Separate Client Statuses with commas', $this->plugin_name ),
					'add_or_remove_items'        => __( 'Add or remove Client Statuses', $this->plugin_name ),
					'choose_from_most_used'      => __( 'Choose from the most used Client Statuses', $this->plugin_name ),
					'not_found'                  => __( 'No Client Statuses found.', $this->plugin_name ),
					'menu_name'                  => __( 'Client Statuses', $this->plugin_name ),
				],
				'show_ui'             => true,
				'show_admin_column'   => true,
				'query_var'           => true,
				'rewrite'             => false,#[ 'slug' => 'slicent-status' ],
			],
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
		add_filter( 'custom_column_client_projects',        [ $this, 'column_client_projects' ], 10, 4 );
		add_filter( 'custom_column_client_contacts',        [ $this, 'column_client_contacts' ], 10, 4 );

		// ACF
		add_filter( 'acf/load_field/name=client',           [ $this, 'maybe_add_client' ] );
		add_action( 'pre_get_posts',                        [ $this, 'pre_get_posts' ] );
		add_filter( 'acf/load_field/name=_post_title',      [ $this, 'load_title_field' ] );

		// TracFlo
		add_action( "{$this->prefix}/client/totals",        [ $this, 'update_totals' ] );
		add_action( "{$this->prefix}/site/totals",          [ $this, 'update_site_total_amounts' ] );
	}

	/**
	 * Update project totals when a ticket or co is saved
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function update_totals( $client_id )
	{
		if ( $client_id ) {
			$this->update_total_amounts( $client_id );
			$this->update_site_total_amounts();
		}
	}

	public function update_total_amounts( $post_id )
	{
		$totals = [
			'total'   => 0,
			'paid'    => 0,
			'balance' => 0,
		];

		// Get all projects for this client
		$projects = get_posts([
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'post_type'      => 'project',
			'meta_query'     => [
				[
					'key'          => 'client',
					'value'        => $post_id,
					'compare'      => '=',
				],
			],
			'tax_query', [
				[
					'taxonomy'     => 'project_status',
					'field'        => 'slug',
					'terms'        => [ 'archive' ],
					'operator'     => 'NOT IN',
				],
			],
		]);
		if ( $projects ) : foreach ( $projects as $project ) :
			$subtotal      = (int)get_post_meta( $project->ID, 'total', true );
			$paid_subtotal = (int)get_post_meta( $project->ID, 'paid_total', true );

			$totals['total']   += $subtotal;
			$totals['paid']    += $paid_subtotal;
			$totals['balance'] += ($subtotal - $paid_subtotal);
		endforeach; endif;

		update_post_meta( $post_id, 'total', $totals['total'] );
		update_post_meta( $post_id, 'paid_total', $totals['paid'] );
		update_post_meta( $post_id, 'balance', $totals['balance'] );

		update_post_meta( $post_id, 'totals', $totals );
	}

	public function update_site_total_amounts()
	{
		$totals = [
			'total'   => 0,
			'paid'    => 0,
			'balance' => 0,
		];

		// Get all projects for this client
		$clients = get_posts([
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'post_type'      => 'client',
			'tax_query', [
				[
					'taxonomy'     => 'client_status',
					'field'        => 'slug',
					'terms'        => [ 'archive' ],
					'operator'     => 'NOT IN',
				],
			],
		]);
		if ( $clients ) : foreach ( $clients as $client ) :
			$subtotal      = (int)get_post_meta( $client->ID, 'total', true );
			$paid_subtotal = (int)get_post_meta( $client->ID, 'paid_total', true );

			$totals['total']   += $subtotal;
			$totals['paid']    += $paid_subtotal;
			$totals['balance'] += ($subtotal - $paid_subtotal);
		endforeach; endif;

		update_option( 'trac_total', $totals['total'] );
		update_option( 'trac_paid_total', $totals['paid'] );
		update_option( 'trac_balance', $totals['balance'] );

		update_option( 'trac_totals', $totals );
	}

	/**
	 * Change the post_title field label
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function load_title_field( $field )
	{
		if ( is_page( 'add-client' ) || is_singular( 'client' ) ) {
			$field['label'] = 'Client Name';
		}
		return $field;
	}

	/**
	 * Adjust archive list
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function pre_get_posts( $query )
	{
		if ( ! is_admin() && $query->is_main_query() ) {
			if ( is_post_type_archive('client') ) {
				$query->set( 'orderby', [ 'title' => 'ASC' ] );
			}
			return;
		}
	}

	/**
	 * Autofill client dropdown when a client is set
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function maybe_add_client( $field )
	{
		if ( ! empty($_REQUEST['cid']) ) {
			$field['value'] = $_REQUEST['cid'];
		}
		return $field;
	}

	/**
	 * Adding the contacts
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_client_contacts( $value, $post_id, $name, $post_type )
	{
		$output = '';
		$items = get_posts([
			'posts_per_page' => -1,
			'post_type' => 'contact',
			'meta_query' => [
				[
					'key'      => 'client',
					'value'    => $post_id,
					'compare'  => '=',
				],
			],
		]);
		if ( $items ) {
			foreach ( $items as $item ) {
				$output .= '<a href="' . esc_url( get_edit_post_link( $item->ID ) ) . '">' . esc_html( $item->post_title ) . '</a><br>';
			}
		}
		return ( $output ) ? rtrim($output, '<br>') : "&mdash;";
	}

	/**
	 * Adding the projects
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_client_projects( $value, $post_id, $name, $post_type )
	{
		$output = '';
		$items = get_posts([
			'posts_per_page' => -1,
			'post_type' => 'project',
			'meta_query' => [
				[
					'key'      => 'client',
					'value'    => $post_id,
					'compare'  => '=',
				],
			],
		]);
		if ( $items ) {
			foreach ( $items as $item ) {
				$output .= '<a href="' . esc_url( get_edit_post_link( $item->ID ) ) . '">' . esc_html( $item->post_title ) . '</a><br>';
			}
		}
		return ( $output ) ? rtrim($output, '<br>') : "&mdash;";
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;
