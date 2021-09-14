<?php
global $wp_query;

$number   = get_post_meta( get_the_ID(), 'number', true );
$taxonomy = 'ticket_status';
$tmstage  = ( ! has_term( '', $taxonomy ) || has_term( 'submitted', $taxonomy ) || has_term( 'revise', $taxonomy ) ) ? true : false;
$closed   = ( has_term( 'reject', $taxonomy ) || has_term( 'void', $taxonomy ) ) ? true : false;
$locked   = ( has_term( 'approverate', $taxonomy ) ) ? true : false;

if ( empty($print_pdf) ) {
	$full_history   = apply_filters( 'trac/history/full', get_the_ID() );#array_reverse( $full_history );
	$latest_history = reset( $full_history );

	acf_form_head();
	get_header();
	if ( $wp_query->ticket_public_view ) { do_action( 'sewn/notifications/show' ); } 
}
$fields = get_fields();
$password   = get_post_meta( get_the_ID(), 'password', true );
$token      = ( ! empty($wp_query->query_vars['ticket']) ) ? $wp_query->query_vars['ticket'] : null;
$contact_id = ( ! empty($wp_query->query_vars['contact_id']) ) ? $wp_query->query_vars['contact_id'] : null;
$contact_info = null;
if ( $contact_id ) {
	$password = $token;
	$contact_info = [
		'email' => get_field('email', $contact_id),
		'name'  => get_the_title( $contact_id ),
	];
}
?>

<div id="content">

	<div class="wrap">

		<div id="main" role="main">
<?php
		if ( empty($print_pdf) ) :
			if ( is_user_logged_in() && ! $wp_query->ticket_public_view ) :
				include( locate_template( 'partials/ticket-header.php' ) );
			else :
?>
			<div id="invoice_header" class="clearfix">
				<div class="grid2of3">
						<?php
/** /
$user      = ! empty($latest_history['user']) ? get_the_author_meta( 'display_name', $latest_history['user'] ) : '';
$date_paid = ! empty($latest_history['date_paid']) ? date_i18n( 'm/d/Y \a\t g:i a', $latest_history['date_paid'] ) : '';
$date      = ! empty($latest_history['date']) ? date_i18n( 'm/d/Y \a\t g:i a', $latest_history['date'] ) : '';
$amount    = ! empty($latest_history['amount']) ? trac_money_format( $latest_history['amount'] ) : 0;
$file      = ! empty($latest_history['file']) ? wp_get_attachment_url( $latest_history['file'] ) : null;
$note      = apply_filters( 'trac/history/note', $latest_history );
/** /
?>
			<table>
				<tr>
					<td class="notes">
						<?php echo $note; ?>
						<span class="meta">
							<strong class="sent-by"><?php echo esc_html( $user ); ?></strong> on <?php echo esc_html( $date ); ?>
						</span>
						<?php if ( $file ) : ?><div class="file-link"><a href="<?php echo $file; ?>" target="_blank">View Purchase Order</a>.</span><?php endif; ?>
					</td>
				</tr>
			</table>
<?php
/**/
						if ( has_term('', 'ticket_status') && ! has_term('submitted', 'ticket_status') ) :
							$terms = wp_get_post_terms( get_the_ID(), 'ticket_status' );
							if ( $terms ) :
								$term = $terms[0];
								$date = ( ! empty($latest_history['date']) ) ? date_i18n( 'm/d/Y \a\t g:i a', $latest_history['date'] ) : '';
?>
								<div class="pull-left public-status" style="font-weight: 500; height: auto; line-height: 34px; padding: 0 14px; text-align: left;">
									<p style="margin-bottom:0;">Status: <?php echo ( 'approvetm' === $term->slug ? '<span class="is-'. $term->slug . '">Approved Time &amp; Materials</span>' : $term->name ); ?> on <?php echo $date; ?></p>
									<?php if ( 'approvetm' === $term->slug && ! empty($latest_history) ) :
										$note = apply_filters( 'trac/history/note', $latest_history ); ?>
										<p class="signature-approved" style="margin-bottom:0;">
											<?php echo $note; ?>
											<br><span classs="is-small" style="color:red;">Signature acknowledges time and material used, but does not change contractual obligations of either party</span>
										</p>
										<style>
											.signature-approved {
												border: 1px solid red;
												margin: 10px 0 0 -10px;
												padding: 10px;
												width: 70%;
											}
										</style>
									<?php endif; ?>
								</div>
<?php
							endif;
						endif;
?>
				</div>
				<div class="grid1of3 pull-right">
					<div class="invoice-action-buttons">

						

						<div class="hui-button-toggle desktop-only pull-right">
<?php
							#$pdf_link = get_post_meta( get_the_ID(), 'pdf', true );
							#if ( $pdf_link ) :
?>
							<a title="Download Ticket as PDF" class="hui-button" href="<?php echo home_url(); ?>/client/ticket/<?php echo esc_attr( $token ? $token : $password ); ?>.pdf<?php #echo esc_url( $pdf_link ); ?>">PDF</a>
							<?php #endif; ?>
							<a href="#" onclick="window.print(); return false" type="button" title="Print Ticket" class="hui-button" data-print="">Print</a>
						</div>
					</div>
				</div>
			</div>

<?php if ( has_term('submitted', 'ticket_status') ) : ?>
<div class="chrome client-edit-header">
	<?php if ( $contact_info ) : ?>
	<div class="document-actions pull-right" style="margin-left: 12px;">
		<a id="approve-ticket" type="button" class="hui-button hui-button-primary">Approve</a>
		<a id="reject-ticket" type="button" class="hui-button">Reject</a>
		<a id="revise-ticket" type="button" class="hui-button">Revise &amp; Resubmit</a>
	</div>
	<?php endif; ?>
</div>

<?php if ( $contact_info ) : ?>
<div id="approve_ticket_form" class="inline_form inline-chrome-form inline-chrome-payment-form do-not-print" style="display:none">
	<form class="form label-right" action="<?php echo home_url( '/client/ticket/' . $password . '/' ); ?>?action=approveticket" accept-charset="UTF-8" method="post" enctype="multipart/form-data">
		<div class="hui-form-field clearfix">
			<div class="grid1of6">
				<label class="hui-label">&nbsp;</label>
			</div>
            <div class="grid4of6">
            	<h3>Approve Ticket: Time &amp; Materials</h3>
<!--             	<p>This approval only covers time &amp; materials. Cost approval is separate.</p> -->
            </div>
		</div>
        <div class="hui-form-field clearfix">
            <div class="grid1of6" style=" padding-top: 60px;">
                <label class="hui-label hui-label-medium" for="message_body" style="line-height: 1.2;">Signature</label>
            </div>
            <div class="grid4of6 signature-pad-wrap" style="padding-top: 60px;">
	            <p class="signature-disclaimer">Signature acknowledges time and material used, but does not change contractual obligations of either party</p>
                <div id="signature-pad" class="m-signature-pad" style="margin: 0; width: 90%">
					<input type="hidden" name="signature" value="" />
					<div class="m-signature-pad--body">
						<canvas></canvas>
					</div>
					<div class="m-signature-pad--footer">
						<?php /** / ?><div class="m-signature-pad--description">Sign above</div><?php /**/ ?>
						<a href="#clear" class="m-signature-pad--clear btn btn-default btn-xs button button-small" data-action="clear">Clear</a>
						<?php /** / ?><button class="save" data-action="save">Save</button><?php /**/ ?>
					</div>
				</div>
				<style>
					.signature-disclaimer {
						background: red;
						border: 2px solid red;
						padding: 10px;
						color:white;
						font-size: 1.21em;
						font-weight: bold;
						letter-spacing: 1px;
						margin: -60px 10px 0 0;
						text-transform: uppercase;
						width: 90%;
					}
					.m-signature-pad {
						height: 170px;
					}
					.m-signature-pad canvas {
						height: 135px;
					}
				</style>
            </div>
        </div>
        <div class="hui-form-field clearfix">
            <div class="grid1of6">
                <label class="hui-label hui-label-medium" for="message_name">Your Name</label>
            </div>
            <div class="grid4of6">
	            <input class="hui-input" type="text" name="message[name]" id="message_name" value="<?php echo ( ! empty($contact_info['name']) ? $contact_info['name'] : '' ); ?>" readonly="readonly">
            </div>
        </div>
        <div class="hui-form-field clearfix">
            <div class="grid1of6">
                <label class="hui-label hui-label-medium" for="message_email">Your Email</label>
            </div>
            <div class="grid4of6">
	            <input class="hui-input" type="text" name="message[email]" id="message_email" value="<?php echo ( ! empty($contact_info['email']) ? $contact_info['email'] : '' ); ?>" readonly="readonly">
            </div>
        </div>
        <div class="hui-form-field-actions clearfix">
            <div class="push1of6 grid4of6">
                <input type="submit" name="commit" value="Send Approval" id="submit-new-invoice-message-button" class="hui-button hui-button-primary" data-disable-on-submit="true" data-disable-with="Send Ticket"> <button type="button" class="hui-button hui-button-cancel toggle-cancel-form" data-hideform="send_invoice_form">Cancel</button> <span class="hui-spinner" data-form-spinner="" hidden=""> Sending…</span>
            </div>
        </div>
	</form>
</div>
<div id="reject_ticket_form" class="inline_form inline-chrome-form inline-chrome-payment-form do-not-print" style="display:none">
	<form class="form label-right" action="<?php echo home_url( '/client/ticket/' . $password . '/' ); ?>?action=rejectticket" accept-charset="UTF-8" method="post" enctype="multipart/form-data">
		<div class="hui-form-field clearfix">
			<div class="grid1of6">
				<label class="hui-label">&nbsp;</label>
			</div>
            <div class="grid4of6">
            	<h3>Reject Ticket</h3>
            </div>
		</div>
        <div class="hui-form-field clearfix">
            <div class="grid1of6">
                <label class="hui-label hui-label-medium" for="message_body" style="line-height: 1.2">Why is the ticket being rejected?</label>
            </div>
            <div class="grid4of6">
                <textarea class="hui-input" rows="4" name="message[body]" id="message_body"></textarea>
            </div>
        </div>
        <div class="hui-form-field clearfix">
            <div class="grid1of6">
                <label class="hui-label hui-label-medium" for="message_name">Your Name</label>
            </div>
            <div class="grid4of6">
	            <input class="hui-input" type="text" name="message[name]" id="message_name" value="<?php echo ( ! empty($contact_info['name']) ? $contact_info['name'] : '' ); ?>" readonly="readonly">
            </div>
        </div>
        <div class="hui-form-field clearfix">
            <div class="grid1of6">
                <label class="hui-label hui-label-medium" for="message_email">Your Email</label>
            </div>
            <div class="grid4of6">
	            <input class="hui-input" type="text" name="message[email]" id="message_email" value="<?php echo ( ! empty($contact_info['email']) ? $contact_info['email'] : '' ); ?>" readonly="readonly">
            </div>
        </div>
        <div class="hui-form-field-actions clearfix">
            <div class="push1of6 grid4of6">
                <input type="submit" name="commit" value="Send Rejection" id="submit-new-invoice-message-button" class="hui-button hui-button-primary" data-disable-on-submit="true" data-disable-with="Send Ticket"> <button type="button" class="hui-button hui-button-cancel toggle-cancel-form" data-hideform="send_invoice_form">Cancel</button> <span class="hui-spinner" data-form-spinner="" hidden=""> Sending…</span>
            </div>
        </div>
	</form>
</div>
<div id="revise_ticket_form" class="inline_form inline-chrome-form inline-chrome-payment-form do-not-print" style="display:none">
	<form class="form label-right" action="<?php echo home_url( '/client/ticket/' . $password . '/' ); ?>?action=reviseticket" accept-charset="UTF-8" method="post" enctype="multipart/form-data">
		<div class="hui-form-field clearfix">
			<div class="grid1of6">
				<label class="hui-label">&nbsp;</label>
			</div>
            <div class="grid4of6">
            	<h3>Request Ticket Revision</h3>
            </div>
		</div>
        <div class="hui-form-field clearfix">
            <div class="grid1of6">
                <label class="hui-label hui-label-medium" for="message_body" style="line-height: 1.2">Why does the ticket require revision?</label>
            </div>
            <div class="grid4of6">
                <textarea class="hui-input" rows="4" name="message[body]" id="message_body"></textarea>
            </div>
        </div>
        <div class="hui-form-field clearfix">
            <div class="grid1of6">
                <label class="hui-label hui-label-medium" for="message_name">Your Name</label>
            </div>
            <div class="grid4of6">
	            <input class="hui-input" type="text" name="message[name]" id="message_name" value="<?php echo ( ! empty($contact_info['name']) ? $contact_info['name'] : '' ); ?>" readonly="readonly">
            </div>
        </div>
        <div class="hui-form-field clearfix">
            <div class="grid1of6">
                <label class="hui-label hui-label-medium" for="message_email">Your Email</label>
            </div>
            <div class="grid4of6">
	            <input class="hui-input" type="text" name="message[email]" id="message_email" value="<?php echo ( ! empty($contact_info['email']) ? $contact_info['email'] : '' ); ?>" readonly="readonly">
            </div>
        </div>
        <div class="hui-form-field-actions clearfix">
            <div class="push1of6 grid4of6">
                <input type="submit" name="commit" value="Send Request" id="submit-new-invoice-message-button" class="hui-button hui-button-primary" data-disable-on-submit="true" data-disable-with="Send Ticket"> <button type="button" class="hui-button hui-button-cancel toggle-cancel-form" data-hideform="send_invoice_form">Cancel</button> <span class="hui-spinner" data-form-spinner="" hidden=""> Sending…</span>
            </div>
        </div>
	</form>
</div>
<?php endif;// contact_id ?>
			<?php endif; ?>
		<?php endif; ?>
<?php



			endif;

			if ( empty($print_pdf) ) :
				if ( ! empty($_REQUEST['action']) && 'edit' == $_REQUEST['action'] ) :
					if ( is_user_logged_in() && ! $wp_query->ticket_public_view ) :
						acf_form([
							'field_groups' => [ 'tracflo-tickets' ],
							'return'       => add_query_arg( 'update', 'ticket', get_permalink() ),
							'submit_value' => 'Update Ticket',
						]);
					endif;
				else :
?>
					<div id="new_client_view_shell" class="client-shell-in-app">
<?php
						if ( is_user_logged_in() && ! $wp_query->ticket_public_view ) : include( locate_template( 'partials/ticket-chrome.php' ) ); endif;
						include( locate_template( 'partials/ticket-content.php' ) );
?>
					</div>
<?php
				endif;

				if ( ! $wp_query->ticket_public_view ) :
					do_action( 'trac/history/log', get_the_ID() );
				endif;
			else :
?>
					<div id="new_client_view_shell" class="client-shell-in-app">
<?php
						include( locate_template( 'partials/ticket-content.php' ) );
?>
					</div>
<?php
			endif;
?>
		</div>

	</div>

</div>

<?php if ( empty($print_pdf) ) { get_footer(); }
