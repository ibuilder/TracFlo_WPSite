<?php

/**
 * @link              http://www.TracFlo.io/
 * @since             1.0.4
 * @package           TracFlo_Co
 *
 * @wordpress-plugin
 * Plugin Name:       TracFlo Subcontractor: Base Functionality Class
 * Plugin URI:        http://www.TracFlo.io/
 * Description:       Base functionality for the different sections.
 * Version:           1.0.5
 * Author:            TracFlo
 * Author URI:        http://www.TracFlo.io/
 * Contributor:       Jake Snyder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$class_name = 'TracFlo_Base';
if (! class_exists($class_name) ) :

class TracFlo_Base
{
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name = '';

	/**
	 * The TracFlo prefix
	 *
	 * @since    1.0.4
	 * @access   protected
	 * @var      string    $prefix    The string used as a prefix in TracFlo.
	 */
	protected $prefix = '';

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $settings       The array used for settings.
	 */
	protected $settings = [];

	public function __construct( $plugin_name='', $file='' ) {
		$this->plugin_name = str_replace( 'tracflo_', '', $plugin_name );
		$this->prefix      = 'trac';
		$this->path        = plugin_dir_path( $file );

		// Plugin Settings
		$this->settings   = [
			'paths'         => [
				'plugin'       => $this->path,
				'acf'          => $this->path . 'acf-json/',
				'views'        => $this->path . 'views/',
			],
			'notifications' => [
				[
					'key'      => 'create',
					'value'    => $this->plugin_name,
					'message'  => __( "Successfully created.", 'tracflo' ),
					'args'     => 'dismiss=true',
				], [
					'key'      => 'update',
					'value'    => $this->plugin_name,
					'message'  => __( "Successfully updated.", 'tracflo' ),
					'args'     => 'dismiss=true',
				], [
					'key'      => 'delete',
					'value'    => $this->plugin_name,
					'message'  => __( "Successfully deleted.", 'tracflo' ),
					'args'     => 'dismiss=true',
				],
			],
		];
	}

	/**
	 * Load the plugin.
	 *
	 * @since	1.0.0
	 * @return	void
	 */
	public function run()
	{
		$this->settings();

		// Load ACF Fields
		add_filter( 'acf/settings/load_json',         [ $this, 'acf_load_json' ] );

		// WP init
		add_action( 'init', [ $this, 'base_init' ] );
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Class settings
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function settings() {}

	/**
	 * Load ACF Fields
	 *
	 * @author  Jake Snyder
	 * @since	1.0.2
	 * @return	void
	 */
	public function acf_load_json( $paths )
	{
		$paths[] = $this->settings['paths']['plugin'] . 'acf-json/';
		return $paths;
	}

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function base_init()
	{
		$this->add_post_types();
		$this->register_taxonomies();

		// WP Admin
		add_filter( 'enter_title_here',               [ $this, 'enter_title_here' ], 10, 2 );
		add_filter( 'manage_posts_columns',           [ $this, 'update_columns' ] );
		add_action( 'manage_posts_custom_column',     [ $this, 'column_data' ], 10, 2 );

		// Page Load Actions
		add_filter( 'wp',                             [ $this, 'update_actions' ] );
		add_filter( 'template_include',               [ $this, 'template_include' ], 99 );
		add_filter( 'the_posts',                      [ $this, 'add_post' ], 1 );

		// TracFlo
		add_filter( "{$this->prefix}/{$this->plugin_name}/locate_template", [ $this, 'locate_template' ] );

		// Notifications
		add_filter( 'sewn/notifications/queries',     [ $this, 'add_notifications' ] );
	}

	/**
	 * Create notifications
	 *
	 * @author  Jake Snyder
	 * @since	1.0.5
	 * @return	void
	 */
	public function add_notifications( $queries )
	{
		if ( ! empty($this->settings['notifications']) ) {
			foreach ( $this->settings['notifications'] as $args ) {
				$queries[] = $args;
			}
		}
		return $queries;
	}

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function init() {}

	/**
	 * Get plugin templates unless they are overridden in the theme
	 *
	 * @author  Jake Snyder
	 * @since	1.0.3
	 * @param	string $template Template file path
	 * @return	array $template
	 */
	public function template_include( $template )
	{
		if ( ! empty($this->settings['post_types']) ) {
			foreach ( $this->settings['post_types'] as $post_type => $args ) {
				$new_template = null;
				if ( is_singular( $post_type ) ) {
					$new_template = apply_filters( "{$this->prefix}/{$this->plugin_name}/locate_template", "single-$post_type.php" );
				} elseif ( is_post_type_archive($post_type) ) {
					$new_template = apply_filters( "{$this->prefix}/{$this->plugin_name}/locate_template", "archive-$post_type.php" );
				} elseif ( is_page( "add-$post_type" ) ) {
					$new_template = apply_filters( "{$this->prefix}/{$this->plugin_name}/locate_template", "page-add-$post_type.php" );
				}
				if ( $new_template ) {
					return $new_template;
				}
			}
		}
		return $template;
	}

	/**
	 * Update the ticket title with number and subject if one exists
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function add_bic( $post_id ) {
		global $wpdb;

		$project_id = get_post_meta( $post_id, 'project', true );
		if ( $project_id ) {
			$manager_id = get_post_meta( $project_id, 'manager', true );
			if ( $manager_id ) {
				update_post_meta( $post_id, 'bic', $manager_id );
			}
		}
	}

	/**
	 * Logic to check theme for file or load from plugin otherwise
	 *
	 * @author  Jake Snyder
	 * @since	1.0.3
	 * @param	string $filename File to locate
	 * @return	array $template
	 */
	public function locate_template( $filename )
	{
		$path = $this->settings['paths']['views'];
		if ( $template = locate_template( [ $filename ] ) ) {
			return $template;
		} elseif ( is_file( "$path$filename" ) ) {
			return "$path$filename";
		}
		return false;
	}

	/**
	 * See if no compose email page exists, and add it
	 *
	 * @author  Jake Snyder
	 * @since	1.0.3
	 * @return	object $posts Modified $posts with the new register post
	 */
	public function add_post( $posts )
	{
		global $wp, $wp_query;

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
		}
		return $posts;
	}

	/**
	 * Create a dynamic post on-the-fly for the register page.
	 *
	 * source: http://scott.sherrillmix.com/blog/blogger/creating-a-better-fake-post-with-a-wordpress-plugin/
	 *
	 * @author  Jake Snyder
	 * @since	1.0.3
	 * @return	object $post Dynamically created post
	 */
	public function create_post( $page_name )
	{
		// Create a fake post.
		$post = new stdClass();
		$post->ID                    = -1;
		$post->post_author           = 1;
		$post->post_date             = current_time('mysql');
		$post->post_date_gmt         = current_time('mysql', 1);
		$post->post_content          = '';
		$post->post_title            = $this->settings['pages'][$page_name];
		$post->post_excerpt          = '';
		$post->post_status           = 'publish';
		$post->comment_status        = 'closed';
		$post->ping_status           = 'closed';
		$post->post_password         = '';
		$post->post_name             = $page_name;
		$post->to_ping               = '';
		$post->pinged                = '';
		$post->post_modified         = current_time('mysql');
		$post->post_modified_gmt     = current_time('mysql', 1);
		$post->post_content_filtered = '';
		$post->post_parent           = 0;
		$post->guid                  = home_url( '/' . $page_name . '/' );
		$post->menu_order            = 0;
		$post->post_type             = 'page';
		$post->post_mime_type        = '';
		$post->comment_count         = 0;
		$post->filter                = 'raw';
		return $post;   
	}

	/**
	 * Update actions
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function update_actions() {}

	/**
	 * Get the next number to use based on previous highest number
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function next_number() {
		global $wpdb;
		/*
			LEFT JOIN $wpdb->postmeta AS proj ON p.ID = proj.post_id
			AND proj.meta_key = 'project'
			AND proj.value =
		*/
		$query = "SELECT m.meta_value FROM $wpdb->posts AS p
			LEFT JOIN $wpdb->postmeta AS m ON p.ID = m.post_id
			WHERE p.post_type = '{$this->plugin_name}'
			AND p.post_status = 'publish'
			AND m.meta_key = 'number'
			ORDER BY CAST(m.meta_value AS SIGNED) DESC
			LIMIT 1";
		$last_number = $wpdb->get_var($query);
		return apply_filters( "{$this->prefix}/format/last_number", $last_number );
	}

	/**
	 * Add the custom post types used by application
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function add_post_types()
	{
		if ( ! empty($this->settings['post_types']) ) {
			foreach ( $this->settings['post_types'] as $post_type => $args ) {
				register_post_type( $post_type, $args );
			}
		}
	}

	/**
	 * Add the taxonomies
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function register_taxonomies()
	{
		if ( ! empty($this->settings['taxonomies']) ) {
			foreach ( $this->settings['taxonomies'] as $taxonomy => $args ) {
				$terms = null;
				if ( ! empty($args['terms']) ) {
					$terms = $args['terms'];
					unset($args['terms']);
				}
				register_taxonomy( $taxonomy, $args['post_types'], $args );
				if ( $terms ) {
					foreach ( $terms as $slug => $name ) {
						if ( ! term_exists( $slug, $taxonomy ) ) {
							wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
						}
					}
				}
			}
		}
	}

	/**
	 * Add a custom title placeholder
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function enter_title_here( $input, $post )
	{
		if ( ! empty($this->settings['post_types']) ) {
			if ( 'Enter title here' == $input ) {
				foreach ( $this->settings['post_types'] as $post_type => $args ) {
					if ( ! empty($args['custom_enter_title']) && $post->post_type == $post_type ) {
						return $args['custom_enter_title'];
					}
				}
			}
		}
		return $input;
	}

	/**
	 * Update post list columns
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function update_columns( $columns )
	{
		if ( ! empty($this->settings['post_types']) ) {
			$screen = get_current_screen();
			if ( $screen ) {
				foreach ( $this->settings['post_types'] as $post_type => $args ) {
					if ( $screen->post_type == $post_type ) {
						if ( ! empty($args['columns']['delete']) ) {
							foreach ( $args['columns']['delete'] as $column ) {
								unset( $columns[$column] );
							}
						}

						if ( ! empty($args['columns']['add']) ) {
							foreach ( $args['columns']['add'] as $name => $title ) {
								$columns[$name] = $title;
							}
						}
					}
				}
			}
		}
		return $columns;
	}

	/**
	 * Add the actual column data
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_data( $column, $post_id )
	{
		if ( ! empty($this->settings['post_types']) ) {
			foreach ( $this->settings['post_types'] as $post_type => $args ) {
				if ( ! empty($args['columns']['add']) && get_post_type($post_id) == $post_type ) {
					foreach ( $args['columns']['add'] as $name => $title ) {
						if ( $name == $column ) {
							$value = get_metadata( 'post', $post_id, $name, true );
							echo apply_filters( "custom_column_{$post_type}_{$name}", $value, $post_id, $name, $post_type );
						}
					}
				}
			}
		}
	}
}

endif;
