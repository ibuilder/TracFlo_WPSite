<?php
/**
 * Cleanup WP functionality
 */


/**
 * Remove admin menu items
 */
function trac_remove_menus() {
	remove_menu_page( 'edit.php' ); //Posts
}
add_action( 'admin_menu', 'trac_remove_menus' );


/**
 * Launch some basic cleanup
 */
function trac_cleanup() {
	// launching operation cleanup
	add_action( 'init',                       'trac_head_cleanup' );
	// remove WP version from RSS
	add_filter( 'the_generator',              'trac_rss_version' );
	// cleaning up random code around images
	add_filter( 'the_content',                'trac_filter_ptags_on_images' );
	// cleaning up excerpt
	add_filter( 'excerpt_more',               'trac_excerpt_more' );
	// excerpt length
	add_filter( 'excerpt_length',             'trac_excerpt_length', 999 );
}
add_action( 'after_setup_theme',          'trac_cleanup' );


/**
 * Cleanup the head output
 *
 */
function trac_head_cleanup() {
	// Remove canonical links
	// remove_action('wp_head',           'rel_canonical');
	// Remove shortlink from head and header
	remove_action( 'wp_head',            'wp_shortlink_wp_head', 10, 0 );
	remove_action( 'template_redirect',  'wp_shortlink_header', 11, 0 );
	// Emoji
	remove_action( 'wp_head',            'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles',    'print_emoji_styles' );
	// Remove REST API links
	remove_action( 'wp_head',            'rest_output_link_wp_head', 10 );
	remove_action( 'wp_head',            'wp_oembed_add_discovery_links', 10 );
	// category feeds
	remove_action( 'wp_head',            'feed_links_extra', 3 );
	// post and comment feeds
	remove_action( 'wp_head',            'feed_links', 2 );
	// windows live writer
	remove_action( 'wp_head',            'wlwmanifest_link' );
	// index link
	remove_action( 'wp_head',            'index_rel_link' );
	// previous link
	remove_action( 'wp_head',            'parent_post_rel_link', 10, 0 );
	// start link
	remove_action( 'wp_head',            'start_post_rel_link', 10, 0 );
	// links for adjacent posts
	remove_action( 'wp_head',            'adjacent_posts_rel_link', 10, 0); 
	remove_action( 'wp_head',            'adjacent_posts_rel_link_wp_head', 10, 0 );
	// WP version
	remove_action( 'wp_head',            'wp_generator' );
	// remove WP version from css
	#add_filter( 'style_loader_src',      'trac_remove_wp_ver_css_js', 9999 );
	// remove Wp version from scripts
	add_filter( 'script_loader_src',     'trac_remove_wp_ver_css_js', 9999 );
}


/**
 * Remove embed
 * /
function trac_deregister_embed() {
	wp_dequeue_script( 'wp-embed' );
}
add_action( 'wp_footer', 'trac_deregister_embed' );


/**
 * Remove WP version from RSS
 *
 * @return	string Empty string
 */
function trac_rss_version() {
	return '';
}


/**
 * Remove the p from around imgs
 *
 * @return	string Modified content
 */
function trac_filter_ptags_on_images( $content ) {
	return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
}


/**
 * Updates the […] for Read More links
 *
 * @return	bool Modified status for comments.
 */
function trac_excerpt_more( $more ) {
	global $post;
	return "&hellip;" . ' <a class="excerpt-read-more" href="'. get_permalink($post->ID) . '" title="Read ' . get_the_title($post->ID).'">Read more &raquo;</a>';
}


/**
 * Change the excerpt length
 *
 * @return	int New total words
 */
function trac_excerpt_length( $length ) {
	return 30;
}


/**
 * Remove WP version from scripts
 *
 * @return	bool Modified status for comments.
 */
function trac_remove_wp_ver_css_js( $src ) {
	if ( strpos( $src, 'ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
