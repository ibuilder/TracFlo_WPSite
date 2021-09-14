<?php
get_header();

acf_form_head();

$owner_id = get_option('options_owner');
?>

<div id="content" class="content-sidebar">

	<div class="wrap">

		<div id="main" role="main">

			<article <?php post_class( 'cf' ); ?>>

			<?php if ( ! get_field( 'accept_terms', "user_$owner_id" ) ) : ?>
			<?php if ( $owner_id == get_current_user_id() ) : ?>

				<header class="article-header">
					<h1 class="page-title" itemprop="headline">TracFlo Terms of Service</h1>
				</header>

				<section class="terms-content" itemprop="articleBody">
					<?php get_template_part( 'partials/terms' ); ?>
				</section>

				<footer>
<?php
					$user_id = 'user_' . get_current_user_id();
					acf_form([
						'field_groups' => [ 'tracflo-accept-terms' ],
						'post_id'      => $user_id,
						'return'       => add_query_arg( 'action', 'acceptterms', home_url('/') ),
					]);
?>
				</footer>

			<?php else : ?>

				<header class="article-header">
					<h1 class="page-title" itemprop="headline">This service is not active yet.</h1>
				</header>

				<section class="entry-content" itemprop="articleBody">
					<p>Please contact the owner or administrator to get the service activated.</p>
				</section>

			<?php endif; ?>
			<?php else : ?>
				<header class="article-header">
					<h1 class="page-title" itemprop="headline">TracFlo Terms of Service</h1>
				</header>

				<section class="terms-content" itemprop="articleBody">
					<?php get_template_part( 'partials/terms' ); ?>
				</section>
			<?php endif; ?>

			</article>

		</div>

	</div>

</div>

<?php get_footer(); ?>
