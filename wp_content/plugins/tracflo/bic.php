<?php
/**
 * == TracFlo == 
 * Ball In Court 
 *
 * Metabox for responsible party
 */

if ( ! class_exists('TracFlo_BallInCourt') ) {
  class TracFlo_BallInCourt {

	var $pluginPath;
	var $pluginUrl;
	
	public function __construct()
	{
		// Set Plugin Path
		$this->pluginPath = dirname(__FILE__);
	
		// Set Plugin URL
		$this->pluginUrl = WP_PLUGIN_URL . '/tracflo';
		
    	add_action( 'cmb2_init', array( $this, 'tf_bic_metabox' ) );
    	add_filter( 'single_template', 'tf_bic_template' ) ;	
    	// add_action('init', 'tf_bic_page');
	}
	/**
	 * Metabox
	 */	
	public function tf_bic_metabox(){
	
	  // set the prefix (start with an underscore to hide it from the custom fields list
	    $prefix = '_tf_bic_';
	     
	    // create the metabox
	    $cmb = new_cmb2_box( array(
	        'id'            => 'tf_bic',
	        'title'         => 'Ball In Court',
	        'object_types'  => array( 'tf_creport' ), // post type
	        'context'       => 'normal', // 'normal', 'advanced' or 'side'
	        'priority'      => 'high', // 'high', 'core', 'default' or 'low'
	        'show_names'    => true, // show field names on the left
	        'cmb_styles'    => false, // false to disable the CMB stylesheet
	        'closed'        => false, // keep the metabox closed by default
	    ) );
		 
		// Contract
		$cmb->add_field( array(
		    'name'        => __( 'Email Address' ),
		    'id'          => 'bic_username',
		    'type'        => 'post_search_text', // This field type
		    // post type also as array
		    'post_type'   => 'cr_posttype_contract',
		    // Default is 'checkbox', used in the modal view to select the post type
		    'select_type' => 'select',
		    // Will replace any selection with selection from modal. Default is 'add'
		    'select_behavior' => 'replace',
		) );

		
	}
   
  }
} 

$creport = new TracFlo_BallInCourt();
?>