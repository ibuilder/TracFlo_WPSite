<?php

// Get payments with meta_id
$number   = get_post_meta( get_the_ID(), 'number', true );
$subject  = get_post_meta( get_the_ID(), 'subject', true );//get_field('');
$closed   = has_term( 'closed', 'co_status' );
$complete = has_term( 'complete', 'co_status' );

if ( empty($print_pdf) ) {
	$full_history   = apply_filters( 'trac/history/full', get_the_ID() );#array_reverse( $full_history );
	$latest_history = reset( $full_history );

	acf_form_head();
	get_header();
}
?>
<div id="content">

	<div class="wrap">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div id="main" role="main">
<?php
			if ( empty($print_pdf) ) :
				include( locate_template( 'partials/co-header.php' ) );
			endif;

			if ( empty($print_pdf) && ! empty($_REQUEST['action']) && 'edit' == $_REQUEST['action'] ) :
				acf_form([
					'field_groups' => [ 'tracflo-change-order' ],
					'return'       => add_query_arg( 'update', 'co', get_permalink() ),
					'submit_value' => 'Update Change Order',
				]);
			else :
				$owed_total = get_post_meta( get_the_ID(), 'total', true );
				$paid       = get_post_meta( get_the_ID(), 'paid_total', true );
				$balance    = $owed_total - $paid;
?>
				<div id="new_client_view_shell" class="client-shell-in-app">
<?php
					if ( empty($print_pdf) ) :
						include( locate_template( 'partials/co-chrome.php' ) );
						include( locate_template( 'partials/co-payment_form.php' ) );
					endif;
?>
					<div class="client-document-container preview">
<?php
					$fields = get_fields();
					if ( $fields ) :
?>
						<div id="client-document-status" class="client-doc-header">
							<div class="client-doc-name">
								<img alt="Logo for <?php echo trac_option( 'name' ); ?>" src="<?php echo trac_option( 'logo' ); ?>">
							</div>
							<div class="Addresses tableWrapper">
								<table cellspacing="0" cellpadding="0" border="0">
								<tbody>
									<tr class="Addresses-address Addresses-from">
										<td class="Addresses-label">
											From
										</td>
										<td class="Addresses-content">
											<strong><?php echo trac_option( 'name' ); ?></strong><br>
											<span class="company-address">
												<?php echo trac_option( 'address' ); ?>
												<br><br>
												<?php echo trac_option( 'phone' ); ?><br>
											</span>
										</td>
									</tr>
									<tr class="Addresses-address Addresses-for">
										<td class="Addresses-label">
											For
										</td>
										<td class="Addresses-content">
<?php
											$project = $fields['project'];
											if ( $client = get_field( 'client', $project->ID ) ) :
?>
												<strong><?php echo esc_html( $client->post_title ); ?></strong>
												<span class="company-address">
													<?php the_field( 'address', $client->ID ); ?>
												</span>
											<?php endif; ?>
										</td>
									</tr>
								</tbody>
								</table>
							</div>

							<div class="client-doc-details tableWrapper">
								<table cellspacing="0" cellpadding="0" border="0">
								<tbody>
									<tr>
										<?php $project_title = get_the_title( $fields['project'] ); ?>
										<td class="label">Project</td>
										<td class="definition">
											<span class="project"><strong><?php echo esc_html( $project_title ); ?></strong></span>
										</td>
									</tr>
									<tr>
										<td class="label">CO Number</td>
										<td class="definition">
											<span class="number"><?php echo esc_html( $number ); ?></span>
										</td>
									</tr>
									<?php if ( $number_client = get_post_meta( get_the_ID(), 'number_client', true ) ) : ?>
									<tr>
										<td class="label">Project Number</td>
										<td class="definition">
											<span class="number_client"><?php echo esc_html( $number_client ); ?></span>
										</td>
									</tr>
									<?php endif; ?>
									<tr>
										<td class="label">Submitted Date</td>
										<td class="definition"><?php echo get_the_date(); ?></td>
									</tr>
									<tr>
										<td class="label">Date</td>
										<td class="definition">
											<span class="due-date"><?php echo ( ! empty($fields['date']) ) ? trac_date( strtotime($fields['date']) ) : "&mdash;"; ?></span>
										</td>
									</tr>
								</tbody>
								</table>
							</div>

							<?php if ( ! empty($fields['rfp']) || ! empty($fields['description']) || ! empty($fields['exclusion']) ) : ?>
							<div class="client-doc-notes tableWrapper">
								<table cellspacing="0" cellpadding="0" border="0">
								<tbody>
									<?php if ( ! empty($fields['rfp']) ) : ?>
									<tr class="rfp rfp-address-on-right">
										<td class="label">RFP</td>
										<td class="definition"><a href="<?php echo esc_url( $fields['rfp'] ); ?>">Download</a></td>
									</tr>
									<?php endif; ?>
									<?php if ( ! empty($fields['description']) ) : ?>
									<tr class="rfp rfp-address-on-right">
										<td class="label">Scope of Work</td>
										<td class="definition">
											<?php echo $fields['description']; ?>
										</td>
									</tr>
									<?php endif; ?>
									<?php if ( ! empty($fields['exclusion']) ) : ?>
									<tr class="rfp rfp-address-on-right">
										<td class="label">Exclusion</td>
										<td class="definition">
											<?php echo $fields['exclusion']; ?>
										</td>
									</tr>
									<?php endif; ?>
								</tbody>
								</table>
							</div>
							<?php endif; ?>
						</div>
<?php
					unset($fields['project']);
					unset($fields['date']);
					unset($fields['rfp']);
					unset($fields['description']);
					unset($fields['exclusion']);
					$total = 0;


					if ( 'sum' == $fields['type'] ) :
						if ( ! empty($fields['breakdowns_labor']) && $labor_obj = apply_filters( 'trac/breakdown/info/labor', $fields['breakdowns_labor'], $fields['markups_labor'] ) ) :
?>
						<div class="breakdowns breakdowns--breakdowns_labor">
							<h4><strong>Labor</strong></h4>
<?php
							$total += $labor_obj->total;
							do_action( 'trac/breakdown/output', $labor_obj );
?>
						</div>
						<?php endif; ?>

						<?php if ( ! empty($fields['breakdowns_material']) && $material_obj = apply_filters( 'trac/breakdown/info/nonlabor', $fields['breakdowns_material'], $fields['markups_material'] ) ) :
							if ( $material_obj->total ) : ?>
						<div class="breakdowns breakdowns--breakdowns_material">
							<h4><strong>Material</strong></h4>
<?php
							$total += $material_obj->total;
							do_action( 'trac/breakdown/output', $material_obj );
?>
						</div>
						<?php endif; endif; ?>

						<?php if ( ! empty($fields['breakdowns_equipment']) && $equipment_obj = apply_filters( 'trac/breakdown/info/nonlabor', $fields['breakdowns_equipment'], $fields['markups_equipment'] ) ) :
							if ( $equipment_obj->total ) : ?>
						<div class="breakdowns breakdowns--breakdowns_equipment">
							<h4><strong>Equipment</strong></h4>
<?php
							$total += $equipment_obj->total;
							do_action( 'trac/breakdown/output', $equipment_obj );
?>
						</div>
						<?php endif; endif; ?>

<?php
					elseif ( 'total' == $fields['type'] ) :
						$total = get_post_meta( get_the_ID(), 'manual_total', true );
						if ( ! empty($fields['breakdowns_labor']) && $labor_obj = apply_filters( 'trac/breakdown/info/labor', $fields['breakdowns_labor'] ) ) :
?>
						<div class="breakdowns breakdowns--breakdowns_labor">
							<h4><strong>Labor</strong></h4>
<?php
							do_action( 'trac/breakdown/output', $labor_obj );
?>
						</div>
						<?php endif; ?>

						<?php if ( ! empty($fields['breakdowns_material']) && $material_obj = apply_filters( 'trac/breakdown/info/nonlabor', $fields['breakdowns_material'] ) ) :
							#if ( $material_obj->total ) : ?>
						<div class="breakdowns breakdowns--breakdowns_material">
							<h4><strong>Material</strong></h4>
<?php
							do_action( 'trac/breakdown/output', $material_obj );
?>
						</div>
						<?php endif; #endif; ?>

						<?php if ( ! empty($fields['breakdowns_equipment']) && $equipment_obj = apply_filters( 'trac/breakdown/info/nonlabor', $fields['breakdowns_equipment'] ) ) :
							#if ( $equipment_obj->total ) : ?>
						<div class="breakdowns breakdowns--breakdowns_equipment">
							<h4><strong>Equipment</strong></h4>
<?php
							do_action( 'trac/breakdown/output', $equipment_obj );
?>
						</div>
						<?php endif; #endif; ?>


					<?php elseif ( 'time' == $fields['type'] && ! empty($fields['tickets']) ) : ?>

						<div class="tableWrapper">
						<table class="table project-overview-table js-projects-table">
						<tbody class="has-clickable-table-rows">
							<tr>
								<th class="col-name"><strong>Ticket #</strong></th>
								<th class="col-date"><strong>Work Date</strong></th>
								<th class="col-submitted"><strong>Submitted</strong></th>
								<th class="col-total"><strong>Total</strong></th>
							</tr>
			
							<?php foreach ( $fields['tickets'] as $post ) : setup_postdata( $post );
								$date = get_field('date');
								$subtotal = ( $amount = get_post_meta( $post->ID, 'total', true ) ) ? $amount : 0;
								$total += $subtotal; ?>
			
								<tr>
									<td class="col-name">
										<strong><a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>"><?php echo esc_html( get_the_title( $post->ID ) ); ?></a></strong>
									</td>
									<td class="col-date"><?php echo $date ? esc_html( trac_date( strtotime($date) ) ) : ''; ?></td>
									<td class="col-submitted"><?php echo esc_html( get_the_date( TRAC_DATE_FORMAT, $post->ID ) ); ?></td>
									<td class="col-total"><?php echo esc_html( trac_money_format( $subtotal ) ); ?></td>
								</tr>
			
							<?php endforeach; wp_reset_postdata(); ?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="3"><strong>Total</strong></td>
								<td><strong><?php echo esc_html( trac_money_format( $total ) ); ?></strong></td>
							</tr>
						</tfoot>
						</table>
						</div>

					<?php endif; ?>

					<section class="breakdowns-total">
						Subtotal: <?php echo esc_html( trac_money_format( $total ) ); ?>
					</section>

					<?php if ( $closed ) : ?>
						<div class="totalFull">
							Accepted Amount: <?php echo esc_html( trac_money_format( $paid ) ); ?>
						</div>
					<?php else : ?>
						<section class="breakdowns-total">
							Payments: <?php echo esc_html( trac_money_format( $paid ) ); ?>
						</section>
						<div class="totalFull">
							<strong>Total Due: <?php echo esc_html( trac_money_format( ($balance < 0 ? 0 : $balance) ) ); ?></strong>
						</div>
					<?php endif; ?>
					
<?php
				endif;
				// end preview
?>
				</div>
				</div>
<?php
				if ( empty($print_pdf) ) :
					do_action( 'trac/history/log', get_the_ID() );
				endif;
			endif;
?>
		</div>
		<?php endwhile; endif; ?>

	</div>

</div>

<?php if ( empty($print_pdf) ) { get_footer(); }
