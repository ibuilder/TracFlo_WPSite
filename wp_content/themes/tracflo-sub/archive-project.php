<?php

if ( ! current_user_can( 'trac_edit_projects' ) ) {
	wp_safe_redirect( home_url( '/' ) );
	die;
}

global $wpdb;

$posttype = get_query_var('post_type');
$pto = get_post_type_object( $posttype );

get_header();
?>

<div id="sub-nav" class="do-not-print">
	<div class="wrap">
		<a class="button-primary" href="<?php echo esc_url( home_url( '/add-' . $pto->name . '/' ) ); ?>">+ New <?php echo esc_html( $pto->labels->singular_name ); ?></a>
	</div>
</div>

<div id="content">

	<div class="wrap">

		<div id="main" role="main">

			<header>

				<h1><?php echo $pto->labels->name; ?></h1>

				<?php $filter = ( empty($_GET['filter']) || 'archive' != $_GET['filter'] ) ? 'active' : 'archive'; ?>
				<div class="header-controls do-not-print clearfix">
					<div class="filter-menu" style="clear: left">
						<button title="Filter Projects" id="filter-projects-button" class="button js-filter-selector" aria-label="Filter projects" aria-haspopup="true" aria-expanded="false">
							<span class="js-label">
								<?php echo ( 'archive' == $filter ) ? 'Archived Projects' : 'Active Projects'; ?>
							</span>
							<svg class="icon" height="15px" version="1.1" viewBox="0 0 50 50" width="15px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><rect fill="none" height="50" width="50"/><polygon points="47.25,15 45.164,12.914 25,33.078 4.836,12.914 2.75,15 25,37.25 "/></svg>
						</button>
						<div role="menu" class="dropdown is-left js-filters" aria-labeledby="filter-project-button" aria-hidden="true">
							<a data-filter="active" class="js-bubble" role="menuitem" href="/<?php echo $pto->has_archive; ?>?filter=active">Active Projects</a>
							<a data-filter="active" class="js-bubble" role="menuitem" href="/<?php echo $pto->has_archive; ?>?filter=archive">Archived Projects</a>
						</div>
					</div>

					<a href="#" onclick="window.print(); return false" title="Print List" class="btn-action-print btn-action btn-pill btn-invoice-action">
						<span class="invoice-action-icon print-icon">Print</span>
					</a>
				</div>

			</header>

			<?php if (have_posts()) : ?>

			<?php do_action( 'patch/page_navi' ); ?>

			<div class="tableWrapper">
			<table class="table project-overview-table js-projects-table"><tbody class="has-clickable-table-rows">
				<tr>
					<th class="col-job_number">Project #</th>
					<th class="col-name">Name</th>
					<th class="col-meter col-meter-paymentstatus col-meter-key"><span></span> Paid</th>
					<th class="col-paid">Paid</th>
					<th class="col-balance">Balance</th>
					<th class="col-client">Client</th>
					<th class="col-tickets">TIX</th>
					<th class="col-cos">COs</th>
					<th class="col-start_date">Start</th>
					<th class="col-action"></th>
				</tr>
<?php
				while (have_posts()) : the_post();
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
						<strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong>
					</td>
					<td class="col-meter">
						<div class="meter-graph<?php echo $meter_class; ?>">
							<div class="meter-graph-fill" style="width:<?php echo $percent_balance; ?>%"></div>
						</div>
					</td>
					<td class="col-paid"><?php echo trac_money_format( empty($totals['paid']) ? 0 : $totals['paid'] ); ?></td>
					<td class="col-balance"><?php echo trac_money_format( empty($totals['balance']) ? 0 : $totals['balance'] ); ?></td>
					<td class="col-client"><?php echo ! empty($client->post_title) ? esc_html( $client->post_title ) : ''; ?></td>
					<td class="col-tickets"><a href="<?php echo esc_url( home_url( '/tickets/?pid=' . get_the_ID() ) ); ?>"><?php echo esc_html( $totals_ticket ); ?></a></td>
					<td class="col-cos"><a href="<?php echo esc_url( home_url( '/cos/?pid=' . get_the_ID() ) ); ?>"><?php echo esc_html( $totals_co ); ?></a></td>
					<td class="col-start_date"><?php echo esc_html( $start_date ? trac_date( strtotime($start_date) ) : '' ); ?></td>
					<td class="col-action">
						<div class="filter-menu filter-menu--right" style="clear: left">
							<button title="Actions" id="filter-project-button" class="button js-filter-selector" aria-label="Actions" aria-haspopup="true" aria-expanded="false">
								<span class="js-label">Actions</span>
								<svg class="icon" height="15px" version="1.1" viewBox="0 0 50 50" width="15px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><rect fill="none" height="50" width="50"/><polygon points="47.25,15 45.164,12.914 25,33.078 4.836,12.914 2.75,15 25,37.25 "/></svg>
							</button>
							<div role="menu" class="dropdown is-right js-filters" aria-labeledby="filter-project-button" aria-hidden="true">
								<a data-filter="active" class="js-bubble" role="menuitem" href="<?php the_permalink(); ?>?action=edit">Edit</a>
								<a role="menuitem" href="<?php the_permalink(); ?>?action=archive">Archive</a>
								<a role="menuitem" href="<?php the_permalink(); ?>?action=delete&confirm=<?php echo wp_create_nonce( 'delete_project_' . get_the_ID() ); ?>" onclick="return confirm(&quot;Are you sure you want to delete this project?&quot;)">Delete</a>
							</div>
						</div>
					</td>
				</tr>
				<?php endwhile; ?>
			</tbody></table>
			</div>

			<?php do_action( 'patch/page_navi' ); ?>

			<?php else : ?>
				<h4>No projects yet</h4>
			<?php endif; ?>

		</div>

	</div>

</div>

<?php get_footer(); ?>
