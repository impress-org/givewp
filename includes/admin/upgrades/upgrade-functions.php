<?php
/**
 * Upgrade Functions
 *
 * @package     Give
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 *
 * NOTICE: When adding new upgrade notices, please be sure to put the action into the upgrades array during install: /includes/install.php @ Appox Line 156
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Perform automatic database upgrades when necessary.
 *
 * @since 1.6
 * @return void
 */
function give_do_automatic_upgrades() {
	$did_upgrade  = false;
	$give_version = preg_replace( '/[^0-9.].*/', '', get_option( 'give_version' ) );

	if ( ! $give_version ) {
		// 1.0 is the first version to use this option so we must add it.
		$give_version = '1.0';
	}

	switch ( true ) {

		case version_compare( $give_version, '1.6', '<' ) :
			give_v16_upgrades();
			$did_upgrade = true;
			break;

		case version_compare( $give_version, '1.7', '<' ) :
			give_v17_upgrades();
			$did_upgrade = true;
			break;

		case version_compare( $give_version, '1.8', '<' ) :
			give_v18_upgrades();
			$did_upgrade = true;
	}

	if ( $did_upgrade ) {
		update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
	}
}

add_action( 'admin_init', 'give_do_automatic_upgrades' );
add_action( 'give_upgrades', 'give_do_automatic_upgrades' );

/**
 * Display Upgrade Notices
 *
 * @since 1.0
 * @return void
 */
function give_show_upgrade_notices() {
	// Don't show notices on the upgrades page.
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'give-upgrades' ) {
		return;
	}

	$give_version = get_option( 'give_version' );

	if ( ! $give_version ) {
		// 1.0 is the first version to use this option so we must add it.
		$give_version = '1.0';
	}

	$give_version = preg_replace( '/[^0-9.].*/', '', $give_version );

	/*
	 *  NOTICE:
	 *
	 *  When adding new upgrade notices, please be sure to put the action into the upgrades array during install:
	 *  /includes/install.php @ Appox Line 156
	 *
	 */

	// v1.3.2 Upgrades
	if ( version_compare( $give_version, '1.3.2', '<' ) || ! give_has_upgrade_completed( 'upgrade_give_payment_customer_id' ) ) {
		printf(
			/* translators: %s: upgrade URL */
			'<div class="updated"><p>' . __( 'Give needs to upgrade the donor database, click <a href="%s">here</a> to start the upgrade.', 'give' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=give-upgrades&give-upgrade=upgrade_give_payment_customer_id' ) )
		);
	}

	// v1.3.4 Upgrades //ensure the user has gone through 1.3.4.
	if ( version_compare( $give_version, '1.3.4', '<' ) || ( ! give_has_upgrade_completed( 'upgrade_give_offline_status' ) && give_has_upgrade_completed( 'upgrade_give_payment_customer_id' ) ) ) {
		printf(
			/* translators: %s: upgrade URL */
			'<div class="updated"><p>' . __( 'Give needs to upgrade the donations database, click <a href="%s">here</a> to start the upgrade.', 'give' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=give-upgrades&give-upgrade=upgrade_give_offline_status' ) )
		);
	}

	// Check if we have a stalled upgrade.
	$resume_upgrade = give_maybe_resume_upgrade();
	if ( ! empty( $resume_upgrade ) ) {
		$resume_url = add_query_arg( $resume_upgrade, admin_url( 'index.php' ) );
		echo Give_Notices::notice_html(
			sprintf(
				__( 'Give needs to complete a database upgrade that was previously started, click <a href="%s">here</a> to resume the upgrade.', 'give' ),
				esc_url( $resume_url )
			)
		);

		return;
	}

	// v1.8 form metadata upgrades.
	if ( version_compare( $give_version, '1.8', '<' ) || ! give_has_upgrade_completed( 'v18_upgrades_form_metadata' ) ) {
		echo Give_Notices::notice_html(
			sprintf(
				esc_html__( 'Give needs to upgrade the form database, click %1$shere%2$s to start the upgrade.', 'give' ),
				'<a class="give-upgrade-link" href="' . esc_url( admin_url( 'index.php?page=give-upgrades&give-upgrade=give_v18_upgrades_form_metadata' ) ) . '">',
				'</a>'
			)
		);
	}

	// End 'Stepped' upgrade process notices.
	?>
	<script>
		jQuery(document).ready(function($){
			var $upgrade_links = $('.give-upgrade-link');
			if( $upgrade_links.length ) {
				$upgrade_links.on( 'click', function(e){
					e.preventDefault();

					if( ! window.confirm( '<?php _e( 'Please make sure to create a database backup before initiating the upgrade.', 'give' ); ?>' ) ) {
						return;
					}

					// Redirect to upgrdae link.
					window.location.assign( $(this).attr('href') );
				});
			}
		});
	</script>
	<?php
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
		wp_die( esc_html__( 'You do not have permission to do Give upgrades.', 'give' ), esc_html__( 'Error', 'give' ), array(
			'response' => 403,
		) );
	}

	$give_version = get_option( 'give_version' );

	if ( ! $give_version ) {
		// 1.0 is the first version to use this option so we must add it.
		$give_version = '1.0';
		add_option( 'give_version', $give_version );
	}

	update_option( 'give_version', GIVE_VERSION );
	delete_option( 'give_doing_upgrade' );

	if ( DOING_AJAX ) {
		die( 'complete' );
	} // End if().
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
 * For use when doing 'stepped' upgrade routines, to see if we need to start somewhere in the middle
 *
 * @since 1.8
 *
 * @return mixed   When nothing to resume returns false, otherwise starts the upgrade where it left off
 */
function give_maybe_resume_upgrade() {
	$doing_upgrade = get_option( 'give_doing_upgrade', false );
	if ( empty( $doing_upgrade ) ) {
		return false;
	}

	return $doing_upgrade;
}

/**
 * Adds an upgrade action to the completed upgrades array
 *
 * @since  1.0
 *
 * @param  string $upgrade_action The action to add to the completed upgrades array
 *
 * @return bool                   If the function was successfully added
 */
function give_set_upgrade_complete( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades   = give_get_completed_upgrades();
	$completed_upgrades[] = $upgrade_action;

	// Remove any blanks, and only show uniques.
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
 * Upgrades the
 *
 * Standardizes the discrepancies between two metakeys `_give_payment_customer_id` and `_give_payment_donor_id`
 *
 * @since      1.3.2
 */
function give_v132_upgrade_give_payment_customer_id() {
	global $wpdb;
	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_die( esc_html__( 'You do not have permission to do Give upgrades.', 'give' ), esc_html__( 'Error', 'give' ), array(
			'response' => 403,
		) );
	}

	ignore_user_abort( true );

	if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	// UPDATE DB METAKEYS.
	$sql   = "UPDATE $wpdb->postmeta SET meta_key = '_give_payment_customer_id' WHERE meta_key = '_give_payment_donor_id'";
	$query = $wpdb->query( $sql );

	update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
	give_set_upgrade_complete( 'upgrade_give_payment_customer_id' );
	delete_option( 'give_doing_upgrade' );
	wp_redirect( admin_url() );
	exit;

}

add_action( 'give_upgrade_give_payment_customer_id', 'give_v132_upgrade_give_payment_customer_id' );

/**
 * Upgrades the Offline Status
 *
 * Reverses the issue where offline donations in "pending" status where inappropriately marked as abandoned
 *
 * @since      1.3.4
 */
function give_v134_upgrade_give_offline_status() {

	global $wpdb;

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_die( esc_html__( 'You do not have permission to do Give upgrades.', 'give' ), esc_html__( 'Error', 'give' ), array(
			'response' => 403,
		) );
	}

	ignore_user_abort( true );

	if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	// Get abandoned offline payments.
	$select = "SELECT ID FROM $wpdb->posts p ";
	$join   = "LEFT JOIN $wpdb->postmeta m ON p.ID = m.post_id ";
	$where  = "WHERE p.post_type = 'give_payment' ";
	$where .= "AND ( p.post_status = 'abandoned' )";
	$where .= "AND ( m.meta_key = '_give_payment_gateway' AND m.meta_value = 'offline' )";

	$sql            = $select . $join . $where;
	$found_payments = $wpdb->get_col( $sql );

	foreach ( $found_payments as $payment ) {

		// Only change ones marked abandoned since our release last week because the admin may have marked some abandoned themselves.
		$modified_time = get_post_modified_time( 'U', false, $payment );

		// 1450124863 =  12/10/2015 20:42:25.
		if ( $modified_time >= 1450124863 ) {

			give_update_payment_status( $payment, 'pending' );

		}
	}

	update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
	give_set_upgrade_complete( 'upgrade_give_offline_status' );
	delete_option( 'give_doing_upgrade' );
	wp_redirect( admin_url() );
	exit;

}

add_action( 'give_upgrade_give_offline_status', 'give_v134_upgrade_give_offline_status' );

/**
 * Cleanup User Roles
 *
 * This upgrade routine removes unused roles and roles with typos
 *
 * @since      1.5.2
 */
function give_v152_cleanup_users() {

	$give_version = get_option( 'give_version' );

	if ( ! $give_version ) {
		// 1.0 is the first version to use this option so we must add it.
		$give_version = '1.0';
	}

	$give_version = preg_replace( '/[^0-9.].*/', '', $give_version );

	// v1.5.2 Upgrades
	if ( version_compare( $give_version, '1.5.2', '<' ) || ! give_has_upgrade_completed( 'upgrade_give_user_caps_cleanup' ) ) {

		// Delete all caps with "ss".
		// Also delete all unused "campaign" roles.
		$delete_caps = array(
			'delete_give_formss',
			'delete_others_give_formss',
			'delete_private_give_formss',
			'delete_published_give_formss',
			'read_private_forms',
			'edit_give_formss',
			'edit_others_give_formss',
			'edit_private_give_formss',
			'edit_published_give_formss',
			'publish_give_formss',
			'read_private_give_formss',
			'assign_give_campaigns_terms',
			'delete_give_campaigns',
			'delete_give_campaigns_terms',
			'delete_give_campaignss',
			'delete_others_give_campaignss',
			'delete_private_give_campaignss',
			'delete_published_give_campaignss',
			'edit_give_campaigns',
			'edit_give_campaigns_terms',
			'edit_give_campaignss',
			'edit_others_give_campaignss',
			'edit_private_give_campaignss',
			'edit_published_give_campaignss',
			'manage_give_campaigns_terms',
			'publish_give_campaignss',
			'read_give_campaigns',
			'read_private_give_campaignss',
			'view_give_campaigns_stats',
			'delete_give_paymentss',
			'delete_others_give_paymentss',
			'delete_private_give_paymentss',
			'delete_published_give_paymentss',
			'edit_give_paymentss',
			'edit_others_give_paymentss',
			'edit_private_give_paymentss',
			'edit_published_give_paymentss',
			'publish_give_paymentss',
			'read_private_give_paymentss',
		);

		global $wp_roles;
		foreach ( $delete_caps as $cap ) {
			foreach ( array_keys( $wp_roles->roles ) as $role ) {
				$wp_roles->remove_cap( $role, $cap );
			}
		}

		// Create Give plugin roles.
		$roles = new Give_Roles();
		$roles->add_roles();
		$roles->add_caps();

		// The Update Ran.
		update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
		give_set_upgrade_complete( 'upgrade_give_user_caps_cleanup' );
		delete_option( 'give_doing_upgrade' );

	}// End if().

}

add_action( 'admin_init', 'give_v152_cleanup_users' );

/**
 * 1.6 Upgrade routine to create the customer meta table.
 *
 * @since  1.6
 * @return void
 */
function give_v16_upgrades() {
	@Give()->customers->create_table();
	@Give()->customer_meta->create_table();
}

/**
 * 1.7 Upgrades.
 *
 * a. Update license api data for plugin addons.
 * b. Cleanup user roles.
 *
 * @since  1.7
 * @return void
 */
function give_v17_upgrades() {
	// Upgrade license data.
	give_v17_upgrade_addon_license_data();
	give_v17_cleanup_roles();
}

/**
 * Upgrade license data
 *
 * @since 1.7
 */
function give_v17_upgrade_addon_license_data() {
	$give_options = give_get_settings();

	$api_url = 'https://givewp.com/give-sl-api/';

	// Get addons license key.
	$addons = array();
	foreach ( $give_options as $key => $value ) {
		if ( false !== strpos( $key, '_license_key' ) ) {
			$addons[ $key ] = $value;
		}
	}

	// Bailout: We do not have any addon license data to upgrade.
	if ( empty( $addons ) ) {
		return false;
	}

	foreach ( $addons as $key => $addon_license ) {

		// Get addon shortname.
		$shortname = str_replace( '_license_key', '', $key );

		// Addon license option name.
		$addon_license_option_name = $shortname . '_license_active';

		// bailout if license is empty.
		if ( empty( $addon_license ) ) {
			delete_option( $addon_license_option_name );
			continue;
		}

		// Get addon name.
		$addon_name       = array();
		$addon_name_parts = explode( '_', str_replace( 'give_', '', $shortname ) );
		foreach ( $addon_name_parts as $name_part ) {

			// Fix addon name
			switch ( $name_part ) {
				case 'authorizenet' :
					$name_part = 'authorize.net';
					break;
			}

			$addon_name[] = ucfirst( $name_part );
		}

		$addon_name = implode( ' ', $addon_name );

		// Data to send to the API
		$api_params = array(
			'edd_action' => 'activate_license', // never change from "edd_" to "give_"!
			'license'    => $addon_license,
			'item_name'  => urlencode( $addon_name ),
			'url'        => home_url(),
		);

		// Call the API.
		$response = wp_remote_post(
			$api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		// Make sure there are no errors.
		if ( is_wp_error( $response ) ) {
			delete_option( $addon_license_option_name );
			continue;
		}

		// Tell WordPress to look for updates.
		set_site_transient( 'update_plugins', null );

		// Decode license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		update_option( $addon_license_option_name, $license_data );
	}// End foreach().
}


/**
 * Cleanup User Roles.
 *
 * This upgrade routine removes unused roles and roles with typos.
 *
 * @since      1.7
 */
function give_v17_cleanup_roles() {

	// Delete all caps with "_give_forms_" and "_give_payments_"
	// These roles have no usage; the proper is singular.
	$delete_caps = array(
		'view_give_forms_stats',
		'delete_give_forms_terms',
		'assign_give_forms_terms',
		'edit_give_forms_terms',
		'manage_give_forms_terms',
		'view_give_payments_stats',
		'manage_give_payments_terms',
		'edit_give_payments_terms',
		'assign_give_payments_terms',
		'delete_give_payments_terms',
	);

	global $wp_roles;
	foreach ( $delete_caps as $cap ) {
		foreach ( array_keys( $wp_roles->roles ) as $role ) {
			$wp_roles->remove_cap( $role, $cap );
		}
	}

	// Set roles again.
	$roles = new Give_Roles();
	$roles->add_roles();
	$roles->add_caps();

}

/**
 * 1.8 Upgrades.
 *
 * a. Upgrade checkbox settings to radio button settings.
 * a. Update form meta for new metabox settings.
 *
 * @since  1.8
 * @return void
 */
function give_v18_upgrades() {
	// Upgrade checkbox settings to radio button settings.
	give_v18_upgrades_core_setting();
}

/**
 * Upgrade core settings.
 *
 * @since  1.8
 * @return void
 */
function give_v18_upgrades_core_setting() {
	// Core settings which changes from checkbox to radio.
	$core_setting_names = array_merge(
		array_keys( give_v18_renamed_core_settings() ),
		array(
			'uninstall_on_delete',
			'scripts_footer',
			'test_mode',
			'email_access',
			'terms',
			'give_offline_donation_enable_billing_fields',
		)
	);

	// Bailout: If not any setting define.
	if ( $give_settings = get_option( 'give_settings' ) ) {

		$setting_changed = false;

		// Loop: check each setting field.
		foreach ( $core_setting_names as $setting_name ) {
			// New setting name.
			$new_setting_name = preg_replace( '/^(enable_|disable_)/', '', $setting_name );

			// Continue: If setting already set.
			if (
				array_key_exists( $new_setting_name, $give_settings )
				&& in_array( $give_settings[ $new_setting_name ], array( 'enabled', 'disabled' ) )
			) {
				continue;
			}

			// Set checkbox value to radio value.
			$give_settings[ $setting_name ] = ( ! empty( $give_settings[ $setting_name ] ) && 'on' === $give_settings[ $setting_name ] ? 'enabled' : 'disabled' );

			// @see https://github.com/WordImpress/Give/issues/1063
			if ( false !== strpos( $setting_name, 'disable_' ) ) {

				$give_settings[ $new_setting_name ] = ( give_is_setting_enabled( $give_settings[ $setting_name ] ) ? 'disabled' : 'enabled' );
			} elseif ( false !== strpos( $setting_name, 'enable_' ) ) {

				$give_settings[ $new_setting_name ] = ( give_is_setting_enabled( $give_settings[ $setting_name ] ) ? 'enabled' : 'disabled' );
			}

			// Tell bot to update core setting to db.
			if ( ! $setting_changed ) {
				$setting_changed = true;
			}
		}

		// Update setting only if they changed.
		if ( $setting_changed ) {
			update_option( 'give_settings', $give_settings );
		}
	}// End if().

	give_set_upgrade_complete( 'v18_upgrades_core_setting' );
}

/**
 * Upgrade form metadata for new metabox settings.
 *
 * @since  1.8
 * @return void
 */
function give_v18_upgrades_form_metadata() {
	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_die( esc_html__( 'You do not have permission to do Give upgrades.', 'give' ), esc_html__( 'Error', 'give' ), array(
			'response' => 403,
		) );
	}

	ignore_user_abort( true );

	if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	$step = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;

	// form query
	$forms = new WP_Query( array(
			'paged'          => $step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'give_forms',
			'posts_per_page' => 20,
		)
	);

	if ( $forms->have_posts() ) {
		while ( $forms->have_posts() ) {
			$forms->the_post();

			// Form content.
			// Note in version 1.8 display content setting split into display content and content placement setting.
			// You can delete _give_content_option in future
			$show_content = get_post_meta( get_the_ID(), '_give_content_option', true );
			if ( $show_content && ! get_post_meta( get_the_ID(), '_give_display_content', true ) ) {
				$field_value = ( 'none' !== $show_content ? 'enabled' : 'disabled' );
				update_post_meta( get_the_ID(), '_give_display_content', $field_value );

				$field_value = ( 'none' !== $show_content ? $show_content : 'give_pre_form' );
				update_post_meta( get_the_ID(), '_give_content_placement', $field_value );
			}

			// "Disable" Guest Donation. Checkbox
			// See: https://github.com/WordImpress/Give/issues/1470
			$guest_donation = get_post_meta( get_the_ID(), '_give_logged_in_only', true );
			$guest_donation_newval = ( in_array( $guest_donation, array( 'yes', 'on' ) ) ? 'disabled' : 'enabled' );
			update_post_meta( get_the_ID(), '_give_logged_in_only', $guest_donation_newval );

			// Offline Donations
			// See: https://github.com/WordImpress/Give/issues/1579
			$offline_donation = get_post_meta( get_the_ID(), '_give_customize_offline_donations', true );
			if ( 'no' === $offline_donation ) {
				$offline_donation_newval = 'global';
			} elseif ( 'yes' === $offline_donation ) {
				$offline_donation_newval = 'enabled';
			} else {
				$offline_donation_newval = 'disabled';
			}
			update_post_meta( get_the_ID(), '_give_customize_offline_donations', $offline_donation_newval );

			// Convert yes/no setting field to enabled/disabled.
			$form_radio_settings = array(
				// Custom Amount.
				'_give_custom_amount',

				// Donation Gaol.
				'_give_goal_option',

				// Close Form.
				'_give_close_form_when_goal_achieved',

				// Term & conditions.
				'_give_terms_option',

				// Billing fields.
				'_give_offline_donation_enable_billing_fields_single',
			);

			foreach ( $form_radio_settings as $meta_key ) {
				// Get value.
				$field_value = get_post_meta( get_the_ID(), $meta_key, true );

				// Convert meta value only if it is in yes/no/none.
				if ( in_array( $field_value, array( 'yes', 'on', 'no', 'none' ) ) ) {

					$field_value = ( in_array( $field_value, array( 'yes', 'on' ) ) ? 'enabled' : 'disabled' );
					update_post_meta( get_the_ID(), $meta_key, $field_value );
				}
			}
		}// End while().

		wp_reset_postdata();

		// Forms found so upgrade them
		$step ++;
		$redirect = add_query_arg( array(
			'page'         => 'give-upgrades',
			'give-upgrade' => 'give_v18_upgrades_form_metadata',
			'step'         => $step,
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect );
		exit();

	} else {
		// No more forms found, finish up.
		update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
		delete_option( 'give_doing_upgrade' );
		give_set_upgrade_complete( 'v18_upgrades_form_metadata' );

		wp_redirect( admin_url() );
		exit;
	}
}

add_action( 'give_give_v18_upgrades_form_metadata', 'give_v18_upgrades_form_metadata' );

/**
 * Get list of core setting renamed in version 1.8.
 *
 * @since  1.8
 * @return array
 */
function give_v18_renamed_core_settings() {
	return array(
		'disable_paypal_verification' => 'paypal_verification',
		'disable_css'                 => 'css',
		'disable_welcome'             => 'welcome',
		'disable_forms_singular'      => 'forms_singular',
		'disable_forms_archives'      => 'forms_archives',
		'disable_forms_excerpt'       => 'forms_excerpt',
		'disable_form_featured_img'   => 'form_featured_img',
		'disable_form_sidebar'        => 'form_sidebar',
		'disable_admin_notices'       => 'admin_notices',
		'disable_the_content_filter'  => 'the_content_filter',
		'enable_floatlabels'          => 'floatlabels',
		'enable_categories'           => 'categories',
		'enable_tags'                 => 'tags',
	);
}
