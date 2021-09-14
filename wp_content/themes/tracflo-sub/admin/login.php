<?php
/**
 * Customize the login screen
 */
add_action( 'login_init', 'trac_login_init' );
function trac_login_init()
{
	add_action( 'login_enqueue_scripts', 'trac_login_css' );
	add_action( 'login_header',          'trac_login_header' );
	add_filter( 'login_message',         'trac_login_message' );
	add_filter( 'login_headerurl',       'trac_login_url' );
	add_filter( 'login_headertitle',     'trac_login_title' );
}


/**
 * Add company info and tracflo to login page
 */
function trac_login_message() {
	$host = $_SERVER['HTTP_HOST'];
	$host_array = explode( '.', $host );
	if ( 3 === count($host_array) ) :
?>
	<div class="headline center">
		<p>Sign in to TracFlo account:</p>
		<h1><?php echo trac_option( 'name' ); ?></h1>
	</div>
<?php
	endif;
}


/**
 * Add company logo if uploaded
 */
function trac_login_header() {
	if ( $logo = trac_option( 'logo' ) ) {
?>
		<style>
			.login h1 a {
				background-image: url(<?php echo esc_url( $logo ); ?>);
			}
		</style>
<?php
	}
}


/**
 * Add theme log in css
 */
function trac_login_css()
{
	wp_enqueue_style( 'trac_admin_login', get_template_directory_uri() . '/assets/css/login.css', false );
}

/**
 * Change the logo link from wordpress.org to the site home
 */
function trac_login_url()
{
	return home_url( '/' );
}

/**
 * Change the alt text on the logo to site name
 */
function trac_login_title()
{
	return get_option('blogname');
}
