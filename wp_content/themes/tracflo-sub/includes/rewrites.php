<?php

/**
 * Custom Rewrite Rules
 */
function trac_rewrites() {
	// Generate pdf files
	add_rewrite_rule(
		"co/([0-9]*)\.pdf$",
		'index.php?co=$matches[1]&pdf_id=$matches[1]',
		'top'
	);

	add_filter( 'query_vars', 'trac_register_query_vars' );
}
add_action( 'init', 'trac_rewrites' );

function trac_register_query_vars( $vars ) {
	$vars[] = 'pdf_id';
	return $vars;
}

/**
 * No trailing slash on pdf rewrites
 * /
// Cancel the redirection for our fake page
function trac_cancel_redirect_canonical( $redirect_url ) {
	return ( get_query_var('pdf_id') ) ? false : $redirect_url;
}
add_filter( 'redirect_canonical', 'trac_cancel_redirect_canonical' );
/**/
