<?php

/**
 * @link              http://www.TracFlo.io/
 * @since             1.0.0
 * @package           TracFlo_Contact
 *
 * @wordpress-plugin
 * Plugin Name:       TracFlo Subcontractor: Contacts
 * Plugin URI:        http://www.TracFlo.io/
 * Description:       Adds contact functionality.
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

$class_name = 'TracFlo_Contact';
if ( ! class_exists($class_name) && class_exists('TracFlo_Base') ) :

class TracFlo_Contact extends TracFlo_Base
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
			"add-{$this->plugin_name}" => 'Add Contact',
		];
		$this->settings['post_types'] = [
			$this->plugin_name    => [
				'labels'              => [
					'name'               => __( 'Contacts', $this->plugin_name ),
					'singular_name'      => __( 'Contact', $this->plugin_name ),
					'all_items'          => __( 'All Contacts', $this->plugin_name ),
					'add_new'            => __( 'Add New', $this->plugin_name ),
					'add_new_item'       => __( 'Add New Contact', $this->plugin_name ),
					'edit'               => __( 'Edit', $this->plugin_name  ),
					'edit_item'          => __( 'Edit Contacts', $this->plugin_name ),
					'new_item'           => __( 'New Contact', $this->plugin_name ),
					'view_item'          => __( 'View Contact', $this->plugin_name ),
					'search_items'       => __( 'Search Contacts', $this->plugin_name ), 
					'not_found'          => __( 'Nothing found in the Database.', $this->plugin_name ), 
					'not_found_in_trash' => __( 'Nothing found in Trash', $this->plugin_name ),
					'parent_item_colon'  => '',
				],
				'description'         => __( 'Contacts.', $this->plugin_name ),
				'menu_icon'           => 'dashicons-admin-users',
				'public'              => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'show_ui'             => true,
				'query_var'           => true,
				'rewrite'	          => [ 'slug' => 'contact', 'with_front' => false ],
				'has_archive'         => 'contacts',
				'menu_position'       => 52,
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => [ 'title', 'author', 'revisions' ],
				'custom_enter_title'  => __( 'Contact Name', $this->plugin_name ),
				'columns'             => [
					#'delete'              => [ 'date', 'author' ],
					'add'                 => [
						'client'              => 'Client',
						'email'               => 'Email',
						'phone'               => 'Phone',
					],
				],
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
		add_filter( 'custom_column_contact_client',         [ $this, 'column_contact_client' ], 10, 4 );
		add_filter( 'custom_column_contact_email',          [ $this, 'column_contact_email' ], 10, 4 );
		add_filter( 'custom_column_contact_phone',          [ $this, 'column_contact_phone' ], 10, 4 );

		// ACF
		add_filter( 'acf/load_field/name=_post_title',      [ $this, 'load_title_field' ] );
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
		if ( is_page( 'add-contact' ) || is_singular( 'contact' ) ) {
			$field['label'] = 'Contact Name';
		}
		return $field;
	}

	/**
	 * Adding the client
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_contact_client( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? '<a href="' . esc_url( get_edit_post_link( $value ) ) . '">' . esc_html( get_the_title( $value ) ) . '</a>' : "&mdash;";
	}

	/**
	 * Adding the email address
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_contact_email( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? '<a href="mailto:' . esc_attr( $value ) . '">' . esc_html( $value ) . '</a>' : "&mdash;";
	}

	/**
	 * Adding the phone number
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_contact_phone( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? '<a href="tel:' . esc_attr( str_replace( ['.','-',' ','+','_'], '', $value ) ) . '">' . esc_html( apply_filters( 'trac/format/phone', $value ) ) . '</a>' : "&mdash;";
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;
