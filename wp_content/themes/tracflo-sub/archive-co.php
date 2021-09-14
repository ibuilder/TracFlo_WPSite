<?php

if ( ! current_user_can( 'trac_edit_cos' ) ) {
	wp_safe_redirect( home_url( '/' ) );
	die;
}

$posttype = get_query_var('post_type');
$pto = get_post_type_object( $posttype );

$current_project = null;
$current_year    = null;

/* Only TopRock * /
if ( 2 === get_current_blog_id() ) {
	if ( ! empty($_COOKIE['project']) ) {
		$current_project = get_post( esc_sql($_COOKIE['project']) );
	}
	if ( ! empty($_COOKIE['year']) ) {
		$current_year = $_COOKIE['year'];
	}
}
/**/

if ( ! empty($_GET['pid']) ) {
	$current_project = get_post( esc_sql($_GET['pid']) );
}
if ( ! empty($_GET['yid']) ) {
	$current_year = $_GET['yid'];
}

$current_tab     = ( empty($_GET['tab']) ) ? 'open' : $_GET['tab'];

$projects = get_posts([
	'post_type' => 'project',
	'posts_per_page' => -1,
]);

$total = $paid_total = $balance_total = 0;

$overall_defaults = [
	'total' => 0,
	'paid' => 0,
	'balance' => 0,
];
$overall_totals = [];
if ( $current_project ) {
	$overall_totals = get_post_meta( $current_project->ID, 'totals', true );
} else {
	$overall_totals = get_option( 'co_totals' );
}
$overall_totals = ( $overall_totals ) ? array_merge( $overall_defaults, $overall_totals ) : $overall_defaults;

if ( ! empty($_GET['export']) ) {
	include( apply_filters( 'trac/co/locate_template', 'archive-co-export.php' ) );
	die;
}

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

				<h1 style="clear:left; float:left; margin-bottom: 30px; width:auto;"><?php echo $pto->labels->name; ?><?php if ( ! empty($current_project) ) { echo ': ' . esc_html( $current_project->post_title ); } ?></h1>

				<div class="invoices-big-data grid1of4 pull-right">
					<div class="big-number amount-open">
						<h6><strong>Total Open<?php echo ( $current_project ) ? ' for ' . esc_html( $current_project->post_title ) : ''; ?></strong></h6>
						<h2><?php echo esc_html( trac_money_format( $overall_totals['balance'] ) ); ?></h2>
					</div>
					<div class="big-number amount-received">
						<h6><strong>Total Paid<?php echo ( $current_project ) ? ' for ' . esc_html( $current_project->post_title ) : ''; ?></strong></h6>
						<h2><?php echo esc_html( trac_money_format( $overall_totals['paid'] ) ); ?></h2>
					</div>
				</div>

				<div class="header-controls do-not-print clearfix">
					<div class="filter-menu" style="clear: left">
						<button title="Choose a Project" id="filter-project-button" class="button js-filter-selector" aria-label="Filter projects" aria-haspopup="true" aria-expanded="false">
							<span class="js-label">
								<?php echo ( $current_project ) ? esc_html( $current_project->post_title ) : 'Choose a Project'; ?>
							</span>
							<svg class="icon" height="15px" version="1.1" viewBox="0 0 50 50" width="15px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><rect fill="none" height="50" width="50"/><polygon points="47.25,15 45.164,12.914 25,33.078 4.836,12.914 2.75,15 25,37.25 "/></svg>
						</button>
						<div role="menu" class="dropdown is-left js-filters" aria-labeledby="filter-project-button" aria-hidden="true">
	<?php
						foreach ( $projects as $project ) :
							#global $wpdb;
							#$items = $wpdb->get_results( "SELECT COUNT(*) FROM $wpdb->posts WHERE `post_type` = 'co'" );
	?>
							<a data-filter="active" class="js-bubble" role="menuitem" href="<?php echo add_query_arg( 'pid', $project->ID ); /** / ?>/<?php echo $pto->has_archive; ?>?pid=<?php echo esc_attr( $project->ID ); /**/ ?>">
								<?php echo esc_html( $project->post_title ); ?>
							</a>
						<?php endforeach; ?>
							<a data-filter="active" class="js-bubble" role="menuitem" href="<?php echo add_query_arg( 'pid', 'reset' ); /** / ?>/<?php echo $pto->has_archive; ?>?pid=reset<?php /**/ ?>">Reset</a>
						</div>
					</div>
<?php
					// get years and if more than one, show this drop down with the years.
					// look for the oldest date in the meta and use that to build list since then.
					$years = ['2016', '2017', '2018'];
					// Check for cookie to see if a year is active
					if ( $years && 1 < count($years) ) :
?>
					<div class="filter-menu">
						<button title="Choose a Year" id="filter-year-button" class="button js-filter-selector" aria-label="Filter items by year" aria-haspopup="true" aria-expanded="false">
							<span class="js-label">
								<?php echo ( $current_year ) ? $current_year : 'Choose a Year'; ?>
							</span>
							<svg class="icon" height="15px" version="1.1" viewBox="0 0 50 50" width="15px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><rect fill="none" height="50" width="50"/><polygon points="47.25,15 45.164,12.914 25,33.078 4.836,12.914 2.75,15 25,37.25 "/></svg>
						</button>
						<div role="menu" class="dropdown is-left js-filters" aria-labeledby="filter-year-button" aria-hidden="true">
							<?php foreach ( $years as $year ) : ?>
							<a data-filter="active" class="js-bubble" role="menuitem" href="<?php echo add_query_arg( 'yid', $year ); /** / ?>/<?php echo $pto->has_archive; ?>?yid=<?php echo esc_attr($year); /**/ ?>">
								<?php echo esc_html($year); ?>
							</a>
							<?php endforeach; ?>
							<a data-filter="active" class="js-bubble" role="menuitem" href="<?php echo add_query_arg( 'yid', 'reset' ); /** / ?>/<?php echo $pto->has_archive; ?>?yid=reset<?php /**/ ?>">Reset</a>
						</div>
					</div>
					<?php endif; ?>

					<a href="#" onclick="window.print(); return false" title="Print List" class="btn-action-print btn-action btn-pill btn-invoice-action">
						<span class="invoice-action-icon print-icon">Print</span>
					</a>

					<a href="<?php echo add_query_arg( 'export', '1' ); ?>" title="Export List" class="btn-action-print btn-action btn-pill btn-invoice-action">
						<span class="invoice-action-icon">Export</span>
					</a>
				</div>
			</header>

			<div class="tabs-v4 js-tabs">
				<div class="wrapper" role="tablist">
					<a href="/<?php echo $pto->has_archive; ?>?tab=open" role="tab" class="tab js-tab-selector js-open-tab<?php echo ( 'open' === $current_tab ? ' is-active' : '' ); ?>" data-tab="open" aria-controls="open-co-tab" aria-expanded="true" aria-label="Open Change Orders" tabindex="-1">Open</a>
					<a href="/<?php echo $pto->has_archive; ?>?tab=all" role="tab" class="tab js-tab-selector<?php echo ( 'open' !== $current_tab ? ' is-active' : '' ); ?>" data-tab="all" aria-controls="all-co-tab" aria-expanded="false">All Change Orders</a>
				</div>
			</div>

			<?php if ( 'open' !== $current_tab ) :
				$extra_tabs = [
					'all'      => 'All COs',
					'paid'     => 'Paid COs',
					'po'       => 'Has PO#',
					'complete' => 'Complete',
				]; ?>
			<div class="js-filters-wrap filters-wrap<?php echo ( 'all' === $current_tab ? ' do-not-print' : '' ); ?> cf" style="padding-bottom: 2em;">
				<div class="filter-menu">
					<button title="Filter Change Orders" id="filter-co-button" class="button js-filter-selector" aria-label="Filter change orders" aria-haspopup="true" aria-expanded="false">
						<span class="js-label">
							<?php echo ( ! empty($extra_tabs[ $current_tab ]) ) ? esc_html( $extra_tabs[ $current_tab ] ) : "&mdash;"; ?>
						</span>
						<svg class="icon" height="15px" version="1.1" viewBox="0 0 50 50" width="15px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><rect fill="none" height="50" width="50"/><polygon points="47.25,15 45.164,12.914 25,33.078 4.836,12.914 2.75,15 25,37.25 "/></svg>
					</button>
					<div role="menu" class="dropdown is-left js-filters" aria-labeledby="filter-project-button" aria-hidden="true">
					<?php foreach ( $extra_tabs as $key => $value ) : ?>
						<a data-filter="active" class="js-bubble" role="menuitem" href="/<?php echo $pto->has_archive; ?>?tab=<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></a>
					<?php endforeach; ?>
					</div>
					<?php if ( ! empty($_GET['tab']) && 'complete' === $_GET['tab'] ) : ?>
						<label for="nopo"><input name="nopo" id="nopo" type="checkbox"<?php echo ( ! empty($_GET['filter']) && 'nopo' === $_GET['filter'] ? ' checked' : '' ); ?>> No PO#</label>
						<script>
							var nopo = document.getElementById('nopo'),
								pourl = '';
							if (nopo) {
								nopo.addEventListener('click', function() {
									if (this.checked) {
										pourl = '<?php echo add_query_arg([ 'tab' => 'complete', 'filter' => 'nopo' ], '/' . $pto->has_archive ); ?>';
									} else {
										pourl = '<?php echo add_query_arg([ 'tab' => 'complete' ], '/' . $pto->has_archive ); ?>';
									}
									location.href = pourl;
								});
							}
						</script>
					<?php endif; ?>
				</div>
<?php /** / ?>
				<select id="client-filter" class="hui-button chosen-with-popover js-client-filter" style="display: none;">
					<option value="all">All Clients</option>
					<option value="5137402">Test</option>
				</select>
				<div class="chosen-container chosen-container-single hui-button chosen-with-popover js-client-filter" style="width: 240px;" title="" id="client_filter_chosen">
					<a class="chosen-single" tabindex="-1"><span>All Clients</span></a>
					<div class="chosen-drop dropdown is-down-left">
						<div class="chosen-search">
							<input type="text" autocomplete="off" placeholder="Filter by client">
						</div>
						<ul class="chosen-results"></ul>
					</div>
				</div>
				<div class="js-timeframe-filter timeframe-filter pull-right">
					<h3 class="timeframe-date js-timeframe-date js-timeframe-navigator is-hidden"></h3>
					<div class="hui-button-toggle timeframe-navigation js-timeframe-navigation js-timeframe-navigator is-hidden"></div><button id="select-timeframe" class="hui-button js-selected-timeframe" aria-label="Select timeframe" aria-haspopup="true" aria-expanded="false" data-popover=""><span class="js-label">All Time</span></button>
					<div class="dropdown is-down-right js-timeframe-options" role="menu" aria-labeledby="select-timeframe" aria-hidden="true">
						<button type="button" role="menuitem" class="js-bubble" data-timeframe="month">Month</button> <button type="button" role="menuitem" class="js-bubble" data-timeframe="quarter">Quarter</button> <button type="button" role="menuitem" class="js-bubble" data-timeframe="year">Year</button> <button type="button" role="menuitem" class="js-bubble" data-timeframe="all">All Time</button>
					</div>
				</div>
				<div class="invoices-loading-spinner pull-right"></div>
<?php /**/ ?>
			</div>
			<?php endif; ?>

			<?php if (have_posts()) : ?>

				<?php do_action( 'patch/page_navi' ); ?>

				<div class="tableWrapper">
				<table class="table project-overview-table js-projects-table"><tbody class="has-clickable-table-rows">
				<thead>
					<tr>
						<th class="col-status">Status</th>
						<th class="col-number">#</th>
						<th class="col-number_client">Client #</th>
						<th class="col-date">Issue Date</th>
						<th class="col-name">Subject</th>
						<?php if ( 'open' !== $current_tab ) : ?>
							<th class="col-amount">Amount</th>
							<th class="col-paid">Accepted</th>
						<?php endif; ?>
						<th class="col-balance">Balance</th>
						<?php if ( 'open' !== $current_tab ) : ?>
							<th class="col-po">PO#</th>
							<th class="col-paid">Paid On</th>
						<?php endif; ?>
						<th class="col-complete">Complete</th>
					</tr>
				</thead>
				<tbody>
				<?php while (have_posts()) : the_post(); ?>
<?php
					$project_id     = get_post_meta( get_the_ID(), 'project', true );
					$project        = get_post( $project_id );
					// If project is deleted, remove
					if ( empty($project) || 'trash' === $project->post_status ) { continue; }

					$client_id      = get_post_meta( $project_id, 'client', true );
					$client         = get_post( $client_id );
					$closed         = has_term( 'closed', 'co_status' );
					$complete       = has_term( 'complete', 'co_status' );
					$number         = get_field('number');
					$number_client  = get_field('number_client');
					$summary        = get_field('subject');
					$date           = get_field('date');
					$paid_date      = get_post_meta( get_the_ID(), 'paid_date', true );
					$type           = get_field('type');
					$po             = get_post_meta( get_the_ID(), 'paid_po', true );

					$subtotals      = apply_filters( 'trac/item/totals', get_the_ID() );
					$total         += $subtotals['total'];
					$paid_total    += $subtotals['paid_total'];
					$balance_total += $subtotals['balance'];
/** /
	<div class="popover-wrapper">
            <button id="action-button-12793266" type="button" class="button button-tiny js-toggle-actions-button" aria-haspopup="true" aria-expanded="false" data-popover="">
              Actions
              <i data-icon="dropdown"></i>
            </button>
            <div class="dropdown is-down-right js-project-actions" role="menu" aria-labeledby="action-button-12793266" aria-hidden="true">
              <a role="menuitem" href="/projects/12793266/edit">Edit</a>
                <a role="menuitem" href="/projects/new?duplicate=12793266">Duplicate</a>
              <button role="menuitem" type="button" class="js-toggle js-bubble">Archive</button>
              <button role="menuitem" type="button" class="delete-project js-delete-project js-bubble">Delete</button>
            </div>
          </div>
/**/
?>
					<tr>
						<td class="col-status">
							<?php if ( $closed ) : ?>
								<strong class="status-pill status-pill-paid">
									Paid
								</strong>
							<?php else : ?>
								<strong class="status-pill status-pill-late">
									Open
								</strong>
							<?php endif; ?>
						</td>
						<td class="col-number">
							<?php echo $number ? esc_html( $number ) : ''; ?>
						</td>
						<td class="col-number_client">
							<?php echo $number_client ? esc_html( $number_client ) : ''; ?>
						</td>
						<td class="col-date"><?php echo get_the_date( 'm/d/y' ); ?></td>
						<td class="col-name">
							<a href="<?php the_permalink(); ?>">
								<strong><?php echo $summary ? esc_html( $summary ) : ''; ?></strong><br>
								<span class="project-client"><?php echo esc_html( get_post_field( 'post_title', $project_id, 'display' ) ); ?><?php if ( ! empty($client) ) : ?> - <?php echo esc_html( $client->post_title ); ?><?php endif; ?></span>
							</a>
						</td>
						<?php if ( 'open' !== $current_tab ) : ?>
							<td class="col-amount"><?php echo esc_html( trac_money_format( $subtotals['total'] ) ); ?></td>
							<td class="col-paid"><?php echo esc_html( trac_money_format( $subtotals['paid_total'] ) ); ?></td>
						<?php endif; ?>
						<td class="col-balance"><?php echo esc_html( trac_money_format( $subtotals['balance'] ) ); ?></td>
						<?php if ( 'open' !== $current_tab ) : ?>
							<td class="col-po"><?php echo $po ? esc_html( $po ) : "&mdash;"; ?></td>
							<td class="col-paid"><?php echo $paid_date ? trac_date( $paid_date ) : "&mdash;"; ?></td>
						<?php endif; ?>
						<td class="col-complete">

						<?php if ( ! $complete ) : ?>
							<div class="coComplete"></div>
						<?php else: ?>
							<div class="coComplete coComplete--complete"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 12"><path d="M10 1L4.5 7 2 4.6l-2 2L4.4 11 12 3l-2-2z"/></svg></div>
						<?php endif; ?>
					</tr>
				<?php endwhile; ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="<?php echo ( 'open' !== $current_tab ) ? 5 : 5; ?>"><strong>Total</strong></td>
						<?php if ( 'open' !== $current_tab ) : ?>
							<td><strong><?php echo esc_html( trac_money_format( $total ) ); ?></strong></td>
							<td><strong><?php echo esc_html( trac_money_format( $paid_total ) ); ?></strong></td>
						<?php endif; ?>
						<td><strong><?php echo esc_html( trac_money_format( $balance_total ) ); ?></strong></td>
						<td colspan="2"></td>
					</tr>
				</tfoot>
				</table>
				</div>

				<?php do_action( 'patch/page_navi' ); ?>

			<?php else : ?>
				<h4>No items yet</h4>
			<?php endif; ?>

		</div>

	</div>

</div>

<?php get_footer(); ?>
