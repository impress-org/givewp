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
		$this->validate_params( $log_data, $log_meta );

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
				$this->logmeta_db->update_meta( $log_id, '_give_log_' . sanitize_key( $key ), $meta );
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
		$this->validate_params( $log_data, $log_meta );

		/**
		 * Fires before updating log entry.
		 *
		 * @since 1.0
		 *
		 * @param array $log_data Log entry data.
		 * @param array $log_meta Log entry meta.
		 */
		do_action( 'give_pre_update_log', $log_data, $log_meta );

		// Store the log entry
		$log_id = $this->log_db->add( $log_data );

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
		$defaults = array(
			'number' => 20,
			'paged'  => get_query_var( 'paged' ),
			'type'   => false,
			'date'   => null,
		);

		$query_args = wp_parse_args( $args, $defaults );

		$this->validate_params( $query_args );

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
		$log_query = array();

		if ( ! empty( $type ) ) {
			$log_query['log_type'] = $type;
		}

		if ( ! empty( $meta_query ) ) {
			$log_query['meta_query'] = $meta_query;
		}

		if ( ! empty( $date_query ) ) {
			$log_query['date_query'] = $date_query;
		}


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
			'log_parent' => $object_id,
			'number'     => - 1,
			'fields'     => 'ids',
		);

		if ( ! empty( $type ) && $this->valid_type( $type ) ) {
			$query_args['log_type'] = $type;
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$logs = $this->log_db->get_logs( $query_args );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$this->log_db->delete( $log->ID );
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

	/**
	 * Validate query params.
	 *
	 * @since  2.0
	 * @access private
	 *
	 * @param       $log_query
	 * @param array $log_meta
	 */
	private function validate_params( &$log_query, &$log_meta = array() ) {
		// Backward compatibility version 2.0
		if ( isset( $log_query['post_title'] ) ) {
			$log_query['log_title'] = $log_query['post_title'];
			unset( $log_query['post_title'] );
		}

		if ( isset( $log_query['post_content'] ) ) {
			$log_query['log_content'] = $log_query['post_content'];
			unset( $log_query['post_content'] );
		}

		if ( isset( $log_query['post_parent'] ) ) {
			$log_meta['form_id'] = $log_query['post_parent'];
			unset( $log_query['post_parent'] );
		}

		if ( isset( $log_query['post_type'] ) ) {
			$log_query['log_type'] = $log_query['post_type'];
			unset( $log_query['post_type'] );
		}
	}
}
