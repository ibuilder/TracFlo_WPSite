		</div><?php // End #container ?>

		<footer id="footer" role="contentinfo">

			<div id="inner-footer" class="wrap cf">
<?php /** / ?>
				<nav id="footer-nav" role="navigation">
				<?php wp_nav_menu(array(
					'container' => false,							// remove nav container
					'menu' => 'Footer Navigation',					// nav name
					'menu_class' => '',								// adding custom nav class
					'theme_location' => 'footer-nav',				// where it's located in the theme
					'before' => '',									// before the menu
					'after' => '',									// after the menu
					'link_before' => '',							// before each link
					'link_after' => '',								// after each link
					'depth' => 0									// limit the depth of the nav
				)); ?>
				</nav>
<?php /**/ ?>
				<p class="copyright">&copy; <?php echo date_i18n('Y'); ?> <?php echo esc_html( get_field('company_name', 'options') ); ?> <a href="<?php echo home_url( '/terms/' ); ?>"><?php _e( 'Terms of Service', 'tracflo' ); ?></a> | <a href="<?php echo home_url( '/privacy/' ); ?>"><?php _e( 'Privacy Policy', 'tracflo' ); ?></a></p>

			</div>

		</footer>

		<?php wp_footer(); ?>
	</body>
</html>
