<div class="chrome client-edit-header">
	<div class="pull-left">
		<?php if ( ! $closed ) : ?>
			<a class="btn-action btn-chrome" href="<?php the_permalink(); ?>?action=edit">Edit Change Order</a>
			<img id="loading-ajax" style="display:none;margin-bottom:-5px;" src="<?php echo get_template_directory_uri(); ?>/assets/img/loading.gif" alt="Loading">
			<a class="btn-action btn-chrome" href="<?php the_permalink(); ?>?action=close">Close Change Order</a>
		<?php else : ?>
			<a class="btn-action btn-chrome" href="<?php the_permalink(); ?>?action=open">Open Change Order</a>
		<?php endif; ?>
		<a class="btn-action btn-chrome delete-post" href="?action=delete&confirm=<?php echo wp_create_nonce( 'delete_co_' . get_the_ID() ); ?>" onclick="return confirm(&quot;Are you sure you want to delete this change order?&quot;)">Delete</a>
	</div>
	<div id="invoice-pay-area" class="pull-right">
		<?php if ( ! $complete ) : ?>
			<a id="complete_button" class="btn-action btn-chrome toggle-complete" href="<?php the_permalink(); ?>?action=complete" style="margin-left:5px;">Work Completed</a>
		<?php else: ?>
			<div class="coComplete coComplete--complete"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 12"><path d="M10 1L4.5 7 2 4.6l-2 2L4.4 11 12 3l-2-2z"/></svg></div>
		<?php endif; ?>
		<?php if ( ! $closed ) : ?>
			<a id="record_payment_button" rel="payment_form" class="btn-action btn-chrome toggle-client-form" href="<?php the_permalink(); ?>?action=payment">Record Payment</a>
		<?php endif; ?>
		<h3>Balance: <strong><?php echo esc_html( trac_money_format( $balance ) ); ?></strong></h3>
		<h3>Total Paid: <strong><?php echo esc_html( trac_money_format( $paid ) ); ?></strong></h3>
	</div>
</div>
