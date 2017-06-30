<?php
/**
 * Class for logging events and errors
 *
 * @package     Give
 * @subpackage  Classes/Give_Logging
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Logging Class
 *
 * A general use class for logging events and errors.
 *
 * @since 1.0
 */
class Give_Logging {
	/**
	 * Logs data operation handler object.
	 *
	 * @since  2.0
	 * @access private
	 * @var Give_DB_Logs
	 */
	public $log_db;

	/**
	 * Log meta data operation handler object.
	 *
	 * @since  2.0
	 * @access private
	 * @var Give_DB_Log_Meta
	 */
	public $logmeta_db;

	/**
	 * Class Constructor
	 *
	 * Set up the Give Logging Class.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function __construct() {
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-db-logs.php';
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-db-logs-meta.php';
		$this->log_db     = new Give_DB_Logs();
		$this->logmeta_db = new Give_DB_Log_Meta();

		// Setup hooks.
		add_action( 'save_post_give_payment', array( $this, 'background_process_delete_cache' ) );
		add_action( 'save_post_give_forms', array( $this, 'background_process_delete_cache' ) );
		add_action( 'save_post_give_log', array( $this, 'background_process_delete_cache' ) );
		add_action( 'give_delete_log_cache', array( $this, 'delete_cache' ) );
	}

	/**
	 * Log Types
	 *
	 * Sets up the default log types and allows for new ones to be created.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array $terms
	 */
	public function log_types() {
		$terms = array(
			'sale',
			'gateway_error',
			'api_request',
		);

		return apply_filters( 'give_log_types', $terms );
	}

	/**
	 * Check if a log type is valid
	 *
	 * Checks to see if the specified type is in the registered list of types.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $type Log type.
	 *
	 * @return bool         Whether log type is valid.
	 */
	public function valid_type( $type ) {
		return in_array( $type, $this->log_types() );
	}

	/**
	 * Create new log entry
	 *
	 * This is just a simple and fast way to log something. Use $this->insert_log()
	 * if you need to store custom meta data.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $title   Log entry title. Default is empty.
	 * @param  string $message Log entry message. Default is empty.
	 * @param  int    $parent  Log entry parent. Default is 0.
	 * @param  string $type    Log type. Default is null.
	 *
	 * @return int             Log ID.
	 */
	public function add( $title = '', $message = '', $parent = 0, $type = null ) {
		// @todo: add backward compatibility for old table.
		$log_data = array(
			'title'   => $title,
			'content' => $message,
			'parent'  => $parent,
			'type'    => $type,
		);

		return $this->log_db->add( $log_data );
	}

	/**
	 * Get Logs
	 *
	 * Retrieves log items for a particular object ID.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int    $object_id Log object ID. Default is 0.
	 * @param  string $type      Log type. Default is null.
	 * @param  int    $paged     Page number Default is null.
	 *
	 * @return array             An array of the connected logs.
	 */
	public function get_logs( $object_id = 0, $type = null, $paged = null ) {
		// @todo: add backward compatibility for old table.
		return $this->get_connected_logs( array(
			'parent' => $object_id,
			'type'   => $type,
			'paged'  => $paged,
		) );
	}

	/**
	 * Stores a log entry
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $log_data Log entry data.
	 * @param  array $log_meta Log entry meta.
	 *
	 * @return int             The ID of the newly created log item.
	 */
	public function insert_log( $log_data = array(), $log_meta = array() ) {
		// @todo: add backward compatibility for old table.

		/**
		 * Fires before inserting log entry.
		 *
		 * @since 1.0
		 *
		 * @param array $log_data Log entry data.
		 * @param array $log_meta Log entry meta.
		 */
		do_action( 'give_pre_insert_log', $log_data, $log_meta );

		// Store the log entry
		$log_id = $this->log_db->insert( $log_data );

		// Set log meta, if any
		if ( $log_id && ! empty( $log_meta ) ) {
			foreach ( (array) $log_meta as $key => $meta ) {
				$this->logmeta_db->update_meta( $log_id, sanitize_key( $key ), $meta );
			}
		}

		/**
		 * Fires after inserting log entry.
		 *
		 * @since 1.0
		 *
		 * @param int   $log_id   Log entry id.
		 * @param array $log_data Log entry data.
		 * @param array $log_meta Log entry meta.
		 */
		do_action( 'give_post_insert_log', $log_id, $log_data, $log_meta );

		return $log_id;
	}

	/**
	 * Update and existing log item
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $log_data Log entry data.
	 * @param  array $log_meta Log entry meta.
	 *
	 * @return bool|null       True if successful, false otherwise.
	 */
	public function update_log( $log_data = array(), $log_meta = array() ) {

		/**
		 * Fires before updating log entry.
		 *
		 * @since 1.0
		 *
		 * @param array $log_data Log entry data.
		 * @param array $log_meta Log entry meta.
		 */
		do_action( 'give_pre_update_log', $log_data, $log_meta );

		$defaults = array(
			'post_type'   => 'give_log',
			'post_status' => 'publish',
			'post_parent' => 0,
		);

		$args = wp_parse_args( $log_data, $defaults );

		// Store the log entry
		$log_id = wp_update_post( $args );

		if ( $log_id && ! empty( $log_meta ) ) {
			foreach ( (array) $log_meta as $key => $meta ) {
				if ( ! empty( $meta ) ) {
					give_update_meta( $log_id, '_give_log_' . sanitize_key( $key ), $meta );
				}
			}
		}

		/**
		 * Fires after updating log entry.
		 *
		 * @since 1.0
		 *
		 * @param int   $log_id   Log entry id.
		 * @param array $log_data Log entry data.
		 * @param array $log_meta Log entry meta.
		 */
		do_action( 'give_post_update_log', $log_id, $log_data, $log_meta );
	}

	/**
	 * Retrieve all connected logs
	 *
	 * Used for retrieving logs related to particular items, such as a specific donation.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args Query arguments.
	 *
	 * @return array|false Array if logs were found, false otherwise.
	 */
	public function get_connected_logs( $args = array() ) {
		// @todo: add backward compatibility for old table.
		$defaults = array(
			'number' => 20,
			'paged'  => get_query_var( 'paged' ),
			'type'   => false,
			'date'   => null,
		);

		$query_args = wp_parse_args( $args, $defaults );

		// Retrieve logs based on specific timeframe
		if ( ! empty ( $query_args['date'] ) && is_array( $query_args['date'] ) ) {
			if ( ! empty( $query_args['date']['start'] ) ) {
				$query_args['date']['after'] = array(
					'year'  => date( 'Y', strtotime( $query_args['date']['start'] ) ),
					'month' => date( 'm', strtotime( $query_args['date']['start'] ) ),
					'day'   => date( 'd', strtotime( $query_args['date']['start'] ) ),
				);
			}

			if ( ! empty( $query_args['date']['end'] ) ) {
				$query_args['date']['before'] = array(
					'year'  => date( 'Y', strtotime( $query_args['date']['end'] ) ),
					'month' => date( 'm', strtotime( $query_args['date']['end'] ) ),
					'day'   => date( 'd', strtotime( $query_args['date']['end'] ) ),
				);
			}
		}

		$logs = $this->log_db->get_logs( $query_args );

		if ( $logs ) {
			return $logs;
		}

		// No logs found
		return false;
	}

	/**
	 * Retrieve Log Count
	 *
	 * Retrieves number of log entries connected to particular object ID.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int    $object_id  Log object ID. Default is 0.
	 * @param  string $type       Log type. Default is null.
	 * @param  array  $meta_query Log meta query. Default is null.
	 * @param  array  $date_query Log data query. Default is null.
	 *
	 * @return int                Log count.
	 */
	public function get_log_count( $object_id = 0, $type = null, $meta_query = null, $date_query = null ) {
		$log_query = array(
			'log_type'   => $type,
			'meta_query' => $meta_query,
			'date_query' => $date_query,
		);

		if ( $object_id ) {
			$log_query['meta_query'] = array(
				array(
					'key'   => '_give_log_form_id',
					'value' => $object_id,
				),
			);
		}

		return $this->log_db->count( $log_query );
	}

	/**
	 * Delete Logs
	 *
	 * Remove log entries connected to particular object ID.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int    $object_id  Log object ID. Default is 0.
	 * @param  string $type       Log type. Default is null.
	 * @param  array  $meta_query Log meta query. Default is null.
	 *
	 * @return void
	 */
	public function delete_logs( $object_id = 0, $type = null, $meta_query = null ) {
		$query_args = array(
			'post_parent'    => $object_id,
			'post_type'      => 'give_log',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		);

		if ( ! empty( $type ) && $this->valid_type( $type ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'give_log_type',
					'field'    => 'slug',
					'terms'    => $type,
				),
			);
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$logs = get_posts( $query_args );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				wp_delete_post( $log, true );
			}
		}
	}

	/**
	 * Setup cron to delete log cache in background.
	 *
	 * @since  1.7
	 * @access public
	 *
	 * @param int $post_id
	 */
	public function background_process_delete_cache( $post_id ) {
		// Delete log cache immediately
		wp_schedule_single_event( time(), 5, 'give_delete_log_cache' );
	}

	/**
	 * Delete all logging cache when form, log or payment updates
	 *
	 * @since  1.7
	 * @access public
	 *
	 * @return bool
	 */
	public function delete_cache() {
		global $wpdb;

		// Add log related keys to delete.
		$cache_option_names = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT *
 						FROM {$wpdb->options}
 						where option_name LIKE '%%%s%%'
 						OR option_name LIKE '%%%s%%'",
				'give_cache_get_logs',
				'give_cache_get_log_count'
			),
			1 // option_name
		);

		// Bailout.
		if ( empty( $cache_option_names ) ) {
			return false;
		}

		Give_Cache::delete( $cache_option_names );
	}
}
