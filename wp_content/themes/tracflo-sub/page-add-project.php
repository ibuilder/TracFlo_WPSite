<?php

if ( ! current_user_can( 'trac_edit_projects' ) ) {
	wp_safe_redirect( home_url( '/' ) );
	die;
}

acf_form_head();

get_header();
?>

<div id="content" class="content">

	<div class="wrap">

		<div id="main" role="main">

			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<article <?php post_class( 'cf' ); ?>>

				<header class="article-header">
					<h1 class="page-title" itemprop="headline"><?php the_title(); ?></h1>
				</header>

				<section class="entry-content" itemprop="articleBody">
<?php
					the_content();

					acf_form([
						'field_groups'   => [ 'tracflo-project' ],
						'new_post'       => [
							'post_type'     => 'project',
							'post_status'   => 'publish'
						],
						'post_id'        => 'new_post',
						'post_title'     => true,
						'return'         => add_query_arg( 'create', 'project', home_url( '/projects/' ) ),
						'submit_value'   => 'Create Project',
					]);
?>
				</section>

			</article>

			<?php endwhile; else : ?>
				<?php get_template_part( 'partials/content', 'missing' ); ?>
			<?php endif; ?>

		</div>

	</div>

</div>

<?php get_footer();
