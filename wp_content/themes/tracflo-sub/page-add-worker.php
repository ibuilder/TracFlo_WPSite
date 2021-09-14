<?php
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

/*
					add_filter( 'acf/load_field/key=field_5aa9fd1b0581e', function() {
						
					});
*/

					acf_form([
						'field_groups' => ['tracflo-user'],
						'post_id'      => 'new_user',
						'return'       => '%post_url%?create=worker',#home_url( '/tickets/' ),
						'submit_value' => 'Add Worker',
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
