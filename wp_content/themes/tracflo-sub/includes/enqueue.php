<?php
/**
 * Enqueue Scripts & Styles
 */

/**
 * Add enqueue scripts/styles
 *
 * @return	void
 */
function trac_enqueue() {
	add_action( 'wp_enqueue_scripts', 'trac_styles', 999 );
	add_action( 'wp_enqueue_scripts', 'trac_scripts', 999 );
}
add_action( 'after_setup_theme', 'trac_enqueue' );

/**
 * Add the modified date to a file for cache busting, but can be turned off for performance.
 *
 * @param	string	$filepath	The path to a file relative to the theme, eg: /assets/js/script.js
 * @return	void
 */
function trac_maybe_add_modified_for_cache( $filepath ) {
	$output = get_stylesheet_directory_uri() . $filepath;
	if ( apply_filters( 'trac/add_cache', true ) ) {
		$output .= '?v=' . filemtime( realpath(__DIR__ . '/..') . $filepath );
	}
	return $output;
}

/**
 * Load in the base styles
 *
 * @return	void
 */
function trac_styles() {
	global $wp_styles;

	// register main stylesheet
	wp_enqueue_style( 'patch-stylesheet', trac_maybe_add_modified_for_cache( '/assets/css/style.css' . '?t=190512' ), [], null, 'all' );

	//jQuery UI theme css file
	wp_enqueue_style( 'patch-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/base/jquery-ui.css', false, '1.9.0', false );

	// ie-only style sheet
	wp_enqueue_style( 'patch-ie-only', get_stylesheet_directory_uri() . '/assets/css/ie.css', [], null );
	$wp_styles->add_data( 'patch-ie-only', 'conditional', 'lt IE 9' );
}

/**
 * Load in the base scripts
 *
 * @return	void
 */
function trac_scripts() {
	global $wp_scripts;

	/* call jQuery from Google and move to footer * /
	wp_deregister_script('jquery');
	wp_register_script('jquery', ('//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js'), false, null, true);

	/* move core jQuery to footer */
	wp_deregister_script('jquery');
	wp_register_script('jquery', includes_url( '/js/jquery/jquery.js' ), false, null, true);

	wp_enqueue_script( 'patch-chart', '//cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.js', false, null, true );

	/* modernizr (without media query polyfill) */
	#wp_register_script( 'patch-modernizr', get_stylesheet_directory_uri() . '/assets/js/modernizr.custom.min.js', array(), null, false );
	wp_enqueue_script( 'patch-shiv', '//html5shim.googlecode.com/svn/trunk/html5.js', false, null, false );
	$wp_scripts->add_data( 'patch-shiv', 'conditional', 'lt IE 9' );

	/* comment reply script for threaded comments * /
	if ( is_singular() && comments_open() && 1 == get_option('thread_comments') ) {
		wp_enqueue_script( 'comment-reply' );
	}

	/* Adding scripts file in the footer */
	wp_enqueue_script( 'patch-js', trac_maybe_add_modified_for_cache( '/assets/js/scripts.js' ), [ 'jquery', 'jquery-ui-datepicker' ], null, true );//array( 'jquery' )
	$args = [
		'ajaxurl' => admin_url('admin-ajax.php'),
	];
	wp_localize_script( 'patch-js', 'patch', $args );
}

/**
 * Remove jQuery Migrate
 *
 * Be absolutely sure, you are ok to do this, and test your code afterwards.
 *
 * @return	void
 * /
add_filter( 'wp_default_scripts', 'trac_dequeue_jquery_migrate' );
function trac_dequeue_jquery_migrate( &$scripts ) {
	if (! is_admin() ) {
		$scripts->remove( 'jquery');
		$scripts->add( 'jquery', false, array( 'jquery-core' ) );
	}
}
/**/
