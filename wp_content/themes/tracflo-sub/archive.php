<?php
$posttype = get_query_var('post_type');
$pto = get_post_type_object( $posttype );

get_header();
?>

<div id="sub-nav">
	<div class="wrap">
		<a class="button-primary" href="<?php echo esc_url( home_url( '/add-' . $pto->name . '/' ) ); ?>">+ New <?php echo esc_html( $pto->labels->singular_name ); ?></a>
	</div>
</div>

<div id="content">

	<div class="wrap">

		<div id="main" role="main">

			<h1><?php echo $pto->labels->name; ?></h1>

			<?php if (have_posts()) : ?>

			<div class="tableWrapper">
			<table class="table project-overview-table js-projects-table"><tbody class="has-clickable-table-rows">
				<tr>
					<th class="col-name">Project Name</th>
					<th class="col-gc">GC</th>
					<th class="col-approver">Approver</th>
				</tr>

				<?php while (have_posts()) : the_post();
					$gc = get_field('general_contractor');
					$approver = get_field('ticket_approver'); ?>

				<tr>
					<td class="col-name">
						<strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong>
					</td>
					<td class="col-gc"><?php echo $gc ? $gc->post_title : ''; ?></td>
					<td class="col-approver"><?php echo $approver ? $approver['display_name'] : ''; ?></td>
				</tr>

				<?php endwhile; ?>
			</tbody></table>
			</div>

			<?php else : ?>
				<h4>No items yet</h4>
			<?php endif; ?>

		</div>

	</div>

</div>

<?php get_footer(); ?>
