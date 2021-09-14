<?php

/**
 * @link              http://www.TracFlo.io/
 * @since             1.0.6
 * @package           TracFlo_History
 *
 * @wordpress-plugin
 * Plugin Name:       TracFlo History
 * Plugin URI:        http://www.TracFlo.io/
 * Description:       Adds history functionality.
 * Version:           1.0.0
 * Author:            TracFlo
 * Author URI:        http://www.TracFlo.io/
 * Contributor:       Jake Snyder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$class_name = 'TracFlo_History';
if ( ! class_exists($class_name) ) :

class TracFlo_History
{

	/**
	 * Load the plugin.
	 *
	 * @since	1.0.0
	 * @return	void
	 */
	public function run()
	{
		add_action( 'init', [ $this, 'init' ] );
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
		add_action( 'trac/history/add',  [ $this, 'add_history' ], 10, 3 );
		add_filter( 'trac/history/sort', [ $this, 'sort_by_date' ] );
		add_filter( 'trac/history/get',  [ $this, 'get_history' ], 10, 2 );
		add_filter( 'trac/history/full', [ $this, 'get_full_history' ] );
		add_filter( 'trac/history/note', [ $this, 'get_history_note' ], 10, 2 );
		add_action( 'trac/history/log',  [ $this, 'get_log' ] );
	}

	/**
	 * Add history record
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function add_history( $post_id, $note=null, $amount=null, $user=null )
	{
		$data = [
			'date'    => esc_sql( current_time('timestamp') ),
			'user'    => $user ? esc_sql( $user ) : get_current_user_id(),
			'note'    => ( $note ? esc_sql( $note ) : 'updated' ),
			'amount'  => ( $amount ? esc_sql( $amount ) : 0 ),
		];
		add_post_meta( $post_id, 'history', $data );
	}

	/**
	 * Sort items by a date col in array
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function sort_by_date( $items )
	{
		usort( $items, 'trac_order_items_by_date' );
		return $items;
	}

	/**
	 * Get history portions by type (eg: payment or history)
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function get_history( $post_id, $meta_key='history' ) {
		static $cache = [];
		if ( ! empty($cache[$post_id][$meta_key]) ) {
			return $cache[$post_id][$meta_key];
		}

		global $wpdb;
		$output = [];
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", esc_sql( $post_id ), esc_sql( $meta_key ) ) );
		if ( $rows ) {
			foreach ( $rows as $row ) {
				$info = maybe_unserialize( $row->meta_value );
				$info['id'] = $row->meta_id;
				$output[] = $info;
			}
		}

		if ( empty($cache[$post_id]) || ! is_array($cache[$post_id]) ) { $cache[$post_id] = []; }
		$cache[$post_id][$meta_key] = $output;

		return $output;
	}

	/**
	 * Sort items by a date col in array
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function get_full_history( $post_id ) {
		static $cache = [];
		if ( ! empty($cache[$post_id]) ) {
			return $cache[$post_id];
		}

		$history          = $this->get_history( $post_id, 'history' );
		$payments         = $this->get_history( $post_id, 'payment' );
		$full_history     = array_merge( $payments, $history );
		$full_history     = $this->sort_by_date( $full_history );
		$full_history     = array_reverse( $full_history );
		$full_history[]   = [
			'date' => strtotime( get_post_field( 'post_date', $post_id ) ),
			'note' => 'created',
			'user' => get_post_field( 'post_author', $post_id ),
		];

		$cache[$post_id] = $full_history;

		return $full_history;
	}

	/**
	 * Output the full history log to the page
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function get_log( $post_id ) {
		$full_history = $this->get_full_history( $post_id );

		include( get_stylesheet_directory() . '/partials/history-log.php' );
	}

	/**
	 * Get a note for history item
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function get_history_note( $history, $default = 'Item updated.' ) {
		if ( ! empty($history['date_paid']) ) {
			$output = 'Payment received on ' . esc_html( date_i18n( TRAC_DATE_FORMAT, $history['date_paid'] ) );
			if ( ! empty($history['po']) ) {
				$output .= '<span class="po">Purchase order: #' . esc_html( $history['po'] ) . '.</span>';
			}
			if ( ! empty($history['notes']) ) {
				$output .= '<em class="notes"> <br> ' . esc_html( $history['notes'] ) . '</em>';
			}
			return $output;
		} elseif ( ! empty($history['note']) ) {
			if ( 'deletepayment' == $history['note'] ) {
				return 'Payment deleted' . ( ! empty($history['amount']) ? ': ' . esc_html( trac_money_format( $history['amount'] ) ) : '' ) . '.';
			} elseif ( 'closed' == $history['note'] ) {
				return 'Item closed.';
			} elseif ( 'complete' == $history['note'] ) {
				return 'Work completed.';
			} elseif ( 'uncomplete' == $history['note'] ) {
				return 'Work changed to not completed.';
			} elseif ( 'opened' == $history['note'] ) {
				return 'Item opened.';
			} elseif ( 'created' == $history['note'] ) {
				return 'Item created.';
			} elseif ( 'updated' == $history['note'] ) {
				return $default;
			} else {
				return $history['note'];
			}
		} else {
			return $default;
		}
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;

function trac_order_items_by_date($a, $b) {
	if ( $a['date'] == $b['date'] ) {
		return 0;
	}
	return ( $a['date'] < $b['date'] ) ? -1 : 1;
}
