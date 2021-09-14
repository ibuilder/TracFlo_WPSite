<?php
/**
* Plugin Name: TracFlo
* Plugin URI: http://tracflo.com/
* Description: Track the work flow of your construction project
* Version: 0.1
* Author: TracFlo Team
* Author URI: http://www.TracFlo.com
* License: Commercial
*/

/**
 * Get the bootstrap!
 */
if ( file_exists(  __DIR__ . '/cmb2/init.php' ) ) {
  require_once  __DIR__ . '/cmb2/init.php';
} elseif ( file_exists(  __DIR__ . '/CMB2/init.php' ) ) {
  require_once  __DIR__ . '/CMB2/init.php';
}

// classes
// require_once  'classes/frontend-edit.php';
// require_once  'classes/frontend-submit.php';

/**
 * Load TracFlo
 */

// register settings
require_once  'settings/location.php';
require_once  'settings/contract.php';
require_once  'settings/labor.php';
require_once  'settings/material.php';
require_once  'settings/equipment.php';


// register everything else
require_once  'creport.php';
// add timesheets, documents, and reports
require_once  'bic.php';

add_shortcode( 'cmb-form', 'cmb2_do_frontend_form_shortcode' );
/**
 * Shortcode to display a CMB2 form for a post ID.
 * @param  array  $atts Shortcode attributes
 * @return string       Form HTML markup
 */
function cmb2_do_frontend_form_shortcode( $atts = array() ) {
    global $post;

    /**
     * Depending on your setup, check if the user has permissions to edit_posts
     */
    if ( ! current_user_can( 'edit_posts' ) ) {
        return __( 'You do not have permissions to edit this post.', 'lang_domain' );
    }

    /**
     * Make sure a WordPress post ID is set.
     * We'll default to the current post/page
     */
    if ( ! isset( $atts['post_id'] ) ) {
        $atts['post_id'] = $post->ID;
    }

    // If no metabox id is set, yell about it
    if ( empty( $atts['id'] ) ) {
        return __( "Please add an 'id' attribute to specify the CMB2 form to display.", 'lang_domain' );
    }

    $metabox_id = esc_attr( $atts['id'] );
    $object_id = absint( $atts['post_id'] );
    // Get our form
    $form = cmb2_get_metabox_form( $metabox_id, $object_id );

    return $form;
}