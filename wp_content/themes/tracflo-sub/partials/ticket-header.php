<div id="invoice_header" class="clearfix">
	<div class="grid2of3">
		<h1>Ticket <?php the_title(); ?></h1>
		<div class="subject-message">
			<strong>Status</strong>: 
<?php
			if ( $terms = wp_get_post_terms( get_the_ID(), 'ticket_status' ) ) :
				$term = $terms[0];
				echo ( in_array( $term->slug, [ 'approvetm', 'approve' ] ) ? 'Approved Time &amp; Materials' : $term->name );
			else :
				echo 'Draft';
			endif;
?>
		</div>
		<div class="activity-message">
<?php
			$user = '';
			if ( ! empty($latest_history['user']) ) {
				if ( $name = get_the_author_meta( 'display_name', $latest_history['user'] ) ) {
					$user = $name;
				} elseif ( is_string($latest_history['user']) ) {
					$user = $latest_history['user'];
				}
			}
			$date      = ! empty($latest_history['date']) ? date_i18n( 'm/d/Y \a\t g:i a', $latest_history['date'] ) : '';
			$note      = apply_filters( 'trac/history/note', $latest_history );#trac_get_history_note( $latest_history );
?>
			<strong>Latest Activity</strong>: <?php echo $note; ?>
			<span class="meta">
				<strong class="sent-by"><?php echo $user; ?></strong> on <?php echo $date; ?>
			</span>
			<a href="#activity_log" id="view_history_link">View History</a>
		</div>
	</div>
	<div class="grid1of3">
		<div class="invoice-action-buttons">
<?php /** / ?>
			<a target="_blank" title="View the Web Invoice" class="btn-action btn-pill btn-invoice-action btn-invoice-action-web-invoice" href="/client/invoices/6c4905d00cd7ca11730bd8ad4e62dff8037f8546">
				<span class="invoice-action-icon web-invoice-icon">View the</span> Web Invoice
			</a>
<?php /**/ ?>
<?php
			#$pdf_url = get_post_meta( get_the_ID(), 'pdf', true );
			#if ( $pdf_url ) :
			$password = get_post_meta( get_the_ID(), 'password', true );
			if ( ! $password ) {
				$password = md5( get_the_ID() . time() );
				update_post_meta( get_the_ID(), 'password', $password );
			}
?>
			<a target="_blank" title="View the Web Ticket" class="btn-action btn-pill btn-invoice-action document-action" href="<?php echo home_url(); ?>/client/ticket/<?php echo esc_attr( $password ); ?>/">
				<span class="document-action-icon-web"></span> Web View
			</a>
			<a title="Download Ticket as PDF" class="btn-action btn-pill btn-invoice-action" href="<?php echo untrailingslashit( get_permalink() ) . '.pdf';#$pdf_url; ?>">
				<span class="invoice-action-icon pdf-icon">Download as PDF</span>
			</a>
			<?php #endif; ?>
			<a href="#" onclick="window.print(); return false" title="Print Ticket" class="btn-action btn-pill btn-invoice-action">
				<span class="invoice-action-icon print-icon">Print</span>
			</a>
		</div>
	</div>
</div>
