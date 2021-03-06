<?php
/**
 * Make adjustments to the admin
 */

// actions
add_action( 'admin_menu',                 'trac_disable_dashboard_widgets' );
add_action( 'admin_head',                 'trac_admin_favicon', 11 );
add_action( 'welcome_panel',              'trac_dashboard_welcome_cleanup' );
add_action( 'admin_menu',                 'trac_remove_menu_pages' );
add_action( 'wp_before_admin_bar_render', 'trac_customize_admin_bar' );
#add_action( 'admin_init',                 'trac_dependencies' );
add_action( 'wp_before_admin_bar_render', 'trac_adminbar_titles' );
add_action( 'wp_head', 					  'trac_admin_bar_styles' );

// filters
add_filter( 'show_admin_bar',             'trac_admin_bar_permissions' );
add_filter( 'gettext',                    'trac_replace_howdy', 10, 3 );
add_filter( 'admin_footer_text',          'trac_admin_footer' );
add_filter( 'screen_options_show_screen', 'trac_remove_screen_options' );

/**
 * Modify the admin bar left label
 */
function trac_adminbar_titles()
{
	$GLOBALS['wp_admin_bar']->add_menu( array(
		'id'    => 'site-name',
		'title' => ( is_admin() ? "Home" : "Admin Area" ),
	) );
}

/**
 * Change admin area left icon to odometer
 */
function trac_admin_bar_styles()
{
	if ( is_user_logged_in() )
	{
	    echo "<style type='text/css'>
	    #wpadminbar #wp-admin-bar-site-name>.ab-item:before {
			content: '\\f226';
			top: 2px;
		}
		</style>";
	}
}

/**
 * Check for dependencies
 */
function trac_dependencies()
{
	if ( ! class_exists( 'acf' ) )
	{
		add_action( 'admin_notices', 'trac_acf_dependency_message' );
	}
}

/**
 * Add a nag for required dependencies that are missing
 */
function trac_acf_dependency_message()
{
	?>
	<div class="update-nag">
		This theme requires the <a href="http://wordpress.org/plugins/advanced-custom-fields/">Advanced Custom Fields</a> plugin to be installed and activated.
	</div>
	<?php
}


/**
 * Disable default dashboard widgets.
 */
function trac_disable_dashboard_widgets()
{

	remove_meta_box('dashboard_right_now', 'dashboard', 'core');    	// Right Now Widget
	remove_meta_box('dashboard_incoming_links', 'dashboard', 'core'); 	// Incoming Links Widget
	remove_meta_box('dashboard_plugins', 'dashboard', 'core');			// Plugins Widget
	remove_meta_box('dashboard_quick_press', 'dashboard', 'core');		// Quick Press Widget
	remove_meta_box('dashboard_recent_drafts', 'dashboard', 'core');	// Recent Drafts Widget
	remove_meta_box('dashboard_activity', 'dashboard', 'core');			// Activity Widget
	remove_meta_box('dashboard_primary', 'dashboard', 'core');			// WordPress News Widget

	// removing plugin dashboard boxes
	remove_meta_box('yoast_db_widget', 'dashboard', 'normal');			// Yoast's SEO Plugin Widget
	remove_meta_box('tribe_dashboard_widget', 'dashboard', 'normal');	// Modern Tribe Plugin Widget
	remove_meta_box('rg_forms_dashboard', 'dashboard', 'normal');		// Gravity Forms Plugin Widget
	remove_meta_box('bbp-dashboard-right-now', 'dashboard', 'core');	// bbPress Plugin Widget

}

/**
 * Add a developer favicon
 */
function trac_admin_favicon()
{
	?>
	<link rel="icon" href="<?php echo get_template_directory_uri() . '/admin/assets/img/favicon.png'; ?>">
	<!--[if IE]>
		<link rel="shortcut icon" href="<?php echo get_template_directory_uri() . '/admin/assets/img/favicon.ico'; ?>">
	<![endif]-->
	<?php
}

/**
 * Remove some screen options from the dashboard
 */
function trac_dashboard_welcome_cleanup()
{
	global $pagenow;

	if ( 'index.php' == $pagenow )
	{
		?>
		<style type="text/css">
			.welcome-panel-column h4,
			.welcome-panel-last,
			.hide-if-no-customize {display: none !important;}
		</style>
		<?php
	}
}

/**
 * Remove some admin pages that we never want
 */
function trac_remove_menu_pages()
{
	remove_menu_page('link-manager.php');
	if (! current_user_can('manage_options') ) remove_menu_page('tools.php');
}

/**
 * Remove top admin menu items
 *
 * @return	void
 */
function trac_customize_admin_bar()
{
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('search');
	$wp_admin_bar->remove_menu('wp-logo');
	$wp_admin_bar->remove_menu('about');
	$wp_admin_bar->remove_menu('wporg');
	$wp_admin_bar->remove_menu('documentation');
	$wp_admin_bar->remove_menu('support-forums');
	$wp_admin_bar->remove_menu('feedback');
	$wp_admin_bar->remove_menu('view-site');
	$wp_admin_bar->remove_menu('new-content');
	$wp_admin_bar->remove_menu('new-link');
	$wp_admin_bar->remove_menu('new-media');
	$wp_admin_bar->remove_menu('new-user');
	if(!is_admin()){ 
		$wp_admin_bar->remove_menu('my-account'); // removes my account bar from top right
	}
}

/**
 * Force the admin bar on for editors and admins and off for below
 *
 * @return	array Modified settings
 */
function trac_admin_bar_permissions( $content )
{
	return ( current_user_can('edit_others_posts') ) ? $content : false;
}

/**
 * Replace howdy in the admin bar
 *
 * @return	string Modified welcome message.
 */
function trac_replace_howdy( $translated, $text, $domain )
{
	if ( false !== strpos($translated, "Howdy") )
	{
		return str_replace("Howdy", "Welcome back", $translated);
	}
	return $translated;
}

/**
 * Customize admin footer
 */
function trac_admin_footer()
{
	?>
	<span id="footer-thankyou">Crafted with WordPress by <a href="<?php echo trac_DEVELOPER_URL; ?>" target="_blank"><?php echo trac_DEVELOPER; ?></a></span>
	<?php
}

/**
 * Remove screen options from the dashboard
 */
function trac_remove_screen_options()
{
	global $pagenow;

	if ( 'index.php' == $pagenow ) return false;
	return true;
}
