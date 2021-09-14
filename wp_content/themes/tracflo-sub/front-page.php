<?php
// Template Name: Home

// Redirect Foreman to Tickets
$current_user = wp_get_current_user();
if ( in_array( 'trac_foreman', (array) $current_user->roles ) ) {
	wp_safe_redirect( home_url( '/tickets/' ) );
	die;
}

get_header();
?>
<div id="content">

	<div class="wrap">

		<div id="main" role="main">

			<article <?php post_class( 'cf' ); ?>>

				<header class="dashboard-header do-not-print">
					<h1 class="page-title">Dashboard<?php #the_title(); ?></h1>
					<?php if ( current_user_can( 'trac_edit_projects' ) ) : ?>
					<a href="#" onclick="window.print(); return false" title="Print List" class="pull-left btn-action-print btn-action btn-pill btn-invoice-action">
						<span class="invoice-action-icon print-icon">Print</span>
					</a>
					<?php endif; ?>
					<div class="header-controls">
						<?php if ( current_user_can( 'trac_edit_cos' ) ) : ?>
							<a class="button" href="<?php echo home_url( '/add-co/' ); ?>">+ Add Change Order</a>
						<?php endif; ?>
						
						<?php if ( current_user_can( 'trac_edit_tickets' ) ) : ?>
							<a class="button" href="<?php echo home_url( '/add-ticket/' ); ?>">+ Add Ticket</a>
						<?php endif; ?>
					</div>
				</header>
				<!-- svg class="overall-chart" width="960" height="500"></svg -->
<?php /** / ?>
				<section class="entry-content" itemprop="articleBody">
					<?php the_content(); ?>
					<section class="summary module" style="float: right; width: 25%;">
						<h4>Change Order Summary</h4>
<table id="tbl-invoicesummary" class="table tbl tbl-invoicesummary">
    <tbody><tr>
      <td class="big_link">
        <a href="#">
          <span class="type-smallgrey">Amount Outstanding</span>
          <span class="type-cost type-red">$31,842.40</span>
          <span class="type-note-black">(8 change orders)</span>
</a>      </td>
    </tr>
    <tr>
      <td class="big_link">
        <a href="#">
          <span class="type-smallgrey">Amount Invoiced This Month</span>
          <span class="type-cost type-red">$20,475.00</span>
          <span class="type-note-black">(8 change orders)</span>
</a>      </td>
    </tr>
    <tr>
      <td class="big_link">
        <a href="#">
          <span class="type-smallgrey">Payments Received Last Month</span>
          <span class="type-cost">$12,167.50</span>
</a>      </td>
    </tr>
    <tr>
      <td class="big_link">
          <a href="#">
            <span class="type-smallgrey">Payments Settled Year-to-Date</span>
            <span class="type-cost">$35,888.75</span>
</a>      </td>
    </tr>
  </tbody></table>
					</section>
<?php /**/ ?>

				<section class="dashboard-projects-active module">
				<?php if ( current_user_can( 'trac_edit_projects' ) ) : ?>
				<?php
				$page = max( 1, get_query_var('page') );
				$project_query = new WP_Query([
					'order' => 'ASC',
					'orderby' => 'title',
					'paged' => $page,
					'post_type' => 'project',
					'posts_per_page' => 50,
					'post_status' => 'publish',
					'tax_query', [
						[
							'taxonomy' => 'project_status',
							'field'    => 'slug',
							'terms'    => [ 'archive' ],
							'operator' => 'NOT IN',
						],
					],
				]);
				if ($project_query->have_posts()) :
					ob_start();
					do_action( 'patch/page_navi', $project_query, $page );
					$pagination = ob_get_clean(); ?>

					<h4>Active Projects</h4>

					<?php echo $pagination; ?>

					<div class="tableWrapper">
					<table class="table project-overview-table js-projects-table"><tbody class="has-clickable-table-rows">
						<tr>
							<th class="col-job_number">Project #</th>
							<th class="col-name">Project</th>
							<th class="col-meter col-meter-paymentstatus col-meter-key"><span></span> Paid</th>
							<th class="col-paid">Paid</th>
							<th class="col-balance">Balance</th>
							<th class="col-client">Client</th>
						</tr>
		<?php
						while ($project_query->have_posts()) : $project_query->the_post();
							$client          = get_field('client');
							$start_date      = get_field('start_date');
							$totals_co       = get_post_meta( get_the_ID(), 'totals_co', true );
							$totals_ticket   = get_post_meta( get_the_ID(), 'totals_ticket', true );
							$totals          = get_post_meta( get_the_ID(), 'totals', true );

							// Set up the meter graph
							$meter_class = '';
							if ( empty($totals['total']) ) {
								$meter_class .= ' is-empty';
							} elseif ( ! empty($totals['balance']) ) {
								$meter_class .= ' is-balance';
							}
							$percent_balance = ( empty($totals['paid']) || empty($totals['total']) ) ? 0 : $totals['paid'] / $totals['total'] * 100;
		?>
						<tr>
							<td class="col-job_number"><?php esc_html( the_field('number') ); ?></td>
							<td class="col-name">
								<strong><a href="<?php echo esc_url( home_url('/cos/?pid=' . get_the_ID()) ); ?>"><?php the_title(); ?></a></strong>
							</td>
							<td class="col-meter">
								<div class="meter-graph<?php echo $meter_class; ?>">
									<div class="meter-graph-fill" style="width:<?php echo $percent_balance; ?>%"></div>
								</div>
							</td>
							<td class="col-paid"><?php echo trac_money_format( empty($totals['paid']) ? 0 : $totals['paid'] ); ?></td>
							<td class="col-balance"><?php echo trac_money_format( empty($totals['balance']) ? 0 : $totals['balance'] ); ?></td>
							<td class="col-client"><?php echo ! empty($client->post_title) ? esc_html( $client->post_title ) : ''; ?></td>
						</tr>
						<?php endwhile; ?>
					</tbody></table>
					</div>

					<?php echo $pagination; ?>

					<?php else : ?>
						<h4>No items yet</h4>
				<?php endif; wp_reset_postdata(); ?>
				<?php endif; ?>
				</section>

				<section class="dashboard-recent-activity module">
					<div class="module-wrap" style="background:#f2f2f2;margin:0 0 50px -16px;padding: 16px;">
						<h4>Ball In Court (BIC)</h4>
						<div class="tableWrapper">
						<table class="table project-overview-table js-projects-table" style="border-top:1px solid #ccc;"><tbody class="has-clickable-table-rows">
<?php
							// The Query
							$bic_query = new WP_Query([
								'meta_key'   => 'bic',
								'meta_value' => get_current_user_id(),
								'order'      => 'DESC',
								'orderby'    => 'modified',
								'post_type'  => [ 'ticket', 'co' ],
							]);
							$count = 0;
							if ($bic_query->have_posts()) : while ($bic_query->have_posts()) : $bic_query->the_post();
?>
								<tr>
									<td class="col-name">
										<strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong>
									</td>
								</tr>
<?php
							$count++; endwhile;
							else :
?>
								<tr>
									<td class="col-name">
										<strong>All Clear</strong>
									</td>
								</tr>
<?php
							endif;
?>
						</tbody></table>
						</div>
					</div>

					<?php if ( current_user_can( 'trac_edit_projects' ) ) : ?>
					<div class="module-wrap">
						<h4>Recent Activity</h4>
<?php
						// Get the posts from the last 30 days only
						function filter_where( $where = '' ) {
							$where .= " AND post_modified > '" . date('Y-m-d', strtotime('-30 days')) . "'";
							return $where;
						}

						add_filter( 'posts_where', 'filter_where' );

						// The Query
						$modified_posts_query = new WP_Query([
							'order'          => 'DESC',
							'orderby'        => 'modified',
							'post_type'      => [ 'ticket', 'co' ],
							'posts_per_page' => '10',
						]);

						// The Loop
						if ( $modified_posts_query->have_posts() ) :
?>
							<div class="tableWrapper">
							<table class="table project-overview-table js-projects-table" style="border-top:1px solid #ccc;"><tbody class="has-clickable-table-rows">
								<!-- tr>
									<th class="col-name">Name</th>
								</tr -->
								<?php $count = 0; while ($modified_posts_query->have_posts()) : $modified_posts_query->the_post(); ?>
								<tr>
									<td class="col-name">
										<strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong>
									</td>
								</tr>
								<?php $count++; endwhile; ?>
							</tbody></table>
							</div>

						<?php else : ?>
							<h4>No recent activity</h4>
						<?php endif; ?>

					</div>
					<?php endif; ?>
				</section>

			</article>

		</div>

	</div>

</div>

<?php get_footer();
