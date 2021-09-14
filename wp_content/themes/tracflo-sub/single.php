<?php
$current_user = wp_get_current_user();
if ( in_array( 'trac_foreman', (array) $current_user->roles ) ) :
wp_safe_redirect( home_url( '/tickets/' ) );
die;
endif;
get_header(); ?>

<div id="content">

	<div class="wrap">

		<div id="main" role="main">

			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

				<?php get_template_part( '/partials/post-formats/format', get_post_format() ); ?>

			<?php endwhile; else : ?>
				<?php get_template_part( '/partials/content', 'missing' ); ?>
			<?php endif; ?>

		</div>

		<?php get_sidebar(); ?>

	</div>

</div>

<?php get_footer(); ?>
