<?php

/**
 * @link              http://www.TracFlo.io/
 * @since             1.0.0
 * @package           TracFlo_Tickets
 *
 * @wordpress-plugin
 * Plugin Name:       TracFlo Subcontractor: Users
 * Plugin URI:        http://www.TracFlo.io/
 * Description:       Adds user functionality.
 * Version:           1.0.4
 * Author:            TracFlo
 * Author URI:        http://www.TracFlo.io/
 * Contributor:       Jake Snyder
 */

/*

	Roles

	Owner - Administrator who owns the account and can cancel it (cancel not built yet)

	Administrator
	------------------
	WP Role: Editor
	Capabilities
		- Create Clients
		- View all Clients
		- Update all Clients
		- Archive all Clients
		- Create Projects
		- View all Projects
		- Update all Projects
		- Archive all Projects
		- Create COs
		- View all COs
		- Update all COs
		- Add payments to CO
		- View CO history
		- Create Tickets
		- View all Tickets
		- Update all Tickets
		- View Ticket history
		- Projects
			- Create
			- View all
			- Update all
			- Archive all
		- COs
			- Create
			- View all
			- Update own
			- View history
		- Tickets
			- Create
			- View all
			- Update own
			- View history
		- Manage Settings
		- Billing
	Variable Capabilities
		- Billing
		

	Project Manager
	------------------
	WP Role: Editor
	All projects
	Capabilities
		- Clients
			- N/A
		- Projects
			- Create
			- View all
			- Update all
			- Archive all
		- COs
			- Create
			- View all
			- Update own
			- View history
		- Tickets
			- Create
			- View all
			- Update own
			- View history
		- Users?
			- Foreman
				- Create
				- Assign Projects
			- Superintendent?
				- Create
				- Assign Projects
	Variable Capabilities
		- Create projects
		- Users

	Superintendent
	------------------
	WP Role: Author
	Assigned to project(s)
	Capabilities
		- Clients
			- N/A
		- Projects
			- View assigned projects
		- COs
			- Create in assigned projects
			- View all in assigned projects
			- Update own
			- View history
		- Tickets
			- Create in assigned projects
			- View all in assigned projects
			- Update own
			- View history

	Foreman
	------------------
	WP Role: Contributor
	Assigned to project(s)
	Capabilities
		- Tickets
			- Create
			- View all in assigned projects
			- Update own
			- View history
		- Create tickets in assigned projects
		- View own tickets
		- Update own tickets
		- View History
	No BIC

*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists('TracFlo_Base') && file_exists(__DIR__ . '/tracflo-base.php') ) {
	include( __DIR__ . '/tracflo-base.php' );
}

$class_name = 'TracFlo_User';
if ( ! class_exists($class_name) && class_exists('TracFlo_Base') ) :

class TracFlo_User extends TracFlo_Base
{
	public function __construct() {
		parent::__construct( strtolower(__CLASS__), __FILE__ );
	}

	/**
	 * Class settings
	 *
	 * @author  Jake Snyder
	 * @since	1.0.1
	 * @return	void
	 */
	public function settings()
	{
		$this->settings['pages'] = [
			'profile' => 'Update Profile',
		];
/** /
		remove_role( 'trac_admin' );
		remove_role( 'trac_pm' );
		remove_role( 'trac_super' );
		remove_role( 'trac_foreman' );
/**/
		$this->settings['roles'] = [
			[
				'role'                    => 'trac_admin',
				'display_name'            => 'TracFlo Administrator',
				'capabilities'            => [
					'read'                   => true,
					'create_posts'           => true,
					'edit_posts'             => true,
					'edit_published_posts'   => true,
					'edit_others_posts'      => true,
					'delete_posts'           => true,
					'delete_published_posts' => true,
					'publish_posts'          => true,
					'upload_files'           => true,
					'create_users'           => true,
					'edit_users'             => true,
					'delete_users'           => true,
					'trac_manage_settings'   => true,
					'trac_edit_clients'      => true,
					'trac_edit_projects'     => true,
					'trac_edit_cos'          => true,
					'trac_edit_tickets'      => true,
					'trac_edit_rates'        => true,
				],
			], [
				'role'                    => 'trac_pm',
				'display_name'            => 'Project Manager',
				'capabilities'            => [
					'read'                   => true,
					'edit_posts'             => true,
					'edit_published_posts'   => true,
					'edit_others_posts'      => true,
					'create_posts'           => true,
					'delete_posts'           => true,
					'delete_published_posts' => true,
					'publish_posts'          => true,
					'upload_files'           => true,
					'trac_edit_clients'      => true,
					'trac_edit_projects'     => true,
					'trac_edit_cos'          => true,
					'trac_edit_tickets'      => true,
					'trac_edit_rates'        => true,
				],
			],
/** /
			[
				'role'                    => 'trac_super',
				'display_name'            => 'Superintendent',
				'capabilities'            => [
					'read'                   => true,
					'create_posts'           => true,
					'edit_posts'             => true,
					'edit_published_posts'   => true,
					'delete_posts'           => true,
					'delete_published_posts' => true,
					'publish_posts'          => true,
					'upload_files'           => true,
					'trac_edit_projects'     => true,
					'trac_edit_cos'          => true,
					'trac_edit_tickets'      => true,
				],
			],
/**/
			[
				'role'                    => 'trac_foreman',
				'display_name'            => 'Foreman',
				'capabilities'            => [
					'read'                   => true,
					'create_posts'           => true,
					'edit_posts'             => true,
					'edit_published_posts'   => true,
					'delete_posts'           => true,
					'delete_published_posts' => true,
					'publish_posts'          => true,
					'upload_files'           => true,
					'trac_edit_tickets'      => true,
					'trac_edit_timesheets'   => true,
				],
			], [
				'role'                    => 'trac_worker',
				'display_name'            => 'Worker',
				'capabilities'            => [
					'read'                   => true,
					'create_posts'           => true,
					'edit_posts'             => true,
					'edit_published_posts'   => true,
					'delete_posts'           => true,
					'delete_published_posts' => true,
					'publish_posts'          => true,
					'upload_files'           => true,
				],
			],
		];

		#register_activation_hook( __FILE__, [ $this, 'activation' ] );
	}

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.1
	 * @return	void
	 */
	public function init()
	{
		$this->add_roles();
		add_action( 'acf/save_post',                  [ $this, 'update_userdata' ], 30 );
		add_filter( 'acf/load_value/name=role',       [ $this, 'load_value_user_role' ], 10, 3 );
		add_filter( 'acf/load_field/name=role',       [ $this, 'load_field_user_role' ] );
		add_filter( 'acf/load_value/name=user_email', [ $this, 'load_value_user_email' ], 10, 3 );

		add_filter( 'acf/load_value/name=first_name', [ $this, 'load_value_new_user' ], 10, 3 );
		add_filter( 'acf/load_value/name=last_name',  [ $this, 'load_value_new_user' ], 10, 3 );
		add_filter( 'acf/load_value/name=user_email', [ $this, 'load_value_new_user' ], 20, 3 );
		add_filter( 'acf/load_value/name=role',       [ $this, 'load_value_new_user' ], 20, 3 );

		add_filter( 'acf/fields/user/result',         [ $this, 'format_user_field_user_name' ], 20, 4 );

		add_action( 'pre_get_posts',                  [ $this, 'restrict_posts_by_user' ] );
	}

	public function format_user_field_user_name( $result, $user, $field, $post_id ) {
		$result = $user->first_name;
		$result .= ' ' . $user->last_name;

		if ( ! empty($user->user_email) ) {
			$result .= ' (' . $user->user_email . ')';
		}

		return $result;
	}

	public function restrict_posts_by_user( $query ) {
		$current_user = wp_get_current_user();
		if ( ! is_admin() && is_archive() && $query->is_main_query() ) {
			// Foreman only sees their own posts
			if ( in_array( 'trac_foreman', (array) $current_user->roles ) ) {
				$query->set( 'author', $current_user->ID );
			}
		}
	}

	/**
	 * Set the email field to the user's email for editing
	 *
	 * @author  Jake Snyder
	 * @since	1.0.3
	 * @return	array $value
	 */
	public function load_value_user_email( $value, $post_id, $field ) {
		if ( false !== strpos($post_id, 'user_') && $user_id = str_replace('user_', '', $post_id) ) {
			$userdata = get_userdata( $user_id );
			if ( $userdata ) {
				$value = $userdata->user_email;
			}
		}
	
	    return $value;
	}
	
	/**
	 * Get plugin templates unless they are overridden in the theme
	 *
	 * @author  Jake Snyder
	 * @since	1.0.3
	 * @return	array $value
	 */
	public function load_value_new_user( $value, $post_id, $field ) {
		if ( 'new_user' === $post_id ) { return ''; }
	    return $value;
	}

	/**
	 * Get plugin templates unless they are overridden in the theme
	 *
	 * @author  Jake Snyder
	 * @since	1.0.2
	 * @param	string $template Template file path
	 * @return	array $template
	 */
	public function template_include( $template )
	{
		$new_template = null;
		if ( is_page( 'profile' ) ) {
			$new_template = apply_filters( "{$this->prefix}/{$this->plugin_name}/locate_template", "page-profile.php" );
		}
		if ( $new_template ) {
			return $new_template;
		}
		return $template;
	}

	/**
	 * Prefill role
	 *
	 * @author  Jake Snyder
	 * @since	1.0.1
	 * @return	void
	 */
	public function load_value_user_role( $value, $post_id, $field ) {
		if ( false !== strpos($post_id, 'user_') ) {
			$user_id   = str_replace( 'user_', '', $post_id );
			$user_meta = get_userdata( $user_id );
			$user_role = str_replace( 'trac_', '', $user_meta->roles[0] );
			if ( ! empty($field['choices']) && array_key_exists($user_role, $field['choices']) ) {
				$value = $user_role;
			}
		} elseif ( ! empty($_GET['role']) ) {
			$value = esc_attr( $_GET['role'] );
		}
		return $value;
	}

	/**
	 * Only load the role field for admins
	 *
	 * @author  Jake Snyder
	 * @since	1.0.2
	 * @return	void
	 */
	public function load_field_user_role( $field )
	{
		if ( current_user_can( 'trac_manage_settings' ) ) {
			return $field;
		}
	}

	/**
	 * On initial creation of a user, create user and update info
	 *
	 * @author  Jake Snyder
	 * @since	1.0.3
	 * @return	void
	 */
	public function update_userdata( $post_id ) {
		// Require email field, and that this is a user
		if ( empty($_POST['acf']) || empty($_POST['acf']['field_599c48218c0ab']) || false === strpos($post_id, 'user') ) { return; }

		$user_email = $_POST['acf']['field_599c48218c0ab'];

		$role = 'subscriber';
		if ( ! empty($_POST['acf']['field_5aa9fd1b0581e']) ) {
			switch ( $_POST['acf']['field_5aa9fd1b0581e'] ) {
				case 'admin' :
					$role = 'trac_admin';
					break;
				case 'pm' :
					$role = 'trac_pm';
					break;
				case 'super' :
					$role = 'trac_super';
					break;
				case 'foreman' :
					$role = 'trac_foreman';
					break;
				case 'worker' :
				default :
					$role = 'trac_worker';
					break;
			}
		}
	
		if ( 'new_user' === $post_id ) {
			if ( email_exists( $user_email ) ) { return false; }
			$length = 13;
			$include_standard_special_chars = false;
			$random_password = wp_generate_password( $length, $include_standard_special_chars );
			$user_id = wp_create_user( $user_email, $random_password, $user_email );
			wp_update_user([
				'ID'           => $user_id,
				'first_name'   => ( ! empty($_POST['acf']['field_599c479c8c0a9']) ? $_POST['acf']['field_599c479c8c0a9'] : '' ),
				'last_name'    => ( ! empty($_POST['acf']['field_599c480e8c0aa']) ? $_POST['acf']['field_599c480e8c0aa'] : '' ),
				'display_name' => ( ! empty($_POST['acf']['field_599c479c8c0a9']) ? $_POST['acf']['field_599c479c8c0a9'] : '' ) . ' ' . ( ! empty($_POST['acf']['field_599c480e8c0aa']) ? $_POST['acf']['field_599c480e8c0aa'] : '' ),
				'role'         => $role,
			]);

			// SEND EMAIL
			$user       = new WP_User( (int) $user_id );
			$adt_rp_key = get_password_reset_key( $user );
			$user_login = $user->user_login;
			$rp_link    = '<a href="' . network_site_url( "wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login), 'login') . '">' . network_site_url( "wp-login.php?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login), 'login' ) . '</a>';

			ob_start();
?>
			<p><?php echo $user->user_firstname; ?>,</p>
			<p><b><?php _e( 'Your TracFlo account is active.', 'trac' ); ?></b></p>
			<div id="signup-welcome">
			    <p><?php _e( 'Email Address:', 'trac' ); ?> <?php echo $user->user_email; ?></p>
			    <p><?php _e( 'To set your password, click this link:', 'trac' ); ?> <?php echo $rp_link; ?></p>
			</div>
			<p><?php _e( 'After your password is created, <b>log in:</b> <a href="' . home_url('/') . '">' . home_url('/') . '</a>.', 'trac' ); ?>
<?php
			$content = ob_get_clean();

			$current_user = wp_get_current_user();

			$to      = $current_user->user_email;
			$subject = __( 'Your TracFlo account is active', 'trac' );
			$body    = $content;
			$headers = [
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . $current_user->display_name . ' <info@tracflo.io>',#trac_option( 'name' )
				'Reply-To: ' . $current_user->display_name . ' <' . $current_user->user_email . '>',
			];
			wp_mail($to, $subject, $body, $headers );

		} else {
			$user_id = str_replace('user_', '', $post_id);
			$args    = [
				'ID'           => $user_id,
				'user_email'   => $user_email,
				'display_name' => ( ! empty($_POST['acf']['field_599c479c8c0a9']) ? $_POST['acf']['field_599c479c8c0a9'] : '' ) . ' ' . ( ! empty($_POST['acf']['field_599c480e8c0aa']) ? $_POST['acf']['field_599c480e8c0aa'] : '' ),
			];
			if ( 'subscriber' !== $role ) {
				$args['role'] = $role;
			}
			wp_update_user($args);
		}

		return $post_id;
	}

	/**
	 * Plugin activation
	 *
	 * @author  Jake Snyder
	 * @since	1.0.1
	 * @return	void
	 */
	public function activation()
	{
		$this->add_roles();
	}

	/**
	 * Add TracFlo Roles
	 *
	 * @author  Jake Snyder
	 * @since	1.0.1
	 * @return	void
	 */
	protected function add_roles()
	{
		if ( ! empty($this->settings['roles']) ) {
			foreach ( $this->settings['roles'] as $role ) {
				add_role( $role['role'], $role['display_name'], $role['capabilities'] );
			}
		}
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;
