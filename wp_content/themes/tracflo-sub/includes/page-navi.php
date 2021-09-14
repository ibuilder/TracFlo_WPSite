<?php

/**
 * Page Navi
 *
 * Usage: do_action('patch/page_navi');
 *
 * Based on Bones by Eddie Machado
 *
 * @return	void
 */
add_action( 'patch/page_navi', 'trac_page_navi', 10, 2 );
function trac_page_navi( $query = null, $page = null )
{
	if ( ! $query ) {
		$query = $GLOBALS['wp_query'];
	}

	if ( ! $page ) {
		$page = max( 1, get_query_var('paged') );
	}

	$bignum = 999999999;
	if ( $query->max_num_pages <= 1 ) {
		return;
	}

	echo '<nav class="pagination">';
	echo paginate_links( array(
		'base'         => str_replace( $bignum, '%#%', esc_url( get_pagenum_link($bignum) ) ),
		'format'       => '',
		'current'      => $page,
		'total'        => $query->max_num_pages,
		'prev_text'    => '&larr;',
		'next_text'    => '&rarr;',
		'type'         => 'list',
		'end_size'     => 3,
		'mid_size'     => 3,
	) );
	echo '</nav>';
}