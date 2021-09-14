<div id="activity_log tableWrapper">
	<h2 id="activity_log_header">Change Order History</h2>
	<table class="activity-items" cellpadding="0" cellspacing="0" border="0">
<?php
		if ( $full_history ) :
			foreach ( $full_history as $history ) :
				$user      = ! empty($history['user']) ? get_the_author_meta( 'display_name', $history['user'] ) : '';
				$date_paid = ! empty($history['date_paid']) ? date_i18n( 'm/d/Y \a\t g:i a', $history['date_paid'] ) : '';
				$date      = ! empty($history['date']) ? date_i18n( 'm/d/Y \a\t g:i a', $history['date'] ) : '';
				$amount    = ! empty($history['amount']) ? trac_money_format( $history['amount'] ) : 0;
				$file      = ! empty($history['file']) ? wp_get_attachment_url( $history['file'] ) : null;
				$note      = apply_filters( 'trac/history/note', $history );
?>
		<tr>
			<td class="notes">
				<?php echo $note; ?>
				<span class="meta">
					<strong class="sent-by"><?php echo esc_html( $user ); ?></strong> on <?php echo esc_html( $date ); ?>
				</span>
				<?php if ( $file ) : ?><div class="file-link"><a href="<?php echo $file; ?>" target="_blank">View Purchase Order</a>.</span><?php endif; ?>
			</td>
			<?php if ( $amount ) : ?>
				<td class="control">
					<strong><?php echo esc_html( $amount ); ?></strong>
					<?php if ( ! empty($history['id']) && ! empty($history['date_paid']) ) : ?>
						<a data-mconfirm-header="Remove Payment?" data-href-method="delete" data-mconfirm-text="This will remove the payment record on <?php echo esc_html( $date_paid ); ?> for <?php echo esc_html( $amount ); ?>." title="Remove Payment" class="delete delete-payment" href="<?php the_permalink(); ?>?action=deletepayment&id=<?php echo esc_attr( $history['id'] ); ?>">Delete</a>
					<?php endif; ?>
				</td>
			<?php elseif ( ! empty($history['note']) && 'complete' === $history['note'] ) : ?>
				<td class="control">
					<?php if ( ! empty($history['id']) ) : ?>
						<a data-mconfirm-header="Remove Complete Status?" data-href-method="delete" data-mconfirm-text="This will remove completed status." title="Remove Complete" class="delete delete-payment" href="<?php the_permalink(); ?>?action=deletecomplete&id=<?php echo esc_attr( $history['id'] ); ?>">Delete</a>
					<?php endif; ?>
				</td>
			<?php else : ?>
				<td class="control">&nbsp;</td>
			<?php endif; ?>
		</tr>
<?php
			endforeach;
		endif;
?>
	</table>
</div>
