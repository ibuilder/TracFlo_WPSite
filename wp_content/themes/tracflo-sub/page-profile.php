<?php

$current_user = wp_get_current_user();

acf_form_head();

get_header();
?>

<div id="content" class="content-sidebar">

	<div class="wrap">

		<div id="main" role="main">

			<article <?php post_class( 'cf' ); ?>>

				<header class="article-header">
					<h1 class="page-title" itemprop="headline"><?php the_title(); ?></h1>
				</header>
<?php
				$user_id = 'user_' . $current_user->ID;
				acf_form([
					'field_groups' => [ 'tracflo-user' ],
					'post_id'      => $user_id,
					'return'       => add_query_arg( 'action', 'updated', get_permalink() ),
				]);
?>
				<a href="<?php the_permalink(); ?>" title="Return without making updates">Cancel</a>

			</article>

		</div>

	</div>

</div>

<?php get_footer();
