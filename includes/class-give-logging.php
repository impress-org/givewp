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
	 * Class Constructor
	 *
	 * Set up the Give Logging Class.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function __construct() {
	}


	/**
	 * Setup hooks
	 *
	 * @since  1.7
	 * @access public
	 */
	public function __setup_hooks() {
		// Create the log post type
		add_action( 'init', array( $this, 'register_post_type' ), 1 );

		// Create types taxonomy and default types
		add_action( 'init', array( $this, 'register_taxonomy' ), 1 );

		add_action( 'save_post_give_payment', array( $this, 'background_process_delete_cache' ) );
		add_action( 'save_post_give_forms', array( $this, 'background_process_delete_cache' ) );
		add_action( 'save_post_give_log', array( $this, 'background_process_delete_cache' ) );
		add_action( 'give_delete_log_cache', array( $this, 'delete_cache' ) );
	}

	/**
	 * Log Post Type
	 *
	 * Registers the 'give_log' Post Type.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function register_post_type() {
		/* Logs post type */
		$log_args = array(
			'labels'              => array(
				'name' => esc_html__( 'Logs', 'give' ),
			),
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'supports'            => array( 'title', 'editor' ),
			'can_export'          => true,
		);

		register_post_type( 'give_log', $log_args );
	}

	/**
	 * Log Type Taxonomy
	 *
	 * Registers the "Log Type" taxonomy.  Used to determine the type of log entry.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function register_taxonomy() {
		register_taxonomy( 'give_log_type', 'give_log', array(
			'public' => false,
		) );
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
			'post_title'   => $title,
			'post_content' => $message,
			'post_parent'  => $parent,
			'log_type'     => $type,
		);

		return $this->insert_log( $log_data );
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
			'post_parent' => $object_id,
			'paged'       => $paged,
			'log_type'    => $type,
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
		$defaults = array(
			'post_type'    => 'give_log',
			'post_status'  => 'publish',
			'post_parent'  => 0,
			'post_content' => '',
			'log_type'     => false,
		);

		$args = wp_parse_args( $log_data, $defaults );

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
		$log_id = wp_insert_post( $args );

		// Set the log type, if any
		if ( $log_data['log_type'] && $this->valid_type( $log_data['log_type'] ) ) {
			wp_set_object_terms( $log_id, $log_data['log_type'], 'give_log_type', false );
		}

		// Set log meta, if any
		if ( $log_id && ! empty( $log_meta ) ) {
			foreach ( (array) $log_meta as $key => $meta ) {
				update_post_meta( $log_id, '_give_log_' . sanitize_key( $key ), $meta );
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
					update_post_meta( $log_id, '_give_log_' . sanitize_key( $key ), $meta );
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
			'post_type'      => 'give_log',
			'posts_per_page' => 20,
			'post_status'    => 'publish',
			'paged'          => get_query_var( 'paged' ),
			'log_type'       => false,
		);

		$query_args = wp_parse_args( $args, $defaults );

		if ( $query_args['log_type'] && $this->valid_type( $query_args['log_type'] ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'give_log_type',
					'field'    => 'slug',
					'terms'    => $query_args['log_type'],
				),
			);
		}

		$logs = get_posts( $query_args );

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

		$query_args = array(
			'post_type'      => 'give_log',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		);

		if ( $object_id ) {
			$query_args['post_parent'] = $object_id;
		}

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

		if ( ! empty( $date_query ) ) {
			$query_args['date_query'] = $date_query;
		}

		// Get cache key for current query.
		$cache_key = give_get_cache_key( 'get_log_count', $query_args );

		// check if cache already exist or not.
		if ( ! ( $logs_count = get_option( $cache_key ) ) ) {
			$logs       = new WP_Query( $query_args );
			$logs_count = (int) $logs->post_count;

			// Cache results.
			add_option( $cache_key, $logs_count, '', 'no' );
		}

		return $logs_count;
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
		wp_schedule_single_event( time(), 'give_delete_log_cache' );
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
		$cache_option_names = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} where option_name LIKE '%%%s%%'",
				'give_cache'
			),
			ARRAY_A
		);

		// Bailout.
		if ( empty( $cache_option_names ) ) {
			return false;
		}

		// Delete log cache.
		foreach ( $cache_option_names as $option_name ) {
			delete_option( $option_name['option_name'] );
		}
	}
}

// Initiate the logging system
$GLOBALS['give_logs'] = new Give_Logging();
$GLOBALS['give_logs']->__setup_hooks();

/**
 * Record a log entry
 *
 * A wrapper function for the Give_Logging class add() method.
 *
 * @since  1.0
 *
 * @param  string $title   Log title. Default is empty.
 * @param  string $message Log message. Default is empty.
 * @param  int    $parent  Parent log. Default is 0.
 * @param  string $type    Log type. Default is null.
 *
 * @return int             ID of the new log entry.
 */
function give_record_log( $title = '', $message = '', $parent = 0, $type = null ) {
	/* @var Give_Logging $give_logs */
	global $give_logs;
	$log = $give_logs->add( $title, $message, $parent, $type );

	return $log;
}
