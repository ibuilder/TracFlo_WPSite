<nav id="NavMain" class="NavMain" role="navigation">
<?php
	wp_nav_menu([
		'container'      => false,
		'menu'           => 'Main Navigation',
		'menu_id'        => 'NavMain-list',
		'menu_class'     => 'NavMain-list',
		'theme_location' => 'main-nav',
		'before'         => '',
		'after'          => '',
		'link_before'    => '',
		'link_after'     => '',
		'depth'          => 1,
		'fallback_cb'    => function() {
			$current_user = wp_get_current_user();
			$nav_items = [
				[
					'post_type' => 'timesheet',
					'title'     => 'Timesheets',
					'url'       => home_url( '/timesheets/' ),
				], [
					'post_type' => 'ticket',
					'title'     => 'Tickets',
					'url'       => home_url( '/tickets/' ),
				]
			];

			if ( current_user_can( 'trac_edit_cos' ) ) :
				$nav_items[] = [
					'post_type' => 'co',
					'title'     => 'Change Orders',
					'url'       => home_url( '/cos/' ),
				];
			endif;

			if ( current_user_can( 'trac_edit_projects' ) ) :
				$nav_items[] = [
					'post_type' => 'project',
					'title'     => 'Projects',
					'url'       => home_url( '/projects/' ),
				];
			endif;

			if ( current_user_can( 'trac_edit_clients' ) ) :
				$nav_items[] = [
					'post_type' => 'client',
					'title'     => 'Clients',
					'url'       => home_url( '/clients/' ),
				];
			endif;
?>
			<ul class="NavMain-list">
				<?php if ( ! in_array( 'trac_foreman', (array) $current_user->roles ) ) : ?>
				<li id="menu-item-dashboard" class="menu-item<?php echo ( is_front_page() ? ' current-menu-item' : '' ); ?> menu-item-home menu-item-dashboard"><a href="/">Dashboard</a></li>
				<?php endif; ?>
				<?php foreach ( $nav_items as $item ) : ?>
				<li id="menu-item-<?php echo esc_attr( $item['post_type'] ); ?>" class="menu-item<?php echo ( is_post_type_archive($item['post_type']) || is_page('add-' . $item['post_type']) ? ' current-menu-item' : '' ); ?> menu-item-<?php echo esc_attr( $item['post_type'] ); ?>"><a href="<?php echo esc_attr( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
<?php
		},
	]);
?>
</nav>
