<?php
get_header();

acf_form_head();

$owner_id = get_option('options_owner');
?>

<div id="content" class="content-sidebar">

	<div class="wrap">

		<div id="main" role="main">

			<article <?php post_class( 'cf' ); ?>>

				<header class="article-header">
					<h1 class="page-title" itemprop="headline">TracFlo Privacy Policy</h1>
				</header>

				<section class="terms-content" itemprop="articleBody">
					<?php get_template_part( 'partials/terms', 'privacy' ); ?>
				</section>

			</article>

		</div>

	</div>

</div>

<?php get_footer(); ?>
