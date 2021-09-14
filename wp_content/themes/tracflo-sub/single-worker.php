<?php

if ( ! current_user_can( 'trac_edit_projects' ) ) {
	wp_safe_redirect( home_url( '/' ) );
	die;
}

acf_form_head();

get_header();
?>

<div id="content">

	<div class="wrap">

		<div id="main" role="main">

			<h1>Edit Worker: <?php the_title(); ?></h1>
<?php
			acf_form([
				'post_title'   => true,
				'field_groups' => [ 'tracflo-worker' ],
				'return'       => add_query_arg( 'update', 'worker', home_url( '/settings/' ) ),
				'submit_value' => 'Update Worker',
			]);
?>
		</div>

	</div>

</div>

<?php get_footer(); ?>
