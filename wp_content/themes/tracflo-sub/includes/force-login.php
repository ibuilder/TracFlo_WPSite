<?php

/**
 * @link              http://www.TracFlo.io/
 * @since             1.0.0
 * @package           TracFlo_Force_Login
 *
 * @wordpress-plugin
 * Plugin Name:       TracFlo Subcontractor: Force Login
 * Plugin URI:        http://www.TracFlo.io/
 * Description:       Force users to be logged in. Based off of https://wordpress.org/plugins/wp-force-login/.
 * Version:           1.0.0
 * Author:            TracFlo
 * Author URI:        http://www.TracFlo.io/
 * Contributor:       Jake Snyder
 */

function trac_forcelogin() {
	// Exceptions for AJAX, Cron, or WP-CLI requests
	if ( ( defined('DOING_AJAX') && DOING_AJAX ) || ( defined('DOING_CRON') && DOING_CRON ) || ( defined('WP_CLI') && WP_CLI ) ) {
		return;
	}

	// Redirect unauthorized visitors
	if ( ! is_user_logged_in() ) {
		// Get URL
		$url = ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'];
		// port is prepopulated here sometimes
		if ( false === strpos($_SERVER['HTTP_HOST'], ':') ) {
			$url .= in_array($_SERVER['SERVER_PORT'], ['80', '443']) ? '' : ':' . $_SERVER['SERVER_PORT'];
		}
		$url .= $_SERVER['REQUEST_URI'];

		// Apply filters
		$whitelist    = apply_filters( 'trac/forcelogin/whitelist', [] );
		$redirect_url = apply_filters( 'trac/forcelogin/redirect', $url );
		
		// Redirect
		if ( preg_replace('/\?.*/', '', $url) != preg_replace('/\?.*/', '', wp_login_url()) && ! in_array($url, $whitelist) && false === strpos($url, home_url( '/client/ticket/' ) ) ) {
			wp_safe_redirect( wp_login_url( $redirect_url ), 302 );
			die;
		}
	} else {
		// Only allow Multisite users access to their assigned sites
		if ( function_exists('is_multisite') && is_multisite() ) {
			$current_user = wp_get_current_user();
			if ( ! is_user_member_of_blog( $current_user->ID ) && ! is_super_admin() ) {
				wp_die( __( "You're not authorized to access this site.", 'tracflo' ), get_option('blogname') . ' &rsaquo; ' . __( "Error", 'tracflo' ) );
			}
		}
	}
}
add_action( 'template_redirect', 'trac_forcelogin' );

/**
 * Restrict REST API for authorized users only
 *
 * @param WP_Error|null|bool $result WP_Error if authentication error, null if authentication
 *                              method wasn't used, true if authentication succeeded.
 */
function trac_forcelogin_rest_access( $result ) {
	if ( null === $result && ! is_user_logged_in() ) {
		return new WP_Error( 'rest_unauthorized', __( "Only authenticated users can access the REST API.", 'tracflo' ), [ 'status' => rest_authorization_required_code() ] );
	}
	return $result;
}
add_filter( 'rest_authentication_errors', 'trac_forcelogin_rest_access', 99 );
