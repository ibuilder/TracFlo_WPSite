<?php
$posttype = get_query_var('post_type');
$pto = get_post_type_object( $posttype );

$site_details = get_blog_details( get_current_blog_id() );

// Group posts in alphabetical order by project name
$active_posts = [];
$inactive_posts = [];
$current_project = 0;
$title = '';
$inactive = false;
$start_year = date_i18n( 'Y', strtotime($site_details->registered) );
$years = range( date_i18n('Y'), $start_year );
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

/* Only TopRock * /
if ( 2 === get_current_blog_id() ) {
	if ( ! empty($_COOKIE['project']) ) {
		$current_project = $_COOKIE['project'];
	}
}
/**/

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
/** /
echo '<pre style="clear:both;font-size:0.7em;text-align:left;width:100%;">';
print_r($show_posts);
echo "</pre>\n";
#exit;
/**/
/* Only TopRock * /
if ( 2 === get_current_blog_id() ) {
	if ( ! empty($_COOKIE['year']) ) {
		$current_year = $_COOKIE['year'];
	}
}
/**/

if ( ! empty($_GET['yid']) && 'reset' !== $_GET['yid'] ) {
	$current_year = $_GET['yid'];
}

if ( $current_year ) {
	foreach ( $show_posts as $key => $main_post ) {
		$count = 0;
		foreach ( $main_post['posts'] as $ticket_post ) {
/** /
echo '<pre style="clear:both;font-size:0.7em;text-align:left;width:100%;">';
print_r($ticket_post);
echo "</pre>\n";
#exit;
/**/
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
		<a class="button-primary" href="<?php echo esc_url( home_url( '/add-' . $pto->name . '/' ) ); ?>">+ New <?php echo esc_html( $pto->labels->singular_name ); ?></a>
	</div>
</div>

<div id="content">

	<div class="wrap">

		<div id="main" role="main">

			<h1><?php echo $pto->labels->name; ?><?php if ( ! empty($current_project) && $title = get_the_title( esc_sql($current_project) ) ) { echo ': ' . esc_html($title); } ?></h1>

			<header class="do-not-print">
				<div class="header-controls do-not-print clearfix">
					<?php #if ( ! $current_project ) : ?>
					<div class="filter-menu">
						<button id="filter-status-button" class="button js-filter-selector" aria-label="Filter statuses" aria-haspopup="true" aria-expanded="false">
							<span class="js-label">
								<?php echo ( $inactive ) ? $title_inactive : $title_active; ?>
							</span>
							<svg class="icon" height="15px" version="1.1" viewBox="0 0 50 50" width="15px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><rect fill="none" height="50" width="50"/><polygon points="47.25,15 45.164,12.914 25,33.078 4.836,12.914 2.75,15 25,37.25 "/></svg>
						</button>
						<div role="menu" class="dropdown is-left js-filters" aria-labeledby="filter-project-button" aria-hidden="true">
							<a data-filter="active" class="js-bubble" role="menuitem" href="/<?php echo $pto->has_archive; ?>?filter=active">
								<?php echo $title_active; ?>
							</a>
							<a data-filter="archived" class="js-bubble" role="menuitem" href="/<?php echo $pto->has_archive; ?>?filter=inactive">
								<?php echo $title_inactive; ?>
							</a>
						</div>
					</div>
					<?php #endif; ?>

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
								<a data-filter="active" class="js-bubble" role="menuitem" href="<?php echo add_query_arg( 'yid', $year ); /** / ?>/<?php echo $pto->has_archive; ?>?yid=<?php echo esc_attr($year); /**/ ?>">
									<?php echo esc_html($year); ?>
								</a>
								<?php endforeach; ?>
								<a data-filter="active" class="js-bubble" role="menuitem" href="<?php echo add_query_arg( 'yid', 'reset' ); /** / ?>/<?php echo $pto->has_archive; ?>?yid=reset<?php /**/ ?>">Reset</a>
							</div>
						</div>
						<?php endif; ?>
						<?php #endif; ?>
					<?php #endif; ?>

					<a href="#" onclick="window.print(); return false" title="Print List" class="btn-action-print btn-action btn-pill btn-invoice-action">
						<span class="invoice-action-icon print-icon">Print</span>
					</a>

					<a class="button pull-right" href="<?php echo ( ! empty($_GET['show-attached']) ) ? remove_query_arg( 'show-attached' ) : add_query_arg( 'show-attached', 1 ); ?>" title="<?php echo ( empty($_GET['show-attached']) ) ? __( 'Show Attached', 'tracflo' ) : __( 'Hide Attached', 'tracflo' ); ?>"><?php echo ( empty($_GET['show-attached']) ) ? __( 'Show Attached', 'tracflo' ) : __( 'Hide Attached', 'tracflo' ); ?></a>
				</div>
			</header>

			<?php if ( $show_posts ) : ?>

			<?php do_action( 'patch/page_navi' ); ?>

			<div class="tableWrapper">
			<table class="table project-overview-table js-projects-table">
			<tbody class="has-clickable-table-rows">
<?php
			$total = 0; foreach ( $show_posts as $project_name => $project ) : $project_posts = $project['posts'];
				if ( ! $project_posts ) { continue; }
?>
				<tr>
					<th class="col-name"><?php echo ( ! empty($project['title']) ? strtoupper($project['title']) : 'TITLE' ); ?></th>
					<th class="col-date">Work Date</th>
					<th class="col-submitted">Submitted</th>
					<th class="col-status">Status</th>
				</tr>
<?php
				foreach ( $project_posts as $post ) : setup_postdata( $post );
					if ( ! empty($_GET['show-attached']) || ! has_term( 'attached', 'ticket_status', $post ) ) :
						$date = get_field('date');
						$subtotal = ( $amount = get_post_meta( get_the_ID(), 'total', true ) ) ? $amount : 0;
						$total += $subtotal;
						$terms = wp_get_post_terms( get_the_ID(), 'ticket_status' );
						$term_slug = ( ! empty($terms[0]) ) ? $terms[0]->slug : 'draft';
						$term_name = ( ! empty($terms[0]) ) ? $terms[0]->name : 'Draft';
?>
					<tr<?php echo ( has_term( 'attached', 'ticket_status', $post ) ) ? ' class="attached"' : ''; ?>>
						<td class="col-name">
							<strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong>
						</td>
						<td class="col-date"><?php echo $date ? trac_date( strtotime($date) ) : ''; ?></td>
						<td class="col-submitted"><?php echo get_the_date(); ?></td>
						<td class="col-status is-<?php echo esc_attr($term_slug); ?>">
							<?php echo ( 'approvetm' === $term_slug ) ? 'Approved<br>Time &amp; Materials' : $term_name; ?>
						</td>
					</tr>

				<?php endif; endforeach; ?>
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

<?php get_footer();
