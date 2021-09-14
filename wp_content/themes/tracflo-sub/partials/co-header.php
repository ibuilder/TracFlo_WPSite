<div id="invoice_header" class="clearfix">
	<div class="grid2of3">
		<h1>Change Order <?php echo esc_html( $number ); ?></h1>
		<div class="subject-message">
			<strong>Subject</strong>: <?php echo esc_html( $subject ); ?>
		</div>
		<div class="activity-message">
<?php
			$user      = ! empty($latest_history['user']) ? get_the_author_meta( 'display_name', $latest_history['user'] ) : '';
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
			$pdf_link = untrailingslashit( get_permalink( get_the_ID() ) ) . '.pdf';// get_post_meta( get_the_ID(), 'pdf', true );
			if ( empty($_GET['action']) ) :
?>
			<a title="Download Invoice as PDF" class="btn-action btn-pill btn-invoice-action" href="<?php echo $pdf_link; //?up=" . current_time('timestamp'); ?>">
				<span class="invoice-action-icon pdf-icon">Download as PDF</span>
			</a>
			<?php endif; ?>
			<a href="#" onclick="window.print(); return false" title="Print Invoice" class="btn-action btn-pill btn-invoice-action">
				<span class="invoice-action-icon print-icon">Print</span>
			</a>
		</div>
	</div>
</div>
