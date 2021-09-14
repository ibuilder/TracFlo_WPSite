<?php

/**
 * Make sure owner user has agreed to terms
 * /
add_action( 'init', function() {
	$owner_id = get_option('options_owner');
	if ( is_user_logged_in() && ! is_admin() && $GLOBALS['pagenow'] !== 'wp-login.php' && false === strpos($_SERVER['REQUEST_URI'], '/terms') && ( ! $owner_id || ! get_field( 'accept_terms', "user_$owner_id" ) ) ) {
		// User is restricted
		wp_safe_redirect( add_query_arg( 'action', 'termsrequired', home_url('/terms/') ) );
		die;
	}

	// Add terms pages
	add_filter( 'the_posts', 'trac_terms_add_post', 1 );
	add_filter( 'the_posts', 'trac_privacy_add_post', 1 );

	// Add notification on success
	add_filter( 'sewn/notifications/queries', function( $queries ) {
		$new_notifications = [
			[
				'key'     => 'action',
				'value'   => 'acceptterms',
				'message' => __( "You are all set to get started.", 'tracflo' ),
				'args'    => 'dismiss=true&fade=true',
			],
			[
				'key'     => 'action',
				'value'   => 'termsrequired',
				'message' => __( "To continue using this service, the Terms must be agreed to.", 'tracflo' ),
			],
		];
		foreach ( $new_notifications as $args ) {
			$queries[] = $args;
		}
		return $queries;
	});
}, 1 );



	/**
	 * See if no page exists, and add it
	 *
	 * @author  Jake Snyder
	 * @since	1.0.3
	 * @return	object $posts Modified $posts with the new register post
	 */
	function trac_terms_add_post( $posts )
	{
		global $wp, $wp_query;

		// Check if the requested page matches our target, and no posts have been retrieved
		if ( ! $posts && strtolower($wp->request) === 'terms' ) {
			// Add the fake post
			$posts   = [];
			$posts[] = trac_terms_create_post( strtolower($wp->request) );

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
	function trac_terms_create_post( $page_name )
	{
		// Create a fake post.
		$post = new stdClass();
		$post->ID                    = -1;
		$post->post_author           = 1;
		$post->post_date             = current_time('mysql');
		$post->post_date_gmt         = current_time('mysql', 1);
		$post->post_content          = '';
		$post->post_title            = 'Terms of Service';
		$post->post_excerpt          = '';
		$post->post_status           = 'publish';
		$post->comment_status        = 'closed';
		$post->ping_status           = 'closed';
		$post->post_password         = '';
		$post->post_name             = 'terms';
		$post->to_ping               = '';
		$post->pinged                = '';
		$post->post_modified         = current_time('mysql');
		$post->post_modified_gmt     = current_time('mysql', 1);
		$post->post_content_filtered = '';
		$post->post_parent           = 0;
		$post->guid                  = home_url( '/terms/' );
		$post->menu_order            = 0;
		$post->post_type             = 'page';
		$post->post_mime_type        = '';
		$post->comment_count         = 0;
		$post->filter                = 'raw';
		return $post;   
	}



	/**
	 * See if no page exists, and add it
	 *
	 * @author  Jake Snyder
	 * @since	1.0.3
	 * @return	object $posts Modified $posts with the new register post
	 */
	function trac_privacy_add_post( $posts )
	{
		global $wp, $wp_query;

		// Check if the requested page matches our target, and no posts have been retrieved
		if ( ! $posts && strtolower($wp->request) === 'privacy' ) {
			// Add the fake post
			$posts   = [];
			$posts[] = trac_privacy_create_post( strtolower($wp->request) );

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
	function trac_privacy_create_post( $page_name )
	{
		// Create a fake post.
		$post = new stdClass();
		$post->ID                    = -1;
		$post->post_author           = 1;
		$post->post_date             = current_time('mysql');
		$post->post_date_gmt         = current_time('mysql', 1);
		$post->post_content          = '';
		$post->post_title            = 'Privacy Policy';
		$post->post_excerpt          = '';
		$post->post_status           = 'publish';
		$post->comment_status        = 'closed';
		$post->ping_status           = 'closed';
		$post->post_password         = '';
		$post->post_name             = 'privacy';
		$post->to_ping               = '';
		$post->pinged                = '';
		$post->post_modified         = current_time('mysql');
		$post->post_modified_gmt     = current_time('mysql', 1);
		$post->post_content_filtered = '';
		$post->post_parent           = 0;
		$post->guid                  = home_url( '/privacy/' );
		$post->menu_order            = 0;
		$post->post_type             = 'page';
		$post->post_mime_type        = '';
		$post->comment_count         = 0;
		$post->filter                = 'raw';
		return $post;   
	}
