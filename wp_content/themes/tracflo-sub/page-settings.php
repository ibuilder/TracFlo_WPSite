<?php

if ( ! current_user_can( 'trac_manage_settings' ) ) {
	wp_safe_redirect( home_url( '/' ) );
	die;
}

if ( ! empty($_GET['user']) ) {
	$user_id = ( is_numeric($_GET['user']) ? 'user_' . esc_sql( $_GET['user'] ) : 'new_user' );
	if ( ! empty($_GET['action']) && 'delete' === $_GET['action'] ) :
		$owner_id = get_option( 'options_owner' );
		if ( $owner_id !== $_GET['user'] ) {
			// Assign posts to the owner
			require_once( ABSPATH . 'wp-admin/includes/user.php' );
			wp_delete_user( esc_sql( $_GET['user'] ), esc_sql( $owner ) );
			wp_safe_redirect( get_permalink() );
			die;
		}
	endif;
}

/*
add_action( 'acf/pre_save_post', function( $post_id ) {

	if ( ! empty($_POST['acf']) && ! empty($_GET['role']) && 'worker' === $_GET['role'] ) {
		$_POST['acf']['field_5aa9fd1b0581e'] = 'worker';
	}

});
*/

acf_form_head();

$site_details = get_blog_details( get_current_blog_id() );

get_header();

?>

<div id="content" class="content-sidebar">

	<div class="wrap">

		<div id="main" role="main">
<?php
		if ( ! empty($_GET['user']) ) :
			$user_id = ( is_numeric($_GET['user']) ? 'user_' . esc_sql( $_GET['user'] ) : 'new_user' );
			$user_title = 'User';
			if ( ! empty($_GET['role']) && 'worker' === $_GET['role'] ) :
				$user_title = 'Worker';
			endif;

			if ( 'new_user' === $user_id ) :
				if ( ! empty($_GET['role']) ) :
					if ( 'worker' === $_GET['role'] ) :
						$user_title = 'Worker';
						add_filter( 'acf/load_field/key=field_5aa9fd1b0581e', function( $field ) {
							$field['choices'] = [
								'worker' => 'Worker',
							];
							return $field;
						});
						add_filter( 'acf/load_value/key=field_5aa9fd1b0581e', function( $field ) {
							return 'worker';
						});
					else :
						add_filter( 'acf/load_value/key=field_5aa9fd1b0581e', function( $value, $post_id, $field ) {
							$value = esc_sql( $_GET['role'] );
							return $value;
						});
					endif;
				else :
					add_filter( 'acf/load_field/key=field_5aa9fd1b0581e', function( $field ) {
						unset($field['choices']['worker']);
						return $field;
					});
				endif;
?>
				<h1>Add a New <?php echo $user_title; ?></h1>
				<h4><?php esc_html_e( 'The new user will be notified by email with a link to create their password and log in.', 'trac' ); ?></h4>
<?php
			else :
?>
				<h1>Update <?php echo $user_title; ?></h1>
<?php
			endif;

			acf_form([
				'field_groups' => [ 'tracflo-user' ],
				'post_id'      => $user_id,
				'submit_value' => ( is_numeric($_GET['user']) ? 'Update' : 'Create' ),
				'return'       => add_query_arg( ( is_numeric($_GET['user']) ? 'update' : 'create' ), 'user', get_permalink() ),
			]);
?>
			<a href="<?php the_permalink(); ?>" title="<?php esc_attr_e( 'Return without making updates', 'trac' ); ?>"><?php _e( 'Cancel', 'trac' ); ?></a>
<?php


		elseif ( ! empty($_GET['worker']) ) :
			$worker_id = ( is_numeric($_GET['worker']) ? esc_sql( $_GET['worker'] ) : 'new_post' );
			if ( 'new_user' === $user_id ) :
?>
				<h1>Add a New Worker</h1>
				<h4><?php esc_html_e( 'The new user will be notified by email with a link to create their password and log in.', 'trac' ); ?></h4>
<?php
			else :
?>
				<h1>Update Worker</h1>
<?php
			endif;

			acf_form([
				'field_groups'   => [ 'tracflo-worker' ],
				'new_post'       => [
					'post_type'     => 'worker',
					'post_status'   => 'publish'
				],
				'post_id'        => $worker_id,
				'post_title'     => true,
				'return'         => add_query_arg( 'create', 'worker', home_url( '/settings/' ) ),
				'submit_value'   => 'Add Worker',
			]);
?>
			<a href="<?php the_permalink(); ?>" title="<?php esc_attr_e( 'Return without making updates', 'trac' ); ?>"><?php _e( 'Cancel', 'trac' ); ?></a>


		<?php elseif ( ! empty($_GET['edit']) ) : ?>
<?php 
			if ( 'preferences' === $_GET['edit'] ) :
?>
				<h1>Update Preferences</h1>
<?php
				acf_form([
					'field_groups' => [ 'tracflo-site' ],
					'post_id'      => 'options',
					'return'       => add_query_arg( 'update', 'settings', get_permalink() ),
				]);
			endif;
?>
			<a href="<?php the_permalink(); ?>" title="Return without making updates"><?php _e( 'Cancel', 'trac' ); ?></a>
		<?php else: ?>

			<article <?php post_class( 'cf' ); ?>>

				<header class="article-header">
					<h1 class="page-title" itemprop="headline"><?php the_title(); ?></h1>
				</header>

				<section class="entry-content">
					<h2 class="mb-10">
						Company Information
					</h2>
					<div class="clearfix">
						<?php if ( ! empty($site_details->domain) ) : ?>
						<div class="form-field">
							<div class="grid1of4 label">
								Web Address
							</div>
							<div class="grid3of4">
								<?php echo esc_html( $site_details->domain ); ?>
							</div>
						</div>
						<?php endif; ?>
						<?php if ( ! empty($site_details->registered) ) : ?>
						<div class="form-field">
							<div class="grid1of4 label">
								Member Since
							</div>
							<div class="grid3of4">
								<?php echo esc_html( trac_date( strtotime($site_details->registered) ) ); ?>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</section><br/><br/>

				<section class="entry-content">
					<h2 class="mb-10">
						Preferences
					</h2>
					<div class="clearfix">
						<?php if ( $setting = trac_option( 'name' ) ) : ?>
						<div class="form-field">
							<div class="grid1of4 label">
								Company Name
							</div>
							<div class="grid3of4">
								<?php echo $setting; ?>
							</div>
						</div>
						<?php endif; ?>
						<?php if ( $setting = trac_option( 'address' ) ) : ?>
						<div class="form-field">
							<div class="grid1of4 label">
								Address
							</div>
							<div class="grid3of4">
								<?php echo $setting; ?>
							</div>
						</div>
						<?php endif; ?>
						<?php if ( $setting = trac_option( 'phone' ) ) : ?>
						<div class="form-field">
							<div class="grid1of4 label">
								Phone
							</div>
							<div class="grid3of4">
								<?php echo esc_html( $setting ); ?>
							</div>
						</div>
						<?php endif; ?>
						<?php if ( $setting = get_field('owner', 'options') ) : ?>
						<div class="form-field">
							<div class="grid1of4 label">
								Account Owner
							</div>
							<div class="grid3of4">
<?php
								$owner = get_user_by( 'id', $setting );
								if ( $owner ) {
									echo esc_html( $owner->display_name );
								}
?>
							</div>
						</div>
						<?php endif; ?>

						<?php $setting = trac_option( 'default_hours' ); ?>
						<div class="form-field">
							<div class="grid1of4 label">
								Timesheet Default Time
							</div>
							<div class="grid3of4">
								<?php echo esc_html( $setting ? $setting : '8' ); ?> hrs
							</div>
						</div>

						<?php if ( $setting = get_field('labor_types', 'options') ) : ?>
						<div class="form-field" style="padding-top: 24px;">
							<div class="grid1of4 label">
								Labor Types
							</div>
							<div class="grid3of4">
								<ul>
								<?php foreach ( $setting as $item ) : ?>
									<li><?php echo esc_html( $item['title'] ); ?></li>
								<?php endforeach; ?>
								</ul>
							</div>
						</div>
						<?php endif; ?>
						<?php if ( $setting = trac_option( 'logo' ) ) : ?>
						<div class="form-field">
							<div class="grid1of4 label">
								Company Logo
							</div>
							<div class="grid3of4">
								<img src="<?php echo esc_url( $setting ); ?>" alt="Company Logo" style="max-width: 300px;">
							</div>
						</div>
						<?php endif; ?>
					</div><br/>
					<a class="button-primary" href="<?php echo esc_url( add_query_arg( 'edit', 'preferences', get_permalink() ) ); ?>">Edit Preferences</a>
				</section><br/><br/>

				<section class="entry-content">
					<h2 class="mb-10">
						Users
					</h2>
					<div class="clearfix">
<?php
					$roles    = [ 'trac_admin', 'trac_pm', 'trac_foreman' ];#'trac_super', 
					$owner_id = get_option( 'options_owner' );
					$owner    = get_user_by( 'id', $owner_id );
					foreach ( $roles as $role_name ) :
						$title    = $GLOBALS['wp_roles']->roles[$role_name]['name'];
						$roles_in = [ $role_name ];
						if ( 'trac_admin' === $role_name ) {
							$roles_in[] = 'editor';
						}
						if ( $users = get_users([ 'role__in' => $roles_in ]) ) :
?>
							<div style="padding-top: 1em">
								<h3><?php echo esc_html( $title ); ?></h3>
								<?php foreach ( $users as $user ) : ?>
								<div class="form-field">
									<div class="grid1of4 label">
										<?php echo esc_html( $user->display_name ); ?>
									</div>
									<div class="grid3of4">
										<?php echo esc_html( $user->user_email ); ?>
										(<a class="user-edit" href="<?php echo esc_url( add_query_arg( [ 'user' => $user->ID, 'action' => 'edit' ], get_permalink() ) ); ?>">Edit user</a>)
										<?php if ( empty($owner) || $owner->ID !== $user->ID ) : ?>
										(<a class="user-delete" href="<?php echo esc_url( add_query_arg( [ 'user' => $user->ID, 'action' => 'delete' ], get_permalink() ) ); ?>" onclick="return confirm(&quot;Are you sure you want to delete <?php echo esc_html( $user->display_name ); ?>? It cannot be undone. All items created by this user will be assigned to the account owner (<?php echo esc_html( $owner->display_name ); ?>).&quot;)">Delete</a>)
										<?php endif; ?>
									</div>
								</div>
								<?php endforeach; ?>
							</div>
<?php
						endif;
					endforeach;
/** /
					if ( $users = get_users([ 'role__in' => [ 'editor', 'author', 'trac_admin', 'trac_pm', 'trac_super', 'trac_foreman' ] ]) ) :
						$owner_id = get_option( 'options_owner' );
						$owner = get_user_by( 'id', $owner_id );
?>
						<?php foreach ( $users as $user ) : ?>
						<div class="form-field">
							<div class="grid1of4 label">
								<?php echo esc_html( $user->display_name ); ?>
							</div>
							<div class="grid3of4">
								<?php echo esc_html( $user->user_email ); ?>
								(<a class="user-edit" href="<?php echo esc_url( add_query_arg( [ 'user' => $user->ID, 'action' => 'edit' ], get_permalink() ) ); ?>">Edit user</a>)
								<?php if ( empty($owner) || $owner->ID !== $user->ID ) : ?>
								(<a class="user-delete" href="<?php echo esc_url( add_query_arg( [ 'user' => $user->ID, 'action' => 'delete' ], get_permalink() ) ); ?>"  onclick="return confirm(&quot;Are you sure you want to delete <?php echo esc_html( $user->display_name ); ?>? It cannot be undone. All items created by this user will be assigned to the account owner (<?php echo esc_html( $owner->display_name ); ?>).&quot;)">Delete</a>)
								<?php endif; ?>
							</div>
						</div>
						<?php endforeach; ?>
					<?php endif;/**/ ?>
					</div><br/>
					<a class="button-primary" href="<?php echo esc_url( add_query_arg( 'user', 'add', get_permalink() ) ); ?>">+ Add User</a>
				</section><br/><br/>

				<section class="entry-content">
					<h2 class="mb-10">
						Workers
					</h2>
					<div class="clearfix">
<?php
					$roles    = [ 'trac_worker' ];
					$owner_id = get_option( 'options_owner' );
					$owner    = get_user_by( 'id', $owner_id );
					foreach ( $roles as $role_name ) :
						$title    = $GLOBALS['wp_roles']->roles[$role_name]['name'];
						$roles_in = [ $role_name ];
						if ( 'trac_admin' === $role_name ) {
							$roles_in[] = 'editor';
						}
						if ( $users = get_users([ 'role__in' => $roles_in ]) ) :
?>
							<div style="padding-top: 1em">
								<h3><?php echo esc_html( $title ); ?></h3>
								<?php foreach ( $users as $user ) : ?>
								<div class="form-field">
									<div class="grid1of4 label">
										<?php echo esc_html( $user->display_name ); ?>
									</div>
									<div class="grid3of4">
										<?php echo esc_html( $user->user_email ); ?>
										(<a class="user-edit" href="<?php echo esc_url( add_query_arg( [ 'user' => $user->ID, 'action' => 'edit', 'role' => 'worker' ], get_permalink() ) ); ?>">Edit worker</a>)
										<?php if ( empty($owner) || $owner->ID !== $user->ID ) : ?>
										(<a class="user-delete" href="<?php echo esc_url( add_query_arg( [ 'user' => $user->ID, 'action' => 'delete' ], get_permalink() ) ); ?>" onclick="return confirm(&quot;Are you sure you want to delete <?php echo esc_html( $user->display_name ); ?>? It cannot be undone. All items created by this user will be assigned to the account owner (<?php echo esc_html( $owner->display_name ); ?>).&quot;)">Delete</a>)
										<?php endif; ?>
									</div>
								</div>
								<?php endforeach; ?>
							</div>
<?php
						endif;
					endforeach;
?>
					</div><br/>
					<a class="button-primary" href="<?php echo esc_url( add_query_arg( [ 'user' => 'add', 'action' => 'add', 'role' => 'worker' ], get_permalink() ) ); ?>">+ Add Worker</a>
				</section><br/><br/>

			</article>

		<?php endif; ?>

		</div>

	</div>

</div>

<?php get_footer();
