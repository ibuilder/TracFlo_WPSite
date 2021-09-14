<div id="send_ticket_form" class="inline_form inline-chrome-form inline-chrome-payment-form do-not-print" rel="invoice-chrome-forms"<?php if ( empty($_GET['send']) ) : ?> style="display:none"<?php endif; ?>>
    <form class="form label-right" action="<?php the_permalink(); ?>?action=sendticket" accept-charset="UTF-8" method="post" enctype="multipart/form-data">
        <div class="hui-form-field clearfix">
            <div class="grid1of6">
                <label class="hui-label">Recipients</label>
            </div>
            <div class="grid4of6">
<?php
			$project = ( ! empty($fields['project']) ) ? $fields['project'] : null;
			$client  = ( $project ) ? get_field( 'client', $project->ID ) : null;
			if ( $project ) :
				if ( $client ) :
?>
				<h3><?php echo esc_html( $client->post_title ); ?></h3>
<?php
					$contacts = get_posts([
						'meta_query' => [
							[
								'name' => 'client',
								'value' => $client->ID,
							],
						],
						'order' => 'ASC',
						'orderby' => 'title',
						'post_type' => 'contact',
					]);
					if ( $contacts ) : foreach ( $contacts as $contact ) :
						$email  = get_field('email', $contact->ID);
?>
						<div class="hui-checkbox">
	                        <input type="checkbox" name="send-to-<?php echo esc_attr($contact->ID); ?>" id="send-to-<?php echo esc_attr($contact->ID); ?>"> <label for="send-to-<?php echo esc_attr($contact->ID); ?>"><?php echo esc_html($contact->post_title); ?> <span class="text-light">(<?php echo esc_html($email); ?>)</span></label>
	                    </div>
<?php
					endforeach; endif;
				endif;
?>
                <div class="hui-checkbox">
                    <input type="checkbox" name="message[send_me_a_copy]" id="message_send_me_a_copy" value="1"> <label for="message_send_me_a_copy">Send me a copy</label>
                </div>
                <?php if ( current_user_can( 'trac_edit_clients' ) ) : ?>
                <div class="mt-5 mb-5">
                    Sending to someone else? <a href="/add-contact/<?php echo ( ! empty($client->ID) ) ? '?cid=' . esc_attr($client->ID) : ''; ?>&return_to=<?php the_ID(); ?>&send=true">Add Contact</a>
                </div>
                <?php endif; ?>
				<?php if ( $pms = get_field('manager', $project->ID) ) : ?>
				<div style="padding-top: 1em">
					<h3>Project Manager</h3>
					<?php foreach ( $pms as $pm ) : ?>
					<div class="hui-checkbox">
	                    <input type="checkbox" name="send-pm-<?php echo esc_attr($pm['ID']); ?>" id="send-pm-<?php echo esc_attr($pm['ID']); ?>" checked> <label for="send-pm-<?php echo esc_attr($pm['ID']); ?>"><?php echo esc_html($pm['display_name']); ?> <span class="text-light">(<?php echo esc_html($pm['user_email']); ?>)</span></label>
	                </div>
	                <?php endforeach; ?>
				</div>
				<?php endif; ?>
<?php
/** /
	            $roles    = [ 'trac_pm' ];
				foreach ( $roles as $role_name ) :
					$title    = $GLOBALS['wp_roles']->roles[$role_name]['name'];
					if ( $users = get_users([ 'role__in' => $roles ]) ) :
?>
						<div style="padding-top: 1em">
							<h3><?php echo esc_html( $title ); ?></h3>
							<?php foreach ( $users as $user ) : ?>
							<div class="hui-checkbox">
		                        <input type="checkbox" name="send-to-<?php echo esc_attr($user->display_name); ?>" id="send-to-<?php echo esc_attr($user->ID); ?>"> <label for="send-to-<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name); ?> <span class="text-light">(<?php echo esc_html($user->user_email); ?>)</span></label>
		                    </div>
							<?php endforeach; ?>
						</div>
<?php
					endif;
				endforeach;
/**/
			endif;
?>
            </div>
        </div>
        <div class="hui-form-field clearfix">
            <div class="grid1of6">
                <label class="hui-label hui-label-medium" for="message_subject">Subject</label>
            </div>
            <div class="grid4of6">
                <input class="hui-input" value="Ticket #<?php echo esc_attr( $number ); ?> for <?php echo ( ! empty($project) ? esc_attr($project->post_title) : '' ); ?>, <?php echo ( ! empty($client) ? esc_attr($client->post_title) : '' ); ?>" required="required" type="text" name="message[subject]" id="message_subject">
            </div>
        </div>
        <div class="hui-form-field clearfix">
            <div class="grid1of6">
                <label class="hui-label hui-label-medium" for="message_body">Message</label>
            </div>
            <div class="grid4of6">
                <textarea class="hui-input" rows="10" name="message[body]" id="message_body">


A ticket requires your approval for time &amp; materials.

---------------------------------------------
Ticket Summary
---------------------------------------------
Project: <?php echo esc_html( get_the_title( $project->ID ) ); ?>
Project #<?php echo ( $project_number = get_field('number', $project->ID) ) ? esc_html( $project_number ) : "&mdash;";
if ( $users = get_field('manager', $project->ID) ) :
	$output = '';
	foreach ( $users as $user ) :
		$output .= $user['display_name'] . ', ';
	endforeach;
?> 
PM: <?php echo rtrim($output, ', ');
endif;
if ( $users = get_field('foreman', $project->ID) ) :
	$output = '';
	foreach ( $users as $user ) :
		$output .= $user['display_name'] . ', ';
	endforeach;
?> 
Foreman: <?php echo rtrim($output, ', ');
endif; ?> 
Ticket #<?php echo esc_attr( $number ); ?> 
Submitted date: <?php echo get_the_date( 'm/d/y' ); ?> 
Work date: <?php
						if ( ! empty($fields['date']) ) :
							echo trac_date( strtotime($fields['date']) );
							if ( ! empty($fields['date_end']) ) :
								echo " &ndash; " . trac_date( strtotime($fields['date_end']) );
							endif;
						else :
							echo "&mdash;";
						endif;
?> 
Client: <?php echo ( ! empty($client) ? esc_attr($client->post_title) : '' ); #The detailed ticket is attached as a PDF. ?>

Thank you!
---------------------------------------------
</textarea>
            </div>
        </div>
        <div class="hui-form-field clearfix">
            <div class="push1of6 grid4of6">
                <div class="hui-checkbox">
                    <input name="message[attach_pdf]" type="hidden" value="0"><input type="checkbox" value="1" checked="checked" name="message[attach_pdf]" id="message_attach_pdf"> <label for="message_attach_pdf">Include a PDF version of the ticket</label>
                </div>
                <div class="hui-checkbox">
                    <input name="message[include_link_to_client_invoice]" type="hidden" value="0"><input type="checkbox" value="1" checked="checked" name="message[include_link_to_client_invoice]" id="message_include_link_to_client_invoice"> <label for="message_include_link_to_client_invoice">Include link to Web Invoice for online payment</label>
                </div>
            </div>
        </div>
        <div class="hui-form-field-actions clearfix">
            <div class="push1of6 grid4of6">
                <input type="submit" name="commit" value="Send Ticket" id="submit-new-invoice-message-button" class="hui-button hui-button-primary" data-disable-on-submit="true" data-disable-with="Send Ticket"> <button type="button" class="hui-button hui-button-cancel toggle-cancel-form" data-hideform="send_invoice_form">Cancel</button> <span class="hui-spinner" data-form-spinner="" hidden=""> Sending…</span>
            </div>
        </div>
    </form>
</div>
