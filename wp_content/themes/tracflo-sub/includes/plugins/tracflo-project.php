<?php

/**
 * @link              http://www.TracFlo.io/
 * @since             1.0.0
 * @package           TracFlo_Project
 *
 * @wordpress-plugin
 * Plugin Name:       TracFlo Subcontractor: Projects
 * Plugin URI:        http://www.TracFlo.io/
 * Description:       Adds project functionality.
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

$class_name = 'TracFlo_Project';
if ( ! class_exists($class_name) && class_exists('TracFlo_Base') ) :

class TracFlo_Project extends TracFlo_Base
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
			"add-{$this->plugin_name}" => 'Add Project',
		];
		$this->settings['post_types'] = [
			$this->plugin_name    => [
				'labels'              => [
					'name'               => __( 'Projects', $this->plugin_name ),
					'singular_name'      => __( 'Project', $this->plugin_name ),
					'all_items'          => __( 'All Projects', $this->plugin_name ),
					'add_new'            => __( 'Add New', $this->plugin_name ),
					'add_new_item'       => __( 'Add New Project', $this->plugin_name ),
					'edit'               => __( 'Edit', $this->plugin_name  ),
					'edit_item'          => __( 'Edit Projects', $this->plugin_name ),
					'new_item'           => __( 'New Project', $this->plugin_name ),
					'view_item'          => __( 'View Project', $this->plugin_name ),
					'search_items'       => __( 'Search Projects', $this->plugin_name ), 
					'not_found'          => __( 'Nothing found in the Database.', $this->plugin_name ), 
					'not_found_in_trash' => __( 'Nothing found in Trash', $this->plugin_name ),
					'parent_item_colon'  => '',
				],
				'description'         => __( 'Projects.', $this->plugin_name ),
				'menu_icon'           => 'dashicons-networking',
				'public'              => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'show_ui'             => true,
				'query_var'           => true,
				'rewrite'	          => [ 'slug' => 'project', 'with_front' => false ],
				'has_archive'         => 'projects',
				'menu_position'       => 52,
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => [ 'title', 'author', 'revisions' ],
				'custom_enter_title'  => __( 'Project Name', $this->plugin_name ),
				'columns'             => [
					#'delete'              => [ 'date', 'author' ],
					'add'                 => [
						'number'          => 'Project #',
						'client'          => 'Client',
						'start_date'      => 'Start Date',
						'totals_co'       => 'COs',
						'totals_ticket'   => 'Tickets',
						'totals'          => 'Balance',
						'uploads'         => 'Uploads',
					],
				],
			],
		];
		$this->settings['taxonomies'] = [
			"{$this->plugin_name}_status" => [
				'post_types'          => [ $this->plugin_name ],
				'hierarchical'        => true,
				'labels'              => [
					'name'                       => _x( 'Project Statuses', 'taxonomy general name' ),
					'singular_name'              => _x( 'Project Status', 'taxonomy singular name' ),
					'search_items'               => __( 'Search Project Statuses', $this->plugin_name ),
					'popular_items'              => __( 'Popular Project Statuses', $this->plugin_name ),
					'all_items'                  => __( 'All Project Statuses', $this->plugin_name ),
					'parent_item'                => null,
					'parent_item_colon'          => null,
					'edit_item'                  => __( 'Edit Project Status', $this->plugin_name ),
					'update_item'                => __( 'Update Project Status', $this->plugin_name ),
					'add_new_item'               => __( 'Add New Project Status', $this->plugin_name ),
					'new_item_name'              => __( 'New Project Status Name', $this->plugin_name ),
					'separate_items_with_commas' => __( 'Separate Project Statuses with commas', $this->plugin_name ),
					'add_or_remove_items'        => __( 'Add or remove Project Statuses', $this->plugin_name ),
					'choose_from_most_used'      => __( 'Choose from the most used Project Statuses', $this->plugin_name ),
					'not_found'                  => __( 'No Project Statuses found.', $this->plugin_name ),
					'menu_name'                  => __( 'Project Statuses', $this->plugin_name ),
				],
				'show_ui'             => true,
				'show_admin_column'   => true,
				'query_var'           => true,
				'rewrite'             => false,#[ 'slug' => 'project-status' ],
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
		add_filter( 'custom_column_project_client',         [ $this, 'column_project_client' ], 10, 4 );
		add_filter( 'custom_column_project_number',         [ $this, 'column_project_number' ], 10, 4 );
		add_filter( 'custom_column_project_start_date',     [ $this, 'column_project_start_date' ], 10, 4 );
		add_filter( 'custom_column_project_totals',         [ $this, 'column_project_totals' ], 10, 4 );
		add_filter( 'custom_column_project_totals_co',      [ $this, 'column_project_totals_co' ], 10, 4 );
		add_filter( 'custom_column_project_totals_ticket',  [ $this, 'column_project_totals_ticket' ], 10, 4 );
		add_filter( 'custom_column_project_uploads',        [ $this, 'column_project_uploads' ], 10, 4 );

		// Saving & Loading Posts
		add_action( 'pre_get_posts',                        [ $this, 'pre_get_posts' ] );
		add_action( 'save_post',                            [ $this, 'save_post' ], 20 );

		// ACF
		add_action( 'acf/save_post',                        [ $this, 'acf_save_post' ], 20 );
		add_filter( 'acf/load_field/name=_post_title',      [ $this, 'load_title_field' ] );
		add_filter( 'acf/load_field/name=number',           [ $this, 'load_number' ] );

		// TracFlo
		add_action( "{$this->prefix}/project/totals",       [ $this, 'update_totals' ] );
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

		if ( ! empty($_GET['action']) && is_singular( 'project' ) ) {

			if ( 'restore' == $_GET['action'] ) {
				wp_remove_object_terms( get_the_ID(), 'archive', 'project_status' );
				wp_safe_redirect( home_url( '/projects/' ) ); die;

			} elseif ( 'archive' == $_GET['action'] ) {
				wp_set_object_terms( get_the_ID(), 'archive', 'project_status', false );
				wp_safe_redirect( home_url( '/projects/' ) ); die;

			} elseif ( 'delete' === $_GET['action'] && ! empty($_GET['confirm']) && wp_verify_nonce( $_GET['confirm'], 'delete_project_' . get_the_ID() ) ) {
				// Trash the post
				$client_id = get_post_meta( get_the_ID(), 'client', true );
				wp_trash_post( get_the_ID() );
				do_action( 'trac/co/site_totals' );
				do_action( 'trac/client/totals', $client_id );
				wp_safe_redirect( home_url( '/projects/' ) ); die;
			}

		}

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
			if ( is_post_type_archive('project') ) {
				$filter = null;
				if ( empty($_GET['filter']) || 'archive' != $_GET['filter'] ) {
					$filter = 'archive';
				}
				$query->set( 'tax_query', [
					[
						'taxonomy' => 'project_status',
						'field'    => 'slug',
						'terms'    => [ 'archive' ],
						'operator' => ($filter ? 'NOT IN' : 'IN'),
					],
				]);
				// Filter out or in archive
				$query->set( 'orderby', [ 'title' => 'ASC' ] );
				$query->set( 'posts_per_page', -1 );
			}
			return;
		}
	}

	/**
	 * WP Save, create a default project number
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function save_post( $post_id ) {
		if ( $this->plugin_name === get_post_type( $post_id ) ) {
			$this->create_number( $post_id );
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
	 * ACF Save, create a default project number
	 *
	 * @author  Jake Snyder
	 * @since	1.0.4
	 * @return	void
	 */
	public function acf_save_post( $post_id ) {
		$this->save_post( $post_id );
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
		if ( is_page( 'add-project' ) || is_singular( 'project' ) ) {
			$field['label'] = 'Project Name';
			$field['wrapper']['width'] = '50';
		}
		return $field;
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
		if ( is_page( 'add-project' ) ) {
			$next_number = $this->next_number();
			$field['placeholder'] = $next_number;
		}
		return $field;
	}

	/**
	 * Update project totals when a ticket or co is saved
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function update_totals( $post_id )
	{
		$project_id = get_post_meta( $post_id, 'project', true );
		if ( $project_id ) {
			$this->update_total_items( $project_id );
			$this->update_total_amounts( $project_id );
			$client_id = get_post_meta( $project_id, 'client', true );
			do_action( 'trac/client/totals', $client_id );
		}
	}

	public function update_total_amounts( $post_id )
	{
		$totals = [
			'total' => 0,
			'paid' => 0,
			'balance' => 0,
		];

		// Get all change orders for this project
		$cos = get_posts([
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'post_type' => 'co',
			'meta_query' => [
				[
					'key'     => 'project',
					'value'   => $post_id,
					'compare' => '=',
				],
			],
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

		update_post_meta( $post_id, 'total', $totals['total'] );
		update_post_meta( $post_id, 'paid_total', $totals['paid'] );
		update_post_meta( $post_id, 'balance', $totals['balance'] );

		update_post_meta( $post_id, 'totals', $totals );
	}

	public function update_total_items( $post_id )
	{
		global $wpdb;

		$query = "SELECT COUNT(*) FROM $wpdb->posts p 
			LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id 
			WHERE p.post_type = 'ticket' 
			AND p.post_status = 'publish' 
			AND pm.meta_key = 'project' 
			AND pm.meta_value = %d";
		$total_tickets = $wpdb->get_var( $wpdb->prepare( $query, $post_id ) );
		update_post_meta( $post_id, 'totals_ticket', $total_tickets );

		$query = "SELECT COUNT(*) FROM $wpdb->posts p 
			LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id 
			WHERE p.post_type = 'co' 
			AND p.post_status = 'publish' 
			AND pm.meta_key = 'project' 
			AND pm.meta_value = %d";
		$total_cos = $wpdb->get_var( $wpdb->prepare( $query, $post_id ) );
		update_post_meta( $post_id, 'totals_co', $total_cos );
	}

	/**
	 * Adding the client
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_project_client( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? '<a href="' . esc_url( get_edit_post_link( $value ) ) . '">' . esc_html( get_the_title( $value ) ) . '</a>' : "&mdash;";
	}

	/**
	 * Adding the project number
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_project_number( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? esc_html( $value ) : "&mdash;";
	}

	/**
	 * Adding the start date
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_project_start_date( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? esc_html( date_i18n( 'm/d/Y', strtotime($value) ) ) : "&mdash;";
	}

	/**
	 * Adding the total
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_project_totals( $value, $post_id, $name, $post_type )
	{
		$balance = ( ! empty($value['balance']) ) ? $value['balance'] : 0;
		return esc_html( trac_money_format( $balance ) );
	}

	/**
	 * Adding the total change orders
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_project_totals_co( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? esc_html( $value ) : "0";
	}

	/**
	 * Adding the total tickets
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_project_totals_ticket( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? esc_html( $value ) : "0";
	}

	/**
	 * Adding the uploads column
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_project_uploads( $value, $post_id, $name, $post_type )
	{
		$uploads = get_field( 'uploads', $post_id );
		return ( $uploads ) ? 'Yes' : 'No';
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;
