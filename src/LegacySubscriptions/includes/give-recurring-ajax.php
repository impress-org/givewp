<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Recurring_AJAX
 */
class Give_Recurring_AJAX {

	/**
	 * Hook in ajax handlers
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'do_give_recurring_ajax' ), 0 );
		self::add_ajax_events();
	}

	/**
	 * Get Give Recurring Ajax Endpoint.
	 *
	 * @param  string $request Optional.
	 * @param  string $url     Optional.
	 *
	 * @return string
	 */
	public static function get_endpoint( $request = '', $url = '' ) {
		return esc_url_raw( add_query_arg( 'give-recurring-ajax', $request, $url ) );
	}

	/**
	 * Set Give Recurring AJAX constant and headers.
	 */
	public static function define_ajax() {
		if ( ! empty( $_GET['give-recurring-ajax'] ) ) {
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			if ( ! defined( 'GIVE_RECURRING_DOING_AJAX' ) ) {
				define( 'GIVE_RECURRING_DOING_AJAX', true );
			}
			// Turn off display_errors during AJAX events to prevent malformed JSON.
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 );
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}

	/**
	 * Send headers for Give Recurring Ajax Requests
	 */
	private static function give_recurring_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	/**
	 * Check for Give Recurring Ajax request and fire action.
	 */
	public static function do_give_recurring_ajax() {
		global $wp_query;

		if ( ! empty( $_GET['give-recurring-ajax'] ) ) {
			$wp_query->set( 'give-recurring-ajax', sanitize_text_field( $_GET['give-recurring-ajax'] ) );
		}

		if ( $action = $wp_query->get( 'give-recurring-ajax' ) ) {
			self::give_recurring_ajax_headers();
			do_action( 'give_recurring_ajax_' . sanitize_text_field( $action ) );
			die();
		}
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		$ajax_events = array(
			'sync_subscription_details'      => false,
			'sync_subscription_transactions' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_give_recurring_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_give_recurring_' . $ajax_event, array( __CLASS__, $ajax_event ) );

				// Give Recurring AJAX can be used for frontend ajax requests.
				add_action( 'give_recurring_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Sync subscription details.
	 */
	public static function sync_subscription_details() {

		check_ajax_referer( 'sync-subscription-details', 'security' );

        $user_can_sync = current_user_can( 'update_plugins' );
        if ( !apply_filters('give_recurring_user_can_sync_transactions', $user_can_sync) ) {
            die( -1 );
        }

		$subscription_id = absint( $_POST['subscription_id'] );
		$log_id          = isset( $_POST['log_id'] ) ? absint( $_POST['log_id'] ) : 0;

		if ( $log_id ) {
			Give_Recurring()->synchronizer->log_id = $log_id;
		}

		$output = Give_Recurring()->synchronizer->sync_subscription_details( $subscription_id );

		wp_send_json( $output );
	}

	/**
	 * Sync subscription transactions.
	 */
	public static function sync_subscription_transactions() {

		check_ajax_referer( 'sync-subscription-transactions', 'security' );

        $user_can_sync = current_user_can( 'update_plugins' );
        if ( !apply_filters('give_recurring_user_can_sync_transactions', $user_can_sync) ) {
            die( -1 );
        }

		$subscription_id = absint( $_POST['subscription_id'] );
		$log_id          = isset( $_POST['log_id'] ) ? absint( $_POST['log_id'] ) : 0;

		if ( $log_id ) {
			Give_Recurring()->synchronizer->log_id = $log_id;
		}

		$output = Give_Recurring()->synchronizer->sync_subscription_transactions( $subscription_id );
		wp_send_json( $output );
	}
}

Give_Recurring_AJAX::init();
