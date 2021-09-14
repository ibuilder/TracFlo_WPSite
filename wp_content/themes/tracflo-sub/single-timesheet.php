<?php

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/' ) );
	die;
}

acf_form_head();

get_header();
?>

<div id="content">

	<div class="wrap">

		<div id="main" role="main">

			<h1>Edit Timesheet: <?php the_title(); ?></h1>
<?php
			acf_form([
				'field_groups' => ['tracflo-timesheets'],#[ ( ! current_user_can('trac_edit_rates') ? 'tracflo-tickets-simple' : 'tracflo-tickets' ) ],
				'return'       => home_url( '/timesheets/' ),
				'submit_value' => 'Update Timesheet',
			]);
?>
		</div>

	</div>

</div>

<?php get_footer(); ?>
