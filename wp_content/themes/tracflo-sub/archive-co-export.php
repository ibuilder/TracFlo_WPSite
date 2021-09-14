<?php

$filename = get_option('blogname') . '_CO_' . date_i18n('Y-m-d_g-h-A', current_time('timestamp'));
$fileType = "csv";
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=$filename.$fileType");

$nl        = "\r\n";

function trac_col_export( $content ) {
	$delimiter = ",";
	return '"' . strip_tags( str_replace('"', '""', $content) ) . '"' . $delimiter;
}

if (have_posts()) :

	$output = '';

	// Set up the titles
	$titles = [ 'Status','#','Client #','Issue Date','Subject','Project','Client', ];
	if ( 'open' !== $current_tab ) {
		$titles[] = 'Amount';
		$titles[] = 'Accepted';
	}
	$titles[] = 'Balance';
	if ( 'open' !== $current_tab ) {
		$titles[] = 'PO#';
		$titles[] = 'Paid On';
	}
	$titles[] = 'Complete';

	foreach ( $titles as $title ) {
		$output .= trac_col_export( $title );
	}

	$output .= $nl;

	while (have_posts()) : the_post();

		$project_id     = get_post_meta( get_the_ID(), 'project', true );
		$project        = get_post( $project_id );
		$client_id      = get_post_meta( $project_id, 'client', true );
		$client         = get_post( $client_id );
		$closed         = has_term( 'closed', 'co_status' );
		$complete       = has_term( 'complete', 'co_status' );
		$number         = get_field( 'number' );
		$number_client  = get_field( 'number_client' );
		$summary        = get_field( 'subject' );
		$date           = get_field( 'date' );
		$paid_date      = get_post_meta( get_the_ID(), 'paid_date', true );
		$type           = get_field( 'type' );
		$po             = get_post_meta( get_the_ID(), 'paid_po', true );

		$subtotals      = apply_filters( 'trac/item/totals', get_the_ID() );
		$total         += $subtotals['total'];
		$paid_total    += $subtotals['paid_total'];
		$balance_total += $subtotals['balance'];

		// Set up the row
		$row = '';

		$row .= trac_col_export( $closed ? 'Paid' : 'Open' );
		$row .= trac_col_export( $number );
		$row .= trac_col_export( $number_client );
		$row .= trac_col_export( get_the_date( 'm/d/y' ) );
		$row .= trac_col_export( $summary );
		$row .= trac_col_export( get_post_field( 'post_title', $project_id, 'display' ) );
		$row .= trac_col_export( ! empty($client) ? $client->post_title : '' );
		if ( 'open' !== $current_tab ) {
			$row .= trac_col_export( trac_money_format( $subtotals['total'] ) );
			$row .= trac_col_export( trac_money_format( $subtotals['paid_total'] ) );
		}
		$row .= trac_col_export( trac_money_format( $subtotals['balance'] ) );
		if ( 'open' !== $current_tab ) {
			$row .= trac_col_export( $po ? esc_html( $po ) : "" );
			$row .= trac_col_export( $paid_date ? trac_date( $paid_date ) : "" );
		}
		$row .= trac_col_export( $complete ? "Y" : "N" );

		$output .= $row . $nl;

	endwhile;
else :
	$output .= "No items yet" . $nl;
endif;

echo $output;
