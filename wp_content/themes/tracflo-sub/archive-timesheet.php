<?php
$posttype = get_query_var('post_type');
$pto = get_post_type_object( $posttype );

$site_details = get_blog_details( get_current_blog_id() );

// Set up dates shown
if ( ! empty($_REQUEST['from']) ) {
	$date_start = $_REQUEST['from'];
} else {
	$date_start = date( 'Y-m-d', strtotime('monday this week') );
}
if ( ! empty($_REQUEST['to']) ) {
	$date_end = $_REQUEST['to'];
} else {
	$date_end = date( 'Y-m-d', strtotime('sunday this week') );
}
if ( ! empty($_REQUEST['type']) ) {
	$date_type = $_REQUEST['type'];
} else {
	$date_type = 'custom';
}


// Group posts in alphabetical order by project name
$active_posts = [];
$inactive_posts = [];
$current_project = 0;
$title = '';
$inactive = false;
$start_year = date_i18n( 'Y', strtotime($site_details->registered) );
$years = range( date_i18n('Y'), $start_year );
$total_time = [
	'hours'    => 0,
	'overtime' => 0,
	'total'    => 0,
];


// Add default projects
$all_foreman_projects_query = New WP_Query([
	'meta_query' => [
		[
			'key'     => 'team_$_foreman',
			'value'   => get_current_user_id(),
			'compare' => '=',
// 			'compare_key' => 'LIKE',
		]
	],
	'post_type' => 'project',
	'posts_per_page' => -1,
]);
if ( ! empty($all_foreman_projects_query->posts) ) {
	$all_foreman_projects = $all_foreman_projects_query->posts;
	foreach ( $all_foreman_projects as $project ) {
		$title = $project->post_title;
		if ( empty($active_posts[ $title ]) ) {
			$active_posts[ $title ] = [];
			$active_posts[ $title ]['ID'] = $project->ID;
			$active_posts[ $title ]['title'] = $project->post_title;
			$active_posts[ $title ]['posts'] = [];
		}
	}
}


if (have_posts()) : while (have_posts()) : the_post();
	$project = get_post_meta( get_the_ID(), 'project', true );
	$project_post = get_post( $project );

	if ( $project_post && 'trash' !== $project_post->post_status ) {
		if ( $current_project != $project ) {
			$current_project = $project;
			$title = get_the_title( $current_project );
			if ( has_term( 'inactive', 'project_status', $current_project ) ) {
				if ( empty($inactive_posts[ $title ]) ) {
					$inactive_posts[ $title ] = [];
					$inactive_posts[ $title ]['ID'] = $current_project;
					$inactive_posts[ $title ]['title'] = $title;
					$inactive_posts[ $title ]['posts'] = [];
				}
			} else {
				if ( empty($active_posts[ $title ]) ) {
					$active_posts[ $title ] = [];
					$active_posts[ $title ]['ID'] = $current_project;
					$active_posts[ $title ]['title'] = $title;
					$active_posts[ $title ]['posts'] = [];
				}
			}
		}
		if ( has_term( 'inactive', 'project_status', $current_project ) ) {
			$inactive_posts[ $title ]['posts'][] = $post;
		} else {
			$active_posts[ $title ]['posts'][] = $post;
		}

		$date = get_field('date');
		$year = date_i18n( 'Y', strtotime($date) );
		if ( ! in_array( $year, $years ) ) {
			$years[] = $year;
		}

		if ( empty($_GET['pid']) || 'reset' === $_REQUEST['pid'] || $_REQUEST['pid'] == get_post_meta(get_the_ID(), 'project', true) ) {
			$timesheet_total_time = get_field('total_time');
			$total_time['hours']    += (int) $timesheet_total_time['hours'];
			$total_time['overtime'] += (int) $timesheet_total_time['overtime'];
			$total_time['total']    += (int) $timesheet_total_time['hours'];
			$total_time['total']    += (int) $timesheet_total_time['overtime'];
		}
	}
endwhile; endif;

//unset($current_project);
unset($title);

$current_project = null;
$current_year = null;

ksort($inactive_posts);
ksort($active_posts);

$total_inactive = count($inactive_posts);
$total_active = count($active_posts);

$title_inactive = "Archived Projects ($total_inactive)";
$title_active = "Active Projects ($total_active)";

if ( ! empty($_REQUEST['filter']) && 'inactive' == $_REQUEST['filter'] ) {
	$inactive = true;
	$main_posts = $inactive_posts;
} else {
	$main_posts = $active_posts;
}

$show_posts = $main_posts;

if ( ! empty($_GET['pid']) && 'reset' !== $_GET['pid'] ) {
	$current_project = $_GET['pid'];
}

if ( $current_project ) {
	$show_posts = [];
	foreach ( $main_posts as $main_post ) {
		if ( $current_project == $main_post['ID'] ) {
			$show_posts[] = $main_post;
			break;
		}
	}
}

if ( ! empty($_GET['yid']) && 'reset' !== $_GET['yid'] ) {
	$current_year = $_GET['yid'];
}

if ( $current_year ) {
	foreach ( $show_posts as $key => $main_post ) {
		$count = 0;
		foreach ( $main_post['posts'] as $ticket_post ) {
			#$date = get_post_meta( $ticket_post->ID, 'date', true );
			$year = date_i18n('Y', strtotime($ticket_post->post_date));
			if ( $current_year != $year ) {
				unset($show_posts[$key]['posts'][$count]);
			}
			$count++;
		}
	}
}

get_header();
?>

<div id="sub-nav" class="do-not-print">
	<div class="wrap">
		<a class="button-primary" style="float:left;" href="<?php echo esc_url( home_url( '/add-' . $pto->name . '/' ) ); ?>">+ New <?php echo esc_html( $pto->labels->singular_name ); ?></a>
	</div>
</div>

<div id="content">

	<div class="wrap">

		<div id="main" role="main">



<!--
<header class="clearfix mb-20">
    <div class="hui-button-toggle mr-5">
      <a class="hui-button hui-button-icon-only" title="Previous" href="/reports?from=2019-04-01&amp;kind=week&amp;till=2019-04-07">
        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="12" viewBox="0 0 8 12" fill="currentColor">
          <path d="M6 .34L.34 6 6 11.66l1.41-1.42L3.17 6 7.4 1.76z"></path>
        </svg>
      </a>
      <a class="hui-button hui-button-icon-only" title="Next" href="/reports?from=2019-04-15&amp;kind=week&amp;till=2019-04-21">
        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="12" viewBox="0 0 8 12" fill="currentColor">
          <path d="M1.75.34L7.41 6l-5.66 5.66-1.41-1.42L4.58 6 .34 1.76z"></path>
        </svg>
      </a>
    </div>

    <h1 class="aligned-to-button mb-0 mr-5">
      Week: <span class="text-400">08 – 14 Apr 2019</span>
    </h1>

    <a class="valign-middle do-not-print" href="/reports?from=2019-04-22&amp;kind=week&amp;till=2019-04-28">Return to This Week</a>

  <div class="fl-right do-not-print">
    <div class="popover-wrapper">
      <button type="button" id="select-timeframe" class="hui-button hui-button-icon-right" data-popover="" aria-expanded="false">
        Week
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
          <path d="M4.63 8.7L.34 4.42 1.75 3l4.3 4.3 4.24-4.25 1.41 1.41-5.65 5.66-1.42-1.41z"></path>
        </svg>
      </button>

      <div class="dropdown is-down-right" role="menu" aria-labelledby="select-timeframe" aria-hidden="true" data-focus-wrap="true" style="top: 34px; left: -20px;">
          <a role="menuitem" href="/reports?from=2019-04-22&amp;kind=week&amp;till=2019-04-28">Week</a>
          <a role="menuitem" href="/reports?from=2019-04-16&amp;kind=semimonth&amp;till=2019-04-30">Semimonth</a>
          <a role="menuitem" href="/reports?from=2019-04-01&amp;kind=month&amp;till=2019-04-30">Month</a>
          <a role="menuitem" href="/reports?from=2019-04-01&amp;kind=quarter&amp;till=2019-06-30">Quarter</a>
          <a role="menuitem" href="/reports?from=2019-01-01&amp;kind=year&amp;till=2019-12-31">Year</a>
          <a role="menuitem" href="/reports?kind=all_time">All Time</a>
          <button type="button" role="menuitem" data-showform="timeframe-selection" disabled="">Custom</button>
      </div>
    </div>
  </div>
</header>
-->



			<h1><?php echo $pto->labels->name; ?><?php if ( ! empty($current_project) && $title = get_the_title( esc_sql($current_project) ) ) { echo ': ' . esc_html($title); } ?></h1>


			<header class="do-not-print">
				<div class="header-controls do-not-print clearfix">
					<?php #if ( $main_posts ) : ?>
						<div class="filter-menu">
							<button title="Choose a Project" id="filter-project-button" class="button js-filter-selector" aria-label="Filter projects" aria-haspopup="true" aria-expanded="false">
								<span class="js-label">
									<?php echo ( $current_project ) ? esc_html( get_the_title($current_project) ) : 'Choose a Project'; ?>
								</span>
								<svg class="icon" height="15px" version="1.1" viewBox="0 0 50 50" width="15px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><rect fill="none" height="50" width="50"/><polygon points="47.25,15 45.164,12.914 25,33.078 4.836,12.914 2.75,15 25,37.25 "/></svg>
							</button>
							<div role="menu" class="dropdown is-left js-filters" aria-labeledby="filter-project-button" aria-hidden="true">
								<?php foreach ( $main_posts as $project_name => $project ) : $project_posts = $project['posts']; ?>
								<a data-filter="active" class="js-bubble" role="menuitem" href="<?php echo add_query_arg( 'pid', $project['ID'] ); /** / ?>/<?php echo $pto->has_archive; ?>?pid=<?php echo esc_attr($project['ID']); /**/ ?>">
									<?php echo esc_html($project['title']); ?> (<?php echo count($project_posts); ?>)
								</a>
								<?php endforeach; ?>
								<a data-filter="active" class="js-bubble" role="menuitem" href="<?php echo add_query_arg( 'pid', 'reset' ); /** / ?>/<?php echo $pto->has_archive; ?>?pid=reset<?php /**/ ?>">Reset</a>
							</div>
						</div>

						<?php #if ( 2 == get_current_blog_id() ) : ?>
<?php
/*
						// get years and if more than one, show this drop down with the years.
						// look for the oldest date in the meta and use that to build list since then.
						#$years = ['2016', '2017', '2018'];
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
								<a data-filter="active" class="js-bubble" role="menuitem" href="<?php echo add_query_arg( 'yid', $year ); ?>">
									<?php echo esc_html($year); ?>
								</a>
								<?php endforeach; ?>
								<a data-filter="active" class="js-bubble" role="menuitem" href="<?php echo add_query_arg( 'yid', 'reset' ); ?>">Reset</a>
							</div>
						</div>
						<?php endif; ?>
						<?php #endif;
*/ ?>
					<?php #endif; ?>

					<form id="timeframe-selection" class="pull-right">
						<input type="hidden" name="pid" id="pid" value="<?php echo ( ! empty($_REQUEST['pid']) ? $_REQUEST['pid'] : 0 ); ?>">
<!-- 						<input type="hidden" name="kind" id="kind" value="custom"> -->
						<span class="aligned-to-button-sm">View time report from</span>
						<input type="text" name="from" id="from" value="<?php echo esc_attr( $date_start ); ?>" class="hui-input hui-input-small datepicker" style="width: 100px" data-isodate="true" data-notafter="till" data-checkdate="true" data-popover="" autocomplete="off">
						<span class="aligned-to-button-sm">to</span>
						<input type="text" name="to" id="to" value="<?php echo esc_attr( $date_end ); ?>" class="hui-input hui-input-small datepicker" style="width: 100px" data-datepicker="true" data-isodate="true" data-checkdate="true">
						<button class="hui-button hui-button-small ml-5">Update Report</button>
					</form>
				</div>
			</header>


<!--
			<form id="timeframe-selection" class="pb-20">
				<hr class="mb-20">
				<input type="hidden" name="kind" id="kind" value="custom">
				<span class="aligned-to-button-sm">View time report from</span>
				<input type="text" name="from" id="from" value="<?php echo esc_attr( $date_start ); ?>" class="hui-input hui-input-small datepicker" style="width: 100px" data-isodate="true" data-notafter="till" data-checkdate="true" data-popover="" autocomplete="off">
				<span class="aligned-to-button-sm">to</span>
				<input type="text" name="to" id="to" value="<?php echo esc_attr( $date_end ); ?>" class="hui-input hui-input-small datepicker" style="width: 100px" data-datepicker="true" data-isodate="true" data-checkdate="true">
				<button class="hui-button hui-button-small ml-5">Update Report</button>
			</form>
-->
			<?php /*
if ( current_user_can( 'trac_pm' ) || current_user_can( 'trac_admin' ) ) : ?>
				<hr class="mb-20">
				<div class="mb-20">
				<a href="<?php echo home_url( '/reports/' ); ?>">View Workers</a> | <a href="<?php echo add_query_arg( 'type', 'project', home_url( '/timesheets/' ) ); ?>">View Projects</a>
				</div>
			<?php endif;
*/ ?>


				<hr class="mb-20">
			<div class="hui-row reports-summary mt-30 mb-30">
				<div class="hui-col-fluid test-hours-tracked">
					<div class="hui-label text-14">
						Hours
					</div>
					<h1 class="mb-0">
						<?php echo esc_html( $total_time['total'] ); ?>
					</h1>
				</div>
				<div class="hui-col-fluid test-billable-percent mb-20">
<canvas id="chart-billable" width="80" height="80"></canvas>
<script>
document.addEventListener('DOMContentLoaded', function() {

Chart.pluginService.register({
  beforeDraw: function (chart) {
    if (chart.config.options.elements.center) {
      //Get ctx from string
      var ctx = chart.chart.ctx;

      //Get options from the center object in options
      var centerConfig = chart.config.options.elements.center;
      var fontStyle = centerConfig.fontStyle || 'Arial';
      var txt = centerConfig.text;
      var color = centerConfig.color || '#000';
      var sidePadding = centerConfig.sidePadding || 20;
      var sidePaddingCalculated = (sidePadding/100) * (chart.innerRadius * 2)
      //Start with a base font of 30px
      ctx.font = "30px " + fontStyle;

      //Get the width of the string and also the width of the element minus 10 to give it 5px side padding
      var stringWidth = ctx.measureText(txt).width;
      var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

      // Find out how much the font can grow in width.
      var widthRatio = elementWidth / stringWidth;
      var newFontSize = Math.floor(30 * widthRatio);
      var elementHeight = (chart.innerRadius * 2);

      // Pick a new font size so it will not be larger than the height of label.
      var fontSizeToUse = Math.min(newFontSize, elementHeight);

      //Set font settings to draw it correctly.
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
      var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
      ctx.font = fontSizeToUse+"px " + fontStyle;
      ctx.fillStyle = color;

      //Draw text in center
      ctx.fillText(txt, centerX, centerY);
    }
  }
});

var ctx = document.getElementById('chart-billable').getContext('2d');
var config = {
	type: 'doughnut',
	data: {
		datasets: [{
			data: [<?php echo esc_attr( $total_time['hours'] ); ?>, <?php echo esc_attr( $total_time['overtime'] ); ?>],
			backgroundColor: [
				'rgba(75, 192, 192, 1)',
				'rgba(248, 133, 56, 1)',
			],
			//label: 'Dataset 1'
		}],
		labels: [
			'Hours',
			'Overtime',
		],
	},
	options: {
		cutoutPercentage: 80,
<?php if ( $total_time['hours'] ) : ?>
		elements: {
			center: {
				text: '<?php

$output = 0;
if ( $total_time['hours'] ) {
	$output = 100;

	if ( $total_time['overtime'] ) {
		$output = round( 100-($total_time['overtime'] / $total_time['hours'] * 100), 1 );
	}
}
echo esc_attr( $output );

?>%',
				color: 'rgba(75, 192, 192, 1)', //Default black
				fontStyle: 'Helvetica', //Default Arial
				sidePadding: 15, //Default 20 (as a percentage)
			},
		},
<?php endif; ?>
		responsive: true,
		legend: false,/*
{
			position: 'right',
		},
*/
/*
		title: {
			display: true,
			text: 'Chart.js Doughnut Chart'
		},
*/
		animation: {
			animateScale: true,
			animateRotate: true
		},
		rotation: -0.5 * Math.PI,
	}
};
var myDoughnutChart = new Chart(ctx, config);

});
</script>
<style>

</style>
				</div>
<!--
				<div class="billable-percent-circle" id="billable-percent-circle">
					<h1 class="mb-0">
						83<span>%</span>
					</h1>
				</div>
-->
				<div class="hui-col-fluid billable-percent-info">
					<div class="hui-label text-14">
						Tracked Time
					</div>
					<div class="mt-5 text-1">
						<strong><?php echo esc_html( $total_time['hours'] ); ?></strong> hours
					</div>
					<div class="mt-5 text-2">
						<strong><?php echo esc_html( $total_time['overtime'] ); ?></strong> Overtime
					</div>
				</div>
			</div>


			<?php if ( $show_posts ) : ?>

			<?php do_action( 'patch/page_navi' ); ?>

			<div class="tableWrapper">
			<table class="table project-overview-table js-projects-table" style="width:100%;">
			<tbody class="has-clickable-table-rows">
<?php
			$total = 0; foreach ( $show_posts as $project_name => $project ) : $project_posts = $project['posts'];
// 				if ( ! $project_posts ) { continue; }
?>
				<tr>
					<th class="col-name">
						<?php echo ( ! empty($project['title']) ? strtoupper($project['title']) : 'TITLE' ); ?>
						&nbsp; (<a href="<?php echo esc_url( add_query_arg( 'pid', $project['ID'], home_url( '/add-' . $pto->name . '/' ) ) ); ?>" style="text-transform: none;">+ Add <?php echo esc_html( $pto->labels->singular_name ); ?></a>)
					</th>
					<th class="col-workers">Workers</th>
					<th class="col-hours">Hours</th>
					<th class="col-overtime">Overtime</th>
					<th class="col-total">Total</th>
				</tr>
<?php
				foreach ( $project_posts as $post ) : setup_postdata( $post );
// 					if ( ! empty($_GET['show-attached']) || ! has_term( 'attached', 'ticket_status', $post ) ) :
						$date       = get_field('date');
						$subtotal   = ( $amount = get_post_meta( get_the_ID(), 'total', true ) ) ? $amount : 0;
						$total     += $subtotal;
						$terms      = wp_get_post_terms( get_the_ID(), 'ticket_status' );
						$term_slug  = ( ! empty($terms[0]) ) ? $terms[0]->slug : 'draft';
						$term_name  = ( ! empty($terms[0]) ) ? $terms[0]->name : 'Draft';
						$total_time = get_post_meta( get_the_ID(), 'total_time', true );
?>
					<tr<?php echo ( has_term( 'attached', 'ticket_status', $post ) ) ? ' class="attached"' : ''; ?>>
						<td class="col-name col-start_date"><strong><a href="<?php the_permalink(); ?>"><?php echo esc_html( $date ? trac_date( strtotime($date) ) : '' ); ?></a></td>
						<td class="col-workers"><?php echo esc_html( get_post_meta( get_the_ID(), 'total_users', true ) ); ?></td>
						<td class="col-hours"><?php echo esc_html( $total_time['hours'] ); ?></td>
						<td class="col-overtime"><?php echo esc_html( $total_time['overtime'] ); ?></td>
						<td class="col-total"><?php echo esc_html( $total_time['total'] ); ?></td>
					</tr>

				<?php /* endif; */ endforeach; ?>
			<?php endforeach; wp_reset_postdata(); ?>
			</tbody>
<?php /** / ?>
			<tfoot>
				<tr>
					<td colspan="4"><strong>Total</strong></td>
					<td><strong><?php echo esc_html( trac_money_format( $total ) ); ?></strong></td>
				</tr>
			</tfoot>
<?php /**/ ?>
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
