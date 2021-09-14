<?php $current_user = wp_get_current_user(); ?>
<nav id="NavSettings" class="NavSettings">
	<ul class="NavSettings-list">
	<?php if ( current_user_can( 'trac_manage_settings' ) ) : ?>
		<li id="menu-item-settings" class="menu-item<?php echo ( is_page('settings') ? ' current-menu-item' : '' ); #$post_type = 'settings'; echo ( is_post_type_archive($post_type) || is_page('add-' . $post_type) ? ' current-menu-item' : '' ); ?> menu-item-settings">
			<a href="<?php echo esc_url( home_url( '/settings/' ) ); ?>">Settings</a>
		</li>
	<?php endif; ?>
		<li id="menu-item-user" class="menu-item<?php echo ( is_page('profile') ? ' current-menu-item' : '' ); ?> menu-item-settings">
			<a href="<?php echo esc_url( home_url( '/profile/' ) ); ?>"><?php echo esc_html( $current_user->display_name ); ?></a>
		</li>
		<li id="menu-item-user" class="menu-item<?php $post_type = 'co'; echo ( is_post_type_archive($post_type) || is_page('add-' . $post_type) ? ' current-menu-item' : '' ); ?> menu-item-settings">
			<a href="<?php echo wp_logout_url(); ?>">Log Out</a>
		</li>
	</ul>
</nav>
