<?php

/**
 * @link              http://www.TracFlo.io/
 * @since             1.0.0
 * @package           TracFlo_Settings
 *
 * @wordpress-plugin
 * Plugin Name:       TracFlo Subcontractor: Settings
 * Plugin URI:        http://www.TracFlo.io/
 * Description:       Adds settings functionality.
 * Version:           1.0.6
 * Author:            TracFlo
 * Author URI:        http://www.TracFlo.io/
 * Contributor:       Jake Snyder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists('TracFlo_Base') && file_exists(__DIR__ . '/tracflo-base.php') ) {
	include( __DIR__ . '/tracflo-base.php' );
}

$class_name = 'TracFlo_Settings';
if ( ! class_exists($class_name) && class_exists('TracFlo_Base') ) :

class TracFlo_Settings extends TracFlo_Base
{
	public function __construct() {
		parent::__construct( strtolower(__CLASS__), __FILE__ );
	}

	/**
	 * Class settings
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function settings()
	{
		$this->settings['pages'] = [
			"{$this->plugin_name}" => 'Account Settings',
		];
	}

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.5
	 * @return	void
	 */
	public function init()
	{
		add_filter( 'acf/load_field/name=owner',   [ $this, 'load_field_owner' ] );
	}

	/**
	 * Only load the owner field for the owner
	 *
	 * @author  Jake Snyder
	 * @since	1.0.5
	 * @return	void
	 */
	public function load_field_owner( $field )
	{
		if ( is_admin() || get_option('options_owner') == get_current_user_id() ) {
			return $field;
		}
	}

	/**
	 * Get plugin templates unless they are overridden in the theme
	 *
	 * @author  Jake Snyder
	 * @since	1.0.3
	 * @param	string $template Template file path
	 * @return	array $template
	 */
	public function template_include( $template )
	{
		$new_template = null;
		if ( is_page( $this->plugin_name ) ) {
			$new_template = apply_filters( "{$this->prefix}/{$this->plugin_name}/locate_template", "page-{$this->plugin_name}.php" );
		}
		if ( $new_template ) {
			return $new_template;
		}
		return $template;
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;
