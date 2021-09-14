<?php
/**
 * == TracFlo == 
 * Locations
 *
 */

if ( ! class_exists('TracFlo_Location') ) {
  class TracFlo_Location {

	var $pluginPath;
	var $pluginUrl;
	
	public function __construct()
	{
		// Set Plugin Path
		$this->pluginPath = dirname(__FILE__);
	
		// Set Plugin URL
		$this->pluginUrl = WP_PLUGIN_URL . '/tracflo';
		
		add_action( 'init', array( $this, 'tf_ln_posttype' ) );					
    	add_action( 'cmb2_init', array( $this, 'tf_ln_metabox' ) );		
	}
	/**
	 * Post Type
	 */	
	public function tf_ln_posttype(){
	
	  $labels = array(
		'name'                  => _x( 'Location', 'Post Type General Name', 'tf_ln' ),
		'singular_name'         => _x( 'Location', 'Post Type Singular Name', 'tf_ln' ),
		'menu_name'             => __( 'Locations', 'tf_ln' ),
		'name_admin_bar'        => __( 'Locations', 'tf_ln' ),
		'archives'              => __( 'Location Archives', 'tf_ln' ),
		'parent_item_colon'     => __( 'Parent Location:', 'tf_ln' ),
		'all_items'             => __( 'All Locations', 'tf_ln' ),
		'add_new_item'          => __( 'Add Location', 'tf_ln' ),
		'add_new'               => __( 'Add Location', 'tf_ln' ),
		'new_item'              => __( 'New Location', 'tf_ln' ),
		'edit_item'             => __( 'Edit Location', 'tf_ln' ),
		'update_item'           => __( 'Update Location', 'tf_ln' ),
		'view_item'             => __( 'View Location', 'tf_ln' ),
		'search_items'          => __( 'Search Locations', 'tf_ln' ),
		'not_found'             => __( 'Not found', 'tf_ln' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'tf_ln' ),
		'featured_image'        => __( 'Featured Image', 'tf_ln' ),
		'set_featured_image'    => __( 'Set featured image', 'tf_ln' ),
		'remove_featured_image' => __( 'Remove featured image', 'tf_ln' ),
		'use_featured_image'    => __( 'Use as featured image', 'tf_ln' ),
		'insert_into_item'      => __( 'Insert into item', 'tf_ln' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'tf_ln' ),
		'items_list'            => __( 'Items list', 'tf_ln' ),
		'items_list_navigation' => __( 'Items list navigation', 'tf_ln' ),
		'filter_items_list'     => __( 'Filter items list', 'tf_ln' ),
	);
	$args = array(
		'label'                 => __( 'Locations', 'tf_ln' ),
		'description'           => __( 'Dataset collecting Locations', 'tf_ln' ),
		'labels'                => $labels,
		'supports'              => array( 'comments', 'trackbacks', 'revisions', ),
		'taxonomies'            => array( 'locations' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,		
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'tf_location', $args );

	}

	/**
	 * Metabox
	 */	
	public function tf_ln_metabox(){
	
	  // set the prefix (start with an underscore to hide it from the custom fields list
	    $prefix = '_tf_ln_';
	     
	    // Location
	    $cmb = new_cmb2_box( array(
	        'id'            => 'tf_location',
	        'title'         => 'Location Name',
	        'object_types'  => array( 'tf_location' ), // post type
	        'context'       => 'normal', // 'normal', 'advanced' or 'side'
	        'priority'      => 'high', // 'high', 'core', 'default' or 'low'
	        'show_names'    => true, // show field names on the left
	        'cmb_styles'    => false, // false to disable the CMB stylesheet
	        'closed'        => false, // keep the metabox closed by default
	    ) );

		$cmb->add_field( array(
		    'name'    => 'Location Name',
		    'desc'    => 'Name associate with Location',
		    'default' => '',
		    'id'      => 'tf_location_name',
		    'type'    => 'text',
		) );

	    // Parent Location
		$cmb->add_field( array(
		    'name'        => __( 'Parent Location' ),
		    'id'          => 'tf_location_parent',
		    'type'        => 'post_search_text', // This field type
		    // post type also as array
		    'post_type'   => 'tf_location',
		    // Default is 'checkbox', used in the modal view to select the post type
		    'select_type' => 'select',
		    // Will replace any selection with selection from modal. Default is 'add'
		    'select_behavior' => 'replace',
		) );

		// attachments
		$cmb->add_field( array(
		    'name'    => 'Attachments',
		    'desc'    => 'Upload an image or enter an URL.',
		    'id'      => 'tf_location_attachment',
		    'type'    => 'file',
		    // Optional:
		    'options' => array(
		        'url' => false, // Hide the text input for the url
		    ),
		    'text'    => array(
		        'add_upload_file_text' => 'Add File' // Change upload button text. Default: "Add or Upload File"
		    ),
		) );
	}
	
	
  }
} 

$creport = new TracFlo_Location();
?>