<?php

if ( ! current_user_can( 'trac_edit_clients' ) ) {
	wp_safe_redirect( home_url( '/' ) );
	die;
}

$posttype = get_query_var('post_type');
$pto = get_post_type_object( $posttype );

get_header();
?>

<div id="sub-nav">
	<div class="wrap">
		<a class="button-primary" href="<?php echo esc_url( home_url( '/add-client/' ) ); ?>">+ New Client</a>
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
					<th class="col-name">Name</th>
					<th class="col-client">Client</th>
					<th class="col-phone">Phone</th>
					<th class="col-email">Email Address</th>
				</tr>

				<?php while (have_posts()) : the_post();
					$client = get_field('client');
					$phone = get_field('phone');
					$email = get_field('email'); ?>

				<tr>
					<td class="col-name">
						<strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong>
					</td>
					<td class="col-client"><?php echo ! empty($client->post_title) ? $client->post_title : ''; ?></td>
					<td class="col-phone">
					<?php if ( ! empty($phone) ) : ?>
						<a href="tel:<?php echo str_replace(['.','-',' '], '', $phone); ?>"><?php echo $phone; ?></a>
					<?php endif; ?>
					</td>
					<td class="col-email">
					<?php if ( ! empty($email) ) : ?>
						<a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a>
					<?php endif; ?>
					</td>
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
