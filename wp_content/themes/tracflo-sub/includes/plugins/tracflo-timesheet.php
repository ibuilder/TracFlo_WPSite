<?php

/**
 * @link              http://www.TracFlo.io/
 * @since             1.0.0
 * @package           TracFlo_Timesheet
 *
 * @wordpress-plugin
 * Plugin Name:       TracFlo Subcontractor: Timesheets
 * Plugin URI:        http://www.TracFlo.io/
 * Description:       Adds timesheet functionality.
 * Version:           1.0.0
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

$class_name = 'TracFlo_Timesheet';
if ( ! class_exists($class_name) && class_exists('TracFlo_Base') ) :

class TracFlo_Timesheet extends TracFlo_Base
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
			"add-{$this->plugin_name}" => 'Add Timesheet',
			"reports" => 'Reports',
		];
		$this->settings['post_types'] = [
			'timesheet'    => [
				'labels'              => [
					'name'               => __( 'Timesheets', $this->plugin_name ),
					'singular_name'      => __( 'Timesheet', $this->plugin_name ),
					'all_items'          => __( 'All Timesheets', $this->plugin_name ),
					'add_new'            => __( 'Add New', $this->plugin_name ),
					'add_new_item'       => __( 'Add New Timesheet', $this->plugin_name ),
					'edit'               => __( 'Edit', $this->plugin_name  ),
					'edit_item'          => __( 'Edit Timesheet', $this->plugin_name ),
					'new_item'           => __( 'New Timesheet', $this->plugin_name ),
					'view_item'          => __( 'View Timesheet', $this->plugin_name ),
					'search_items'       => __( 'Search Timesheets', $this->plugin_name ), 
					'not_found'          => __( 'Nothing found in the Database.', $this->plugin_name ), 
					'not_found_in_trash' => __( 'Nothing found in Trash', $this->plugin_name ),
					'parent_item_colon'  => '',
				],
				'description'         => __( 'Timesheets.', $this->plugin_name ),
				'menu_icon'           => 'dashicons-clock',
				'public'              => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'show_ui'             => true,
				'query_var'           => true,
				'rewrite'	          => [ 'slug' => 'timesheet', 'with_front' => false ],
				'has_archive'         => 'timesheets',
				'menu_position'       => 57,
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => [ 'title', 'author', 'revisions' ],
				'custom_enter_title'  => __( 'Timesheet Reference', $this->plugin_name ),
				'columns'             => [
					#'delete'              => [ 'date', 'author' ],
					'add'                 => [
						'project'     => 'Project',
// 						'total'       => 'Total',
					],
				],
			],
		];
	}

	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function init()
	{
		// WP Admin
		add_filter( 'custom_column_timesheet_project',         [ $this, 'column_project' ], 10, 4 );

		// Saving & Loading Posts
		add_action( 'pre_get_posts',                           [ $this, 'pre_get_posts' ] );
		add_action( 'save_post',                               [ $this, 'save_post' ], 99 );

		// ACF
		add_action( 'acf/save_post',                           [ $this, 'acf_save_post' ], 99 );
		add_filter( 'acf/load_field/key=field_5c8b64bf656a8',  [ $this, 'default_start_date' ] );
		add_filter( 'acf/fields/post_object/query/key=field_5c8b64bf6563f', [ $this, 'limit_project_posts' ], 10, 3 );
		add_filter( 'acf/load_value/key=field_5c8b64bf6598f',  [ $this, 'default_workers' ], 10, 3 );
		add_filter( 'acf/load_field/key=field_5c8b64bf7d35b',  [ $this, 'default_hours' ], 10, 3 );

		add_filter( 'acf/load_field/key=field_5c8b64bf6563f',  [ $this, 'load_project' ] );
	}

	/**
	 * When the project field on the form loads restrict projects to user, adjust query
	 *
	 * @author  Jake Snyder
	 * @since	1.0.4
	 * @return	void
	 */
	public function limit_project_posts( $args, $field, $post ) {
		if ( current_user_can( 'trac_foreman' ) ) {// || current_user_can( 'trac_pm' )
			$projects = New WP_Query([
				'meta_query' => [
					[
						'key'     => 'team_$_foreman',
						'value'   => get_current_user_id(),
						'compare' => '=',
					],
				],
				'post_type' => 'project',
				'posts_per_page' => -1,
			]);
/**/
			if ( $projects->posts ) {
				$ids = [];
				foreach ( $projects->posts as $project ) {
					$ids[] = $project->ID;
				}
				$args['post__in'] = $ids;
			}
/**/
		}
		return $args;
	}

	/**
	 * If only one project, select it
	 *
	 * @author  Jake Snyder
	 * @since	1.0.6
	 * @return	void
	 */
	public function load_project( $field ) {
		if ( ! is_admin() && is_page() && (current_user_can( 'trac_foreman' ) || current_user_can( 'trac_pm' )) ) {
			$projects = New WP_Query([
				'meta_query' => [
					[
						'key'     => 'team_$_foreman',
						'value'   => get_current_user_id(),
						'compare' => '=',
					]
				],
				'post_type' => 'project',
				'posts_per_page' => -1,
			]);

			if ( $projects->posts ) {
				if ( 1 === count($projects->posts) ) {
					$field['value'] = $projects->post->ID;
				} elseif ( ! empty($_GET['pid']) ) {
					foreach ( $projects->posts as $project ) {
						if ( $project->ID == $_GET['pid'] ) {
							$field['value'] = $project->ID;
						}
					}
				}
			}
		}
		return $field;
	}

	/**
	 * Pre get posts, adjust ticket archives
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function pre_get_posts( $query )
	{
		if ( ! is_admin() && $query->is_main_query() && is_post_type_archive('timesheet') ) {
			$query->set( 'posts_per_page', -1 );
			$query->set( 'meta_key', 'project' );

			if ( ! empty($_REQUEST['yid']) ) {
				$query->set( 'year', esc_sql( $_REQUEST['yid'] ) );
			}

			if ( current_user_can( 'trac_foreman' ) ) {
				$projects          = get_posts([
					'meta_query'     => [
						[
							'key'          => 'team_0_foreman',
							'value'        => get_current_user_id(),
							'compare'      => '=',
						],
					],
					'post_type'      => 'project',
					'posts_per_page' => -1,
				]);
				if ( $projects ) {
					$ids = [];
					foreach ( $projects as $project ) {
						$ids[] = $project->ID;
					}
					if ( ! empty($_REQUEST['pid']) && in_array($_REQUEST['pid'], $ids) && $post = get_post(esc_sql( $_REQUEST['pid'] )) ) {
						$query->set( 'meta_value_num', esc_sql( $post->ID ) );
					} else {
						$query->set( 'meta_value', $ids );
						$query->set( 'meta_compare', 'IN' );
					}
				}
			}

			if ( ! empty($_REQUEST['from']) ) {
				$date_start = str_replace( '-', '', $_REQUEST['from'] );
			} else {
				$date_start = date( 'Ymd', strtotime('monday this week') );
			}
			if ( ! empty($_REQUEST['to']) ) {
				$date_end = str_replace( '-', '', $_REQUEST['to'] );
			} else {
				$date_end = date( 'Ymd', strtotime('sunday this week') );
			}
			if ( ! empty($_REQUEST['type']) ) {
				$date_type = $_REQUEST['type'];
			} else {
				$date_type = 'custom';
			}

			$query->set( 'meta_query', [
				[
					'key'		=> 'date',
					'compare'	=> '>=',
					'value'		=> esc_sql( $date_start ),
				], [
					'key'		=> 'date',
					'compare'	=> '<=',
					'value'		=> esc_sql( $date_end ),
				],
			]);

			$query->set( 'orderby', [ 'meta_value_num' => 'DESC', 'title' => 'DESC' ] );
			return;
		}
	}

	/**
	 * Use site default hours option setting
	 *
	 * @author  Jake Snyder
	 * @since	1.0.10
	 * @return	void
	 */
	public function default_hours( $field ) {
		$field['default_value'] = get_option( 'options_default_hours', '8' );
		return $field;
	}

	public function default_workers( $value, $post_id, $field ) {
		if ( ! is_numeric($post_id) ) {
			$default_hours = get_option( 'options_default_hours', '8' );

			$value[] = [
				'field_5c8b64bf7d138' => '',
				'field_5c8b64bf7d35b' => $default_hours,
			];

			if ( ! empty($_GET['pid']) ) {
/*
				global $wpdb;
				$sql      = "SELECT * FROM $wpdb->postmeta WHERE `post_id` = " . esc_sql($_GET['pid']) . " && `meta_key` LIKE 'team_%_foreman' && `meta_value` = " . get_current_user_id();
				$projects = $wpdb->get_results( $sql );
*/
				$teams = get_field( 'team', esc_sql($_GET['pid']) );
				if ( $teams ) {
					foreach ( $teams as $team ) {
						if ( get_current_user_id() == $team['foreman'] && ! empty($team['workers']) ) {
							$value = [];
							$value[] = [
								'field_5c8b64bf7d138' => $team['foreman'],
								'field_5c8b64bf7d35b' => $default_hours,
								'field_5cb9ec6038f86' => 0,
							];
							foreach ( $team['workers'] as $worker_id ) {
								$value[] = [
									'field_5c8b64bf7d138' => $worker_id,
									'field_5c8b64bf7d35b' => $default_hours,
									'field_5cb9ec6038f86' => 0,
								];
							}
							break;
						}
					}
				}
			}
		}
		return $value;
	}

	/**
	 * Pick today's date for start date picker
	 *
	 * @author  Jake Snyder
	 * @since	1.0.10
	 * @return	void
	 */
	public function default_start_date( $field ) {
		$field['default_value'] = date('Ymd');
		return $field;
	}

	/**
	 * Update the ticket total into meta
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function update_totals( $post_id ) {
		if ( empty($_POST['acf']) || empty($_POST['acf']['field_5c8b64bf6598f']) || empty($_POST['acf']['field_5c8b64bf656a8']) ) { return; }

		$fields       = $_POST['acf'];
		$work_date    = $fields['field_5c8b64bf656a8'];#get_field( 'date', $post_id );
		$project      = $fields['field_5c8b64bf6563f'];
		$worker_hours = [];
		foreach ( $fields['field_5c8b64bf6598f'] as $data ) {
			$worker_hours[] = [
				'worker'   => $data['field_5c8b64bf7d138'],
				'hours'    => $data['field_5c8b64bf7d35b'],
				'overtime' => $data['field_5cb9ec6038f86'],
			];
		}

		$total_time  = [
			'hours'    => 0,
			'overtime' => 0,
			'total'    => 0,
		];
		$total_users     = 0;
		$timesheet_final = [];

		if ( $worker_hours ) {
			foreach ( $worker_hours as $worker_time ) {
				if ( $worker_time['worker'] && is_numeric($worker_time['hours']) && 0 < $worker_time['hours'] ) {
					$user_id = esc_sql($worker_time['worker']);
					if ( $user = get_user_by( 'id', $user_id ) ) {

						// Update user time total
						if ( ! ($user_total_time = get_user_meta( $user_id, 'timesheet_total', true )) || ! is_array($user_total_time) ) {
							$user_total_time  = [
								'hours'              => 0,
								'overtime'           => 0,
								'total'              => 0,
							];
						}
						$user_total_time['hours']    += (int) $worker_time['hours'];
						$user_total_time['overtime'] += (int) $worker_time['overtime'];
						$user_total_time['total']    += (int) $worker_time['hours'];
						$user_total_time['total']    += (int) $worker_time['overtime'];

						// Add a new entry for user timesheet
						$user_timesheet = [
							'project' => $project,
							'date'  => $work_date,
							'hours' => $worker_time['hours'],
							'overtime' => $worker_time['overtime'],
							'total' => $worker_time['hours'] + $worker_time['overtime'],
						];
						$user_timesheet_existing = get_user_meta( $user_id, 'timesheet_' . $work_date, true );
						if ( $user_timesheet_existing ) {
							$user_timesheet_entry = json_decode($user_timesheet_existing);
							if ( ! empty($user_timesheet_entry->hours) ) {
								$user_total_time['hours']    -= $user_timesheet_entry->hours;
							}
							if ( ! empty($user_timesheet_entry->overtime) ) {
								$user_total_time['overtime'] -= $user_timesheet_entry->overtime;
							}
							if ( ! empty($user_timesheet_entry->total) ) {
								$user_total_time['total']    -= $user_timesheet_entry->total;
							}
						}
						add_user_meta( $user_id, 'timesheet_' . $work_date, json_encode($user_timesheet), true );
						update_user_meta( $user_id, 'timesheet_total', $user_total_time );

						// Add static record with user's name in case users are ever deleted
						$timesheet_final[] = [
							'name'    => $user->first_name . ' ' . $user->last_name,
							'email'   => $user->user_email,
							'hours'   => $worker_time['hours'],
							'user_id' => $user_id,
						];
						$total_users++;
						$total_time['hours']    += (int) $worker_time['hours'];
						$total_time['overtime'] += (int) $worker_time['overtime'];
						$total_time['total']    += (int) $worker_time['hours'];
						$total_time['total']    += (int) $worker_time['overtime'];
					}
				}
			}
		}

		update_post_meta( $post_id, 'total_users', $total_users );
		update_post_meta( $post_id, 'total_time', $total_time );
		update_post_meta( $post_id, 'timesheet_final', json_encode($timesheet_final) );
	}

	/**
	 * Update the timesheet meta and attached users
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function update_timesheet( $post_id ) {
		$this->update_totals( $post_id );
	}

	/**
	 * Update the ticket when saved
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function save_post( $post_id ) {
		// Add date to timesheet title
		if ( 'timesheet' === get_post_type( $post_id ) ) {
			$this->update_timesheet( $post_id );
			$this->create_title( $post_id );
		}
	}

	/**
	 * Update attached tickets when co is saved
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function acf_save_post( $post_id ) {
		$this->save_post( $post_id );
	}

	/**
	 * Update the ticket title with number and subject if one exists
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function create_title( $post_id ) {
		global $wpdb;

		$date  = get_post_meta( $post_id, 'date', true );

		$wpdb->update(
			$wpdb->posts,
			[ 'post_title' => date_i18n( 'm/d/Y', strtotime($date) ) ],
			[ 'ID' => $post_id ],
			[ '%s' ],
			[ '%d' ]
		);
	}

	/**
	 * Adding the project
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function column_project( $value, $post_id, $name, $post_type )
	{
		return ( $value ) ? '<a href="' . esc_url( get_edit_post_link( $value ) ) . '">' . esc_html( get_the_title( $value ) ) . '</a>' : "&mdash;";
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;
