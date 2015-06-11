<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     Give
 * @subpackage  Functions/AJAX
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if AJAX works as expected
 *
 * @since 1.0
 * @return bool True if AJAX works, false otherwise
 */
function give_test_ajax_works() {

	// Check if the Airplane Mode plugin is installed
	if ( class_exists( 'Airplane_Mode_Core' ) ) {

		$airplane = Airplane_Mode_Core::getInstance();

		if ( method_exists( $airplane, 'enabled' ) ) {

			if ( $airplane->enabled() ) {
				return true;
			}

		} else {

			if ( $airplane->check_status() == 'on' ) {
				return true;
			}
		}
	}

	add_filter( 'block_local_requests', '__return_false' );

	if ( get_transient( '_give_ajax_works' ) ) {
		return true;
	}

	$params = array(
		'sslverify'  => false,
		'timeout'    => 30,
		'body'       => array(
			'action' => 'give_test_ajax'
		)
	);

	$ajax  = wp_remote_post( give_get_ajax_url(), $params );
	$works = true;

	if ( is_wp_error( $ajax ) ) {

		$works = false;

	} else {

		if( empty( $ajax['response'] ) ) {
			$works = false;
		}

		if( empty( $ajax['response']['code'] ) || 200 !== (int) $ajax['response']['code'] ) {
			$works = false;
		}

		if( empty( $ajax['response']['message'] ) || 'OK' !== $ajax['response']['message'] ) {
			$works = false;
		}

		if( ! isset( $ajax['body'] ) || 0 !== (int) $ajax['body'] ) {
			$works = false;
		}

	}

	if ( $works ) {
		set_transient( '_give_ajax_works', '1', DAY_IN_SECONDS );
	}

	return $works;
}


/**
 * Get AJAX URL
 *
 * @since 1.0
 * @return string
 */
function give_get_ajax_url() {
	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$current_url = give_get_current_page_url();
	$ajax_url    = admin_url( 'admin-ajax.php', $scheme );

	if ( preg_match( '/^https/', $current_url ) && ! preg_match( '/^https/', $ajax_url ) ) {
		$ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
	}

	return apply_filters( 'give_ajax_url', $ajax_url );
}

/**
 * Loads Checkout Login Fields the via AJAX
 *
 * @since 1.0
 * @return void
 */
function give_load_checkout_login_fields() {
	do_action( 'give_purchase_form_login_fields' );
	give_die();
}

add_action( 'wp_ajax_nopriv_give_checkout_login', 'give_load_checkout_login_fields' );

/**
 * Load Checkout Register Fields via AJAX
 *
 * @since 1.0
 * @return void
 */
function give_load_checkout_register_fields() {
	do_action( 'give_purchase_form_register_fields' );
	give_die();
}

add_action( 'wp_ajax_nopriv_checkout_register', 'give_load_checkout_register_fields' );

/**
 * Get Form Title via AJAX (used only in WordPress Admin)
 *
 * @since 1.0
 * @return void
 */
function give_ajax_get_form_title() {
	if ( isset( $_POST['form_id'] ) ) {
		$title = get_the_title( $_POST['form_id'] );
		if ( $title ) {
			echo $title;
		} else {
			echo 'fail';
		}
	}
	give_die();
}

add_action( 'wp_ajax_give_get_form_title', 'give_ajax_get_form_title' );
add_action( 'wp_ajax_nopriv_give_get_form_title', 'give_ajax_get_form_title' );

/**
 * Retrieve a states drop down
 *
 * @since 1.0
 * @return void
 */
function give_ajax_get_states_field() {

	if ( empty( $_POST['country'] ) ) {
		$_POST['country'] = give_get_country();
	}
	$states = give_get_states( $_POST['country'] );

	if ( ! empty( $states ) ) {

		$args = array(
			'name'             => $_POST['field_name'],
			'id'               => $_POST['field_name'],
			'class'            => $_POST['field_name'] . '  give-select',
			'options'          => give_get_states( $_POST['country'] ),
			'show_option_all'  => false,
			'show_option_none' => false
		);

		$response = Give()->html->select( $args );

	} else {

		$response = 'nostates';
	}

	echo $response;

	give_die();
}

add_action( 'wp_ajax_give_get_states', 'give_ajax_get_states_field' );
add_action( 'wp_ajax_nopriv_give_get_states', 'give_ajax_get_states_field' );

/**
 * Retrieve a states drop down
 *
 * @since 1.0
 * @return void
 */
function give_ajax_form_search() {
	global $wpdb;

	$search  = esc_sql( sanitize_text_field( $_GET['s'] ) );
	$results = array();
	if ( current_user_can( 'edit_give_forms' ) ) {
		$items = $wpdb->get_results( "SELECT ID,post_title FROM $wpdb->posts WHERE `post_type` = 'give_forms' AND `post_title` LIKE '%$search%' LIMIT 50" );
	} else {
		$items = $wpdb->get_results( "SELECT ID,post_title FROM $wpdb->posts WHERE `post_type` = 'give_forms' AND `post_status` = 'publish' AND `post_title` LIKE '%$search%' LIMIT 50" );
	}

	if ( $items ) {

		foreach ( $items as $item ) {

			$results[] = array(
				'id'   => $item->ID,
				'name' => $item->post_title
			);
		}

	} else {

		$items[] = array(
			'id'   => 0,
			'name' => __( 'No results found', 'give' )
		);

	}

	echo json_encode( $results );

	give_die();
}

add_action( 'wp_ajax_give_form_search', 'give_ajax_form_search' );
add_action( 'wp_ajax_nopriv_give_form_search', 'give_ajax_form_search' );

/**
 * Search the donors database via Ajax
 *
 * @since 1.0
 * @return void
 */
function give_ajax_donor_search() {
	global $wpdb;

	$search  = esc_sql( sanitize_text_field( $_GET['s'] ) );
	$results = array();
	if ( ! current_user_can( 'view_give_reports' ) ) {
		$donors = array();
	} else {
		$donors = $wpdb->get_results( "SELECT id,name,email FROM {$wpdb->prefix}give_donors WHERE `name` LIKE '%$search%' OR `email` LIKE '%$search%' LIMIT 50" );
	}

	if ( $donors ) {

		foreach ( $donors as $donor ) {

			$results[] = array(
				'id'   => $donor->id,
				'name' => $donor->name . '(' . $donor->email . ')'
			);
		}

	} else {

		$donors[] = array(
			'id'   => 0,
			'name' => __( 'No results found', 'give' )
		);

	}

	echo json_encode( $results );

	give_die();
}

add_action( 'wp_ajax_give_donor_search', 'give_ajax_donor_search' );


/**
 * Searches for users via ajax and returns a list of results
 *
 * @since 1.0
 * @return void
 */
function give_ajax_search_users() {

	if ( current_user_can( 'manage_give_settings' ) ) {

		$search_query = trim( $_POST['user_name'] );

		$found_users = get_users( array(
				'number' => 9999,
				'search' => $search_query . '*'
			)
		);

		$user_list = '<ul>';
		if ( $found_users ) {
			foreach ( $found_users as $user ) {
				$user_list .= '<li><a href="#" data-login="' . esc_attr( $user->user_login ) . '">' . esc_html( $user->user_login ) . '</a></li>';
			}
		} else {
			$user_list .= '<li>' . __( 'No users found', 'give' ) . '</li>';
		}
		$user_list .= '</ul>';

		echo json_encode( array( 'results' => $user_list ) );

	}
	die();
}

add_action( 'wp_ajax_give_search_users', 'give_ajax_search_users' );