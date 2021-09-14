<?php
/**
 * == TracFlo == 
 * Construction Report
 *
 * to be done
 * Set up custom meta based on custom post type: https://deliciousbrains.com/managing-custom-tables-wordpress/
 * https://pippinsplugins.com/series/building-a-database-abstraction-layer/
 */

if ( ! class_exists('TracFlo_ConstructionReport') ) {
  class TracFlo_ConstructionReport {

	var $pluginPath;
	var $pluginUrl;
	
	public function __construct()
	{
		// Set Plugin Path
		$this->pluginPath = dirname(__FILE__);
	
		// Set Plugin URL
		$this->pluginUrl = WP_PLUGIN_URL . '/tracflo';
		
		add_action( 'init', array( $this, 'tf_cr_posttype' ) );					
    	add_action( 'cmb2_init', array( $this, 'tf_cr_metabox' ) );
    	add_filter( 'single_template', 'tf_cr_template' ) ;	
    	// add_action('init', 'tf_cr_page');
	}
	/**
	 * Post Type
	 */	
	public function tf_cr_posttype(){
	
	  $labels = array(
		'name'                  => _x( 'Construction Reports', 'Post Type General Name', 'tf_cr' ),
		'singular_name'         => _x( 'Construction Reports', 'Post Type Singular Name', 'tf_cr' ),
		'menu_name'             => __( 'Construction Reports', 'tf_cr' ),
		'name_admin_bar'        => __( 'Construction Reports', 'tf_cr' ),
		'archives'              => __( 'Construction Report Archives', 'tf_cr' ),
		'parent_item_colon'     => __( 'Parent Construction Report:', 'tf_cr' ),
		'all_items'             => __( 'All Construction Reports', 'tf_cr' ),
		'add_new_item'          => __( 'Add Construction Report', 'tf_cr' ),
		'add_new'               => __( 'Add Construction Report', 'tf_cr' ),
		'new_item'              => __( 'New Construction Report', 'tf_cr' ),
		'edit_item'             => __( 'Edit Construction Report', 'tf_cr' ),
		'update_item'           => __( 'Update Construction Report', 'tf_cr' ),
		'view_item'             => __( 'View Construction Report', 'tf_cr' ),
		'search_items'          => __( 'Search Construction Reports', 'tf_cr' ),
		'not_found'             => __( 'Not found', 'tf_cr' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'tf_cr' ),
		'featured_image'        => __( 'Featured Image', 'tf_cr' ),
		'set_featured_image'    => __( 'Set featured image', 'tf_cr' ),
		'remove_featured_image' => __( 'Remove featured image', 'tf_cr' ),
		'use_featured_image'    => __( 'Use as featured image', 'tf_cr' ),
		'insert_into_item'      => __( 'Insert into item', 'tf_cr' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'tf_cr' ),
		'items_list'            => __( 'Items list', 'tf_cr' ),
		'items_list_navigation' => __( 'Items list navigation', 'tf_cr' ),
		'filter_items_list'     => __( 'Filter items list', 'tf_cr' ),
	);
	$args = array(
		'label'                 => __( 'Construction Reports', 'tf_cr' ),
		'description'           => __( 'Dataset collecting construction reports', 'tf_cr' ),
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
	register_post_type( 'tf_creport', $args );

	}

	/**
	 * Metabox
	 */	
	public function tf_cr_metabox(){
	
	  // set the prefix (start with an underscore to hide it from the custom fields list
	    $prefix = '_tf_cr_';
	     
	    // create the metabox
	    $cmb = new_cmb2_box( array(
	        'id'            => 'tf_creport',
	        'title'         => 'Construction Report',
	        'object_types'  => array( 'tf_creport' ), // post type
	        'context'       => 'normal', // 'normal', 'advanced' or 'side'
	        'priority'      => 'high', // 'high', 'core', 'default' or 'low'
	        'show_names'    => true, // show field names on the left
	        'cmb_styles'    => false, // false to disable the CMB stylesheet
	        'closed'        => false, // keep the metabox closed by default
	    ) );

	    // Work Date
		$cmb->add_field( array(
		    'name' => 'Work Date',
		    'desc' => 'Day work was completed',
		    'type' => 'text_date_timestamp',
		    'id'   => 'cr_work_date'
		) );
		 
		// Change Order
		$cmb->add_field( array(
		    'name'             => 'Is this a change to base scope?',
		    'id'               => 'cr_contract_change',
		    'type'             => 'radio',
		    'show_option_none' => true,
		    'options'          => array(
		        'standard' => __( 'No', 'cmb2' ),
		        'custom'   => __( 'Yes', 'cmb2' ),
		    ),
		) );
		 
		// Contract
		$cmb->add_field( array(
		    'name'        => __( 'Contract' ),
		    'id'          => 'cr_contract_contract',
		    'type'        => 'post_search_text', // This field type
		    // post type also as array
		    'post_type'   => 'cr_posttype_contract',
		    // Default is 'checkbox', used in the modal view to select the post type
		    'select_type' => 'select',
		    // Will replace any selection with selection from modal. Default is 'add'
		    'select_behavior' => 'replace',
		) );
		// location of work and description at location (repeatable)
		$group_field_id = $cmb->add_field( array(
		    'id'          => 'cr_contract_location_group',
		    'type'        => 'group',
		    'description' => __( 'Generates reusable form entries', 'cmb2' ),
		    // 'repeatable'  => false, // use false if you want non-repeatable group
		    'options'     => array(
		        'group_title'   => __( 'Entry {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
		        'add_button'    => __( 'Add Another Entry', 'cmb2' ),
		        'remove_button' => __( 'Remove Entry', 'cmb2' ),
		        'sortable'      => true, // beta
		        // 'closed'     => true, // true to have the groups closed by default
		    ),
		) );

		// Id's for group's fields only need to be unique for the group. Prefix is not needed.
		$cmb->add_group_field( $group_field_id, array(
		    'name'        => __( 'Location' ),
		    'id'          => 'cr_contract_location',
		    'type'        => 'post_search_text', // This field type
		    // post type also as array
		    'post_type'   => 'tf_posttype_location',
		    // Default is 'checkbox', used in the modal view to select the post type
		    'select_type' => 'select',
		    // Will replace any selection with selection from modal. Default is 'add'
		    'select_behavior' => 'replace',
		) );

		$cmb->add_group_field( $group_field_id, array(
		    'name' => 'Description',
		    'description' => 'Write a short description for this entry',
		    'id'   => 'cr_contract_description',
		    'type' => 'textarea_small',
		) );

		// attachments
		$cmb->add_field( array(
		    'name'    => 'Attachments',
		    'desc'    => 'Upload an image or enter an URL.',
		    'id'      => 'cr_contract_attachment',
		    'type'    => 'file',
		    // Optional:
		    'options' => array(
		        'url' => false, // Hide the text input for the url
		    ),
		    'text'    => array(
		        'add_upload_file_text' => 'Add File' // Change upload button text. Default: "Add or Upload File"
		    ),
		) );
		
		// labor
		// material
		//equipment
	}
   
   public function tf_cr_template($single_template) {
	 global $post;

	 if ($post->post_type == 'books') {
	      $single_template = dirname( __FILE__ ) . '/single-creport.php';
	 }
	 return $single_template;
	}
/*
  public function tf_cr_page() {
	        $new_page_template = ''; //ex. template-custom.php. Leave blank if you don't want a custom page template.
	        //don't change the code bellow, unless you know what you're doing
	        $page_check = get_page_by_title($new_page_title);
	        $new_page = array(
	                'post_type' => 'page',
	                'post_title' => 'Add Construction Report',
	                'post_content' => '[cmb-form id="tf_creport"]',
	                'post_status' => 'publish',
	                'post_author' => 1,
	        );
	        if(!isset($page_check->ID)){
	                $new_page_id = wp_insert_post($new_page);
	                if(!empty($new_page_template)){
	                        update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
	                }
			}
		}
		*/
  }
} 

$creport = new TracFlo_ConstructionReport();
?>