<?php
/**
 * Upgrade Functions
 *
 * @package     Give
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 *
 * NOTICE: When adding new upgrade notices, please be sure to put the action into the upgrades array during install: /includes/install.php @ Appox Line 156
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display Upgrade Notices
 *
 * @since 1.0
 * @return void
 */
function give_show_upgrade_notices() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'give-upgrades' ) {
		return;
	} // Don't show notices on the upgrades page

	$give_version = get_option( 'give_version' );

	if ( ! $give_version ) {
		// 1.0 is the first version to use this option so we must add it
		$give_version = '1.0';
	}

	$give_version = preg_replace( '/[^0-9.].*/', '', $give_version );

	//	if ( Give()->session->get( 'upgrade_sequential' ) && give_get_payments() ) {
	//		printf(
	//			'<div class="updated"><p>' . __( 'Give needs to upgrade past order numbers to make them sequential, click <a href="%s">here</a> to start the upgrade.', 'give' ) . '</p></div>',
	//			admin_url( 'index.php?page=give-upgrades&give-upgrade=upgrade_sequential_payment_numbers' )
	//		);
	//	}

	if ( version_compare( $give_version, '1.0', '<' ) ) {
		printf(
			'<div class="updated"><p>' . __( 'Give needs to upgrade the transaction logs database, click <a href="%s">here</a> to start the upgrade.', 'give' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=give-upgrades&give-upgrade=upgrade_payments_logs_db' ) )
		);
	}

	if ( version_compare( $give_version, '1.0', '<' ) || ! give_has_upgrade_completed( 'upgrade_donor_payments_association' ) ) {
		printf(
			'<div class="updated"><p>' . __( 'Give needs to upgrade the donor database, click <a href="%s">here</a> to start the upgrade.', 'give' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=give-upgrades&give-upgrade=upgrade_donor_payments_association' ) )
		);
	}

	/*
	 *  NOTICE:
	 *
	 *  When adding new upgrade notices, please be sure to put the action into the upgrades array during install:
	 *  /includes/install.php @ Appox Line 156
	 *
	 */

	// End 'Stepped' upgrade process notices


}

add_action( 'admin_notices', 'give_show_upgrade_notices' );

/**
 * Triggers all upgrade functions
 *
 * This function is usually triggered via AJAX
 *
 * @since 1.0
 * @return void
 */
function give_trigger_upgrades() {

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_die( __( 'You do not have permission to do Give upgrades', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	$give_version = get_option( 'give_version' );

	if ( ! $give_version ) {
		// 1.0 is the first version to use this option so we must add it
		$give_version = '1.0';
		add_option( 'give_version', $give_version );
	}

	update_option( 'give_version', GIVE_VERSION );

	if ( DOING_AJAX ) {
		die( 'complete' );
	} // Let AJAX know that the upgrade is complete
}

add_action( 'wp_ajax_give_trigger_upgrades', 'give_trigger_upgrades' );

/**
 * Check if the upgrade routine has been run for a specific action
 *
 * @since  1.0
 *
 * @param  string $upgrade_action The upgrade action to check completion for
 *
 * @return bool                   If the action has been added to the completed actions array
 */
function give_has_upgrade_completed( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades = give_get_completed_upgrades();

	return in_array( $upgrade_action, $completed_upgrades );

}

/**
 * Adds an upgrade action to the completed upgrades array
 *
 * @since  1.0
 *
 * @param  string $upgrade_action The action to add to the copmleted upgrades array
 *
 * @return bool                   If the function was successfully added
 */
function give_set_upgrade_complete( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades   = give_get_completed_upgrades();
	$completed_upgrades[] = $upgrade_action;

	// Remove any blanks, and only show uniques
	$completed_upgrades = array_unique( array_values( $completed_upgrades ) );

	return update_option( 'give_completed_upgrades', $completed_upgrades );
}

/**
 * Get's the array of completed upgrade actions
 *
 * @since  1.0
 * @return array The array of completed upgrades
 */
function give_get_completed_upgrades() {

	$completed_upgrades = get_option( 'give_completed_upgrades' );

	if ( false === $completed_upgrades ) {
		$completed_upgrades = array();
	}

	return $completed_upgrades;

}


/**
 * Fixes the give_log meta
 *
 * @since 1.0
 * @return void
 */
function give_v1_upgrade_payments_logs_db() {

	global $wpdb;

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_die( __( 'You do not have permission to do Give upgrades', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
	}
	ignore_user_abort( true );

	if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}
	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 25;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;
	if ( 1 === $step ) {
		// Check if we have any variable price products on the first step
		$sql          = "SELECT ID FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta m ON p.ID = m.post_id WHERE m.meta_key = '_variable_pricing' AND m.meta_value = 1 LIMIT 1";
		$has_variable = $wpdb->get_col( $sql );
		if ( empty( $has_variable ) ) {
			// We had no variable priced forms, so go ahead and just complete
			update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
			delete_option( 'give_doing_upgrade' );
			wp_redirect( admin_url( 'index.php?page=give-about' ) );
			exit;
		}
	}
	$payment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'give_payment' ORDER BY post_date DESC LIMIT %d,%d;", $offset, $number ) );
	if ( ! empty( $payment_ids ) ) {
		foreach ( $payment_ids as $payment_id ) {
			$payment_meta = give_get_payment_meta( $payment_id );
			$variable_donation = array();
			// Don't care if the form is a single price id
			if ( ! isset( $payment_meta['price_id'] ) || empty( $payment_meta['price_id'] ) ) {
				continue;
			}
			$variable_donation[] = array(
				'id'       => $payment_meta['form_id'],
				'price_id' => $payment_meta['price_id']
			);

			$variable_donation_ids = array_unique( wp_list_pluck( $variable_donation, 'id' ) );
			$unique_donation_ids   = implode( ',', $variable_donation_ids );
			if ( empty( $unique_donation_ids ) ) {
				continue; // If there were no downloads, just fees, move along
			}
			// Get all Log Ids where the post parent is in the set of download IDs we found in the cart meta
			$logs        = $wpdb->get_results( "SELECT m.post_id AS log_id, p.post_parent AS give_form_id FROM $wpdb->postmeta m LEFT JOIN $wpdb->posts p ON m.post_id = p.ID WHERE meta_key = '_give_log_payment_id' AND meta_value = $payment_id AND p.post_parent IN ($unique_donation_ids)", ARRAY_A );
			$mapped_logs = array();

			// Go through each cart item
			foreach ( $variable_donation as $cart_item ) {
				// Itterate through the logs we found attached to this payment
				foreach ( $logs as $key => $log ) {
					// If this Log ID is associated with this donation ID give it the price_id
					if ( (int) $log['give_form_id'] === (int) $cart_item['id'] ) {
						$mapped_logs[ $log['log_id'] ] = $cart_item['price_id'];
						// Remove this Donation/Log ID from the list, for multipurchase compatibility
						unset( $logs[ $key ] );
						// These aren't the logs we're looking for. Move Along, Move Along.
						break;
					}
				}
			}
			if ( ! empty( $mapped_logs ) ) {
				$update = "UPDATE $wpdb->postmeta SET meta_value = ";
				$case   = "CASE post_id ";
				foreach ( $mapped_logs as $post_id => $value ) {
					$case .= "WHEN $post_id THEN $value ";
				}
				$case .= "END ";
				$log_ids = implode( ',', array_keys( $mapped_logs ) );
				$where   = "WHERE post_id IN ($log_ids) AND meta_key = '_give_log_price_id'";
				$sql     = $update . $case . $where;
				// Execute our query to update this payment
				$wpdb->query( $sql );
			}
		}
		// More Payments found so upgrade them
		$step ++;
		$redirect = add_query_arg( array(
			'page'         => 'give-upgrades',
			'give-upgrade' => 'upgrade_payments_logs_db',
			'step'         => $step
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect );
		exit;
	} else {
		// No more payments found, finish up
		update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
		delete_option( 'give_doing_upgrade' );
		wp_redirect( admin_url( 'index.php?page=give-about' ) );
		exit;
	}
}

add_action( 'give_upgrade_payments_logs_db', 'give_v1_upgrade_payments_logs_db' );

/**
 * Run the upgrade for the customers to find all payment attachments
 *
 * @since  1.0
 * @return void
 */
function give_v1_upgrade_customer_purchases() {
	global $wpdb;

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_die( __( 'You do not have permission to do Give upgrades', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 50;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	if ( $step < 2 ) {
		// Check if we have any payments before moving on
		$sql          = "SELECT ID FROM $wpdb->posts WHERE post_type = 'give_payment' LIMIT 1";
		$has_payments = $wpdb->get_col( $sql );

		if ( empty( $has_payments ) ) {
			// We had no payments, just complete
			update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
			give_set_upgrade_complete( 'upgrade_donor_payments_association' );
			delete_option( 'give_doing_upgrade' );
			wp_redirect( admin_url( 'index.php?page=give-about' ) );
			exit;
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$total = Give()->customers->count();
	}

	$customers = Give()->customers->get_customers( array( 'number' => $number, 'offset' => $offset ) );

	if ( ! empty( $customers ) ) {

		foreach ( $customers as $customer ) {

			// Get payments by email and user ID
			$select = "SELECT ID FROM $wpdb->posts p ";
			$join   = "LEFT JOIN $wpdb->postmeta m ON p.ID = m.post_id ";
			$where  = "WHERE p.post_type = 'give_payment' ";

			if ( ! empty( $customer->user_id ) && intval( $customer->user_id ) > 0 ) {
				$where .= "AND ( ( m.meta_key = '_give_payment_user_email' AND m.meta_value = '$customer->email' ) OR ( m.meta_key = '_give_payment_customer_id' AND m.meta_value = '$customer->id' ) OR ( m.meta_key = '_give_payment_user_id' AND m.meta_value = '$customer->user_id' ) )";
			} else {
				$where .= "AND ( ( m.meta_key = '_give_payment_user_email' AND m.meta_value = '$customer->email' ) OR ( m.meta_key = '_give_payment_customer_id' AND m.meta_value = '$customer->id' ) ) ";
			}

			$sql            = $select . $join . $where;
			$found_payments = $wpdb->get_col( $sql );

			$unique_payment_ids = array_unique( array_filter( $found_payments ) );

			if ( ! empty( $unique_payment_ids ) ) {

				$unique_ids_string = implode( ',', $unique_payment_ids );

				$customer_data = array( 'payment_ids' => $unique_ids_string );

				$purchase_value_sql = "SELECT SUM( m.meta_value ) FROM $wpdb->postmeta m LEFT JOIN $wpdb->posts p ON m.post_id = p.ID WHERE m.post_id IN ( $unique_ids_string ) AND p.post_status IN ( 'publish', 'revoked' ) AND m.meta_key = '_give_payment_total'";
				$purchase_value     = $wpdb->get_col( $purchase_value_sql );

				$purchase_count_sql = "SELECT COUNT( m.post_id ) FROM $wpdb->postmeta m LEFT JOIN $wpdb->posts p ON m.post_id = p.ID WHERE m.post_id IN ( $unique_ids_string ) AND p.post_status IN ( 'publish', 'revoked' ) AND m.meta_key = '_give_payment_total'";
				$purchase_count     = $wpdb->get_col( $purchase_count_sql );

				if ( ! empty( $purchase_value ) && ! empty( $purchase_count ) ) {

					$purchase_value = $purchase_value[0];
					$purchase_count = $purchase_count[0];

					$customer_data['purchase_count'] = $purchase_count;
					$customer_data['purchase_value'] = $purchase_value;

				}

			} else {

				$customer_data['purchase_count'] = 0;
				$customer_data['purchase_value'] = 0;
				$customer_data['payment_ids']    = '';

			}


			if ( ! empty( $customer_data ) ) {

				$customer = new Give_Customer( $customer->id );
				$customer->update( $customer_data );

			}

		}

		// More Payments found so upgrade them
		$step ++;
		$redirect = add_query_arg( array(
			'page'         => 'give-upgrades',
			'give-upgrade' => 'upgrade_donor_payments_association',
			'step'         => $step,
			'number'       => $number,
			'total'        => $total
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect );
		exit;
	} else {

		// No more customers found, finish up

		update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
		give_set_upgrade_complete( 'upgrade_donor_payments_association' );
		delete_option( 'give_doing_upgrade' );

		wp_redirect( admin_url( 'index.php?page=give-about' ) );
		exit;
	}
}

add_action( 'give_upgrade_donor_payments_association', 'give_v1_upgrade_customer_purchases' );
