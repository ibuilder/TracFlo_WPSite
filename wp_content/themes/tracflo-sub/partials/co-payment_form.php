<div id="payment_form" class="inline_form inline-chrome-form inline-chrome-payment-form do-not-print" rel="invoice-chrome-forms" style="display:none">
<div class="span-10">
	<form class="form label-right" action="<?php the_permalink(); ?>?action=addpayment" accept-charset="UTF-8" method="post" enctype="multipart/form-data">
		<input name="utf8" type="hidden" value="✓">
		<input type="hidden" name="authenticity_token" value="IIbPDl0wzhzXaQbSw5n2XKeqYRd0M/9CLGHErujqOwCzWJQVY8vMpmKSejbyfiYnB+aW2RTyU02yAGh6w0HV7Q==">
		<div class="form-field">
			<label class="form-label required">Payment Date</label>
			<div class="form-inputs">
				<input value="<?php echo esc_attr( date_i18n( TRAC_DATE_FORMAT ) ); ?>" size="10" class="date" autocomplete="off" data-datepicker="true" data-isodate="true" type="text" name="payment[paid_at]" id="payment_paid_at">
			</div>
		</div>
		<div class="form-field">
			<label class="form-label">Close Change Order?</label>
			<div class="form-inputs">
				<input type="checkbox" name="payment[close]" id="payment_close" checked="checked">
				<span class="description">Accept this payment as final</span>
			</div>
		</div>
		<div class="form-field">
			<label class="form-label">Purchase Order #</label>
			<div class="form-inputs">
				<input value="" size="20" class="date" autocomplete="off" data-datepicker="true" data-isodate="true" type="text" name="payment[po]" id="payment_po">
			</div>
		</div>
		<div class="form-field">
			<label class="form-label">Purchase Order Upload</label>
			<div class="form-inputs">
				<input value="" type="file" name="payment_file" id="payment_file">
			</div>
		</div>
		<div class="form-field">
			<label class="form-label required">Amount ($)</label>
			<div class="form-inputs">
				<input size="10" value="<?php echo esc_html( trac_money_format( $owed_total ) ); ?>" <?php /* onchange="invoice.format_field(this)" */ ?> type="text" name="payment[amount]" id="payment_amount">
			</div>
		</div>
		<div class="form-field">
			<label class="form-label">Notes</label>
			<div class="form-inputs">
				<textarea name="payment[notes]" id="payment_notes_field"></textarea>
			</div>
		</div>
		<div id="payment_apply_retainer"></div>
		<div class="btn-submit-container form-submit-toggle">
			<input type="submit" name="commit" value="Save Payment" id="save-payment-button" <?php /* onclick="return invoice.payment.valid();" */ ?> class="btn-submit btn-primary">
			<a class="btn-submit toggle-cancel-form" href="#">Cancel</a>
			<div class="loader" style="display:none;">Loading...</div>
		</div>
	</form>
</div>
</div>
