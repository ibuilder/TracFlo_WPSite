<div class="chrome client-edit-header">
	<div class="pull-left">
		<?php if ( ! $closed && ! $locked ) : ?>
			<a class="btn-action btn-primary" href="<?php the_permalink(); ?>?action=send" id="send_ticket_button">
				<?php if ( has_term( 'submitted', 'ticket_status' ) ) : ?>
					Resend Ticket
				<?php else : ?>
					Send Ticket
				<?php endif; ?>
			</a>
<?php
			if (
				(current_user_can( 'trac_edit_rates' ) && has_term('', 'ticket_status') && ! has_term(['approvedtm','approve','submitted'], 'ticket_status'))
				|| ((current_user_can( 'trac_foreman' ) || current_user_can( 'trac_admin' ) || current_user_can( 'trac_pm' )) && ! has_term('', 'ticket_status') && ! has_term(['approvedtm','approve','submitted'], 'ticket_status'))
			) : ?>
			<a class="btn-action btn-chrome" href="<?php the_permalink(); ?>?action=edit">Edit Ticket</a>
			<a class="btn-action btn-chrome delete-post" href="?action=delete&confirm=<?php echo wp_create_nonce( 'delete_ticket_' . get_the_ID() ); ?>" onclick="return confirm(&quot;Are you sure you want to delete this ticket?&quot;)">Delete</a>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
<?php include( locate_template( 'partials/ticket-send_form.php' ) ); ?>
