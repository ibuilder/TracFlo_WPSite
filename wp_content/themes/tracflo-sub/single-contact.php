<?php

if ( ! current_user_can( 'trac_edit_clients' ) ) {
	wp_safe_redirect( home_url( '/' ) );
	die;
}

acf_form_head();

get_header();
?>

<div id="content">

	<div class="wrap">

		<div id="main" role="main">

			<h1>Edit Contact: <?php the_title(); ?></h1>
<?php
			acf_form([
				'post_title'   => true,
				'field_groups' => [ 'tracflo-contact' ],
				'return'       => home_url( '/clients/' ),
				'submit_value' => 'Update Contact',
			]);
?>
		</div>

	</div>

</div>

<?php get_footer(); ?>
