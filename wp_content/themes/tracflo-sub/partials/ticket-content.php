<div class="client-document-container preview">
<?php
if ( $fields ) :
	$project = $fields['project'];
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
						<?php if ( $users = get_field('manager', $project->ID) ) : ?>
						<span class="company-manager">
<?php
							$output = '';
							foreach ( $users as $user ) :
								$output .= $user['display_name'] . ', ';
							endforeach;
?>
							PM: <?php echo rtrim($output, ', '); ?>
						</span><br>
						<?php endif; ?>
						<?php if ( $users = get_field('foreman', $project->ID) ) : ?>
						<span class="company-foreman">
<?php
							$output = '';
							foreach ( $users as $user ) :
								$output .= $user['display_name'] . ', ';
							endforeach;
?>
							Foreman: <?php echo rtrim($output, ', '); ?>
						</span><br>
						<?php endif; ?>
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
						<?php if ( $client = get_field( 'client', $project->ID ) ) : ?>
						<strong><?php echo esc_html( $client->post_title ); ?></strong>
						<?php endif; ?>
						<?php if ( $address = get_field('address', $project->ID) ) : ?>
						<span class="company-address">
							<?php echo $address; ?>
						</span>
						<?php endif; ?>
					<?php /*

						<strong><?php echo esc_html( $client->post_title ); ?></strong>
						<span class="company-address">
							<?php the_field( 'address', $client->ID ); ?>
						</span>
					
*/ ?>
					</td>
				</tr>
			</tbody>
			</table>
		</div>
		<div class="client-doc-details tableWrapper">
			<table cellspacing="0" cellpadding="0" border="0">
			<tbody>
				<tr>
					<td class="label">Project</td>
					<td class="definition">
						<span class="project"><strong><?php echo esc_html( get_the_title( $fields['project'] ) ); ?></strong></span>
					</td>
				</tr>
				<?php if ( $project_number = get_field('number', $project->ID) ) : ?>
				<tr>
					<td class="label">Project #</td>
					<td class="definition">
						<span class="project-number"><?php echo esc_html( $project_number ); ?></span>
					</td>
				</tr>
				<?php endif; ?>
				<tr>
					<td class="label">Ticket #</td>
					<td class="definition">
						<span class="number"><?php echo esc_html( $number ); ?></span>
					</td>
				</tr>
				<tr>
					<td class="label">Submitted Date</td>
					<td class="definition"><?php echo get_the_date( 'm/d/y' ); ?></td>
				</tr>
				<tr>
					<td class="label">Work Date</td>
					<td class="definition">
						<span class="due-date">
<?php
						if ( ! empty($fields['date']) ) :
							echo trac_date( strtotime($fields['date']) );
							if ( ! empty($fields['date_end']) ) :
								echo " &ndash; " . trac_date( strtotime($fields['date_end']) );
							endif;
						else :
							echo "&mdash;";
						endif;
?>
						</span>
					</td>
				</tr>
				<tr>
					<td class="label">Subject</td>
					<td class="definition"><?php echo esc_html( $fields['subject'] ); ?></td>
				</tr>
				<tr class="description description-address-on-right">
					<td class="label">Work Description</td>
					<td class="definition"><?php echo $fields['description']; ?></td>
				</tr>
			</tbody>
			</table>
		</div>

		<?php if ( $files = get_field('files') ) : ?>
		<div class="TicketFiles">
<?php
			foreach ( $files as $file ) :
				$image_exts = ['jpg','jpeg','png','gif','webp'];
				$filetype = wp_check_filetype($file['file']);
				$image = wp_mime_type_icon( $filetype['type'] );
				if ( in_array($filetype['ext'], $image_exts) ) :
					$image = $file['file'];
				endif;
?>
				<div class="TicketFiles-item">
					<div class="TicketFiles-file">
						<a class="TicketFiles-link" href="<?php echo esc_url( $file['file'] ); ?>" title="Open file" target="_blank">
							<img class="TicketFiles-img" src="<?php echo esc_url($image); ?>" alt="Thumbnail">
						</a>
					</div>
					<div class="TicketFiles-name"><a class="TicketFiles-link" href="<?php echo esc_url( $file['file'] ); ?>" title="Open file" target="_blank">
						<?php echo esc_html( basename($file['file']) ); ?>
					</a></div>
					<div class="TicketFiles-content">
						<?php echo esc_html( $file['content'] ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

	</div>
<?php
	unset($fields['project']);
	unset($fields['date']);
	unset($fields['description']);
	$total = 0;
?>
	<div class="breakdowns breakdowns--breakdowns_labor">
		<h4>Labor Breakdown</h4>
<?php
		if ( ! empty($fields['breakdowns_labor']) ) :
			// Draft
			if ( ! has_term('', 'ticket_status') ) :
				foreach ( $fields['breakdowns_labor'] as &$breakdown ) :
					$breakdown['rate'] = false;
				endforeach;
			endif;
			$labor_obj = apply_filters( 'trac/breakdown/info/labor', $fields['breakdowns_labor'], ( ! empty($fields['markups_labor']) ? $fields['markups_labor'] : [] ) );

			if ( $labor_obj ) :
				$total += $labor_obj->total;
				do_action( 'trac/breakdown/output', $labor_obj );
			endif;
		endif;
?>
	</div>

	<?php
	if ( ! empty($fields['breakdowns_material']) ) :
		$material_obj = apply_filters( 'trac/breakdown/info/nonlabor', $fields['breakdowns_material'], ( ! empty($fields['markups_material']) ? $fields['markups_material'] : [] ) );
		if ( $material_obj ) :# && $material_obj->total
?>
		<div class="breakdowns breakdowns--breakdowns_material">
			<h4>Material Breakdown</h4>
<?php
			$total += $material_obj->total;
			do_action( 'trac/breakdown/output', $material_obj );
?>
		</div>
	<?php endif; endif; ?>

	<?php
	if ( ! empty($fields['breakdowns_equipment']) ) :
		$equipment_obj = apply_filters( 'trac/breakdown/info/nonlabor', $fields['breakdowns_equipment'], ( ! empty($fields['markups_equipment']) ? $fields['markups_equipment'] : [] ) );
		if ( $equipment_obj ) :# && $equipment_obj->total
?>
		<div class="breakdowns breakdowns--breakdowns_equipment">
			<h4>Equipment Breakdown</h4>
<?php
			$total += $equipment_obj->total;
			do_action( 'trac/breakdown/output', $equipment_obj );
?>
		</div>
	<?php endif; endif; ?>

	<?php if ( $total ) : ?>
	<div class="totalFull">
		Total: <?php echo esc_html( trac_money_format( $total ) ); ?>
	</div>
	<?php endif; ?>
<?php
endif;
// end preview
?>
</div>
