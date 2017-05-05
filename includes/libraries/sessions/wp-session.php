<?php
/**
 * WordPress session managment.
 *
 * Standardizes WordPress session data and uses either database transients or in-memory caching
 * for storing user session information.
 *
 * @package WordPress
 * @subpackage Session
 * @since   3.7.0
 */

/**
 * Return the current cache expire setting.
 *
 * @return int
 */
function wp_session_cache_expire() {
	$wp_session = WP_Session::get_instance();

	return $wp_session->cache_expiration();
}

/**
 * Alias of wp_session_write_close()
 */
function wp_session_commit() {
	wp_session_write_close();
}

/**
 * Load a JSON-encoded string into the current session.
 *
 * @param string $data
 */
function wp_session_decode( $data ) {
	$wp_session = WP_Session::get_instance();

	return $wp_session->json_in( $data );
}

/**
 * Encode the current session's data as a JSON string.
 *
 * @return string
 */
function wp_session_encode() {
	$wp_session = WP_Session::get_instance();

	return $wp_session->json_out();
}

/**
 * Regenerate the session ID.
 *
 * @param bool $delete_old_session
 *
 * @return bool
 */
function wp_session_regenerate_id( $delete_old_session = false ) {
	$wp_session = WP_Session::get_instance();

	$wp_session->regenerate_id( $delete_old_session );

	return true;
}

/**
 * Start new or resume existing session.
 *
 * Resumes an existing session based on a value sent by the _wp_session cookie.
 *
 * @return bool
 */
function wp_session_start() {
	$wp_session = WP_Session::get_instance();
	do_action( 'wp_session_start' );

	return $wp_session->session_started();
}
if ( ! defined( 'WP_CLI' ) || false === WP_CLI ) {
	add_action( 'plugins_loaded', 'wp_session_start' );
}

/**
 * Return the current session status.
 *
 * @return int
 */
function wp_session_status() {
	$wp_session = WP_Session::get_instance();

	if ( $wp_session->session_started() ) {
		return PHP_SESSION_ACTIVE;
	}

	return PHP_SESSION_NONE;
}

/**
 * Unset all session variables.
 */
function wp_session_unset() {
	$wp_session = WP_Session::get_instance();

	$wp_session->reset();
}

/**
 * Write session data and end session
 */
function wp_session_write_close() {
	$wp_session = WP_Session::get_instance();

	$wp_session->write_data();
	do_action( 'wp_session_commit' );
}
if ( ! defined( 'WP_CLI' ) || false === WP_CLI ) {
	add_action( 'shutdown', 'wp_session_write_close' );
}

/**
 * Clean up expired sessions by removing data and their expiration entries from
 * the WordPress options table.
 *
 * This method should never be called directly and should instead be triggered as part
 * of a scheduled task or cron job.
 */
function wp_session_cleanup() {
	if ( defined( 'WP_SETUP_CONFIG' ) ) {
		return;
	}

	if ( ! defined( 'WP_INSTALLING' ) ) {
		/**
		 * Determine the size of each batch for deletion.
		 *
		 * @param int
		 */
		$batch_size = apply_filters( 'wp_session_delete_batch_size', 1000 );

		// Delete a batch of old sessions
		WP_Session_Utils::delete_old_sessions( $batch_size );
	}

	// Allow other plugins to hook in to the garbage collection process.
	do_action( 'wp_session_cleanup' );
}
add_action( 'wp_session_garbage_collection', 'wp_session_cleanup' );

/**
 * Register the garbage collector as a twice daily event.
 */
function wp_session_register_garbage_collection() {
	if ( ! wp_next_scheduled( 'wp_session_garbage_collection' ) ) {
		wp_schedule_event( time(), 'hourly', 'wp_session_garbage_collection' );
	}
}
add_action( 'wp', 'wp_session_register_garbage_collection' );
