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

		case version_compare( $give_version, '1.7', '<' ) :
			give_v17_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8', '<' ) :
			give_v18_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8.7', '<' ) :
			give_v187_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8.8', '<' ) :
			give_v188_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8.9', '<' ) :
			give_v189_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8.12', '<' ) :
			give_v1812_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.8.13', '<' ) :
			give_v1813_upgrades();
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
 * @since 1.8.12 Update new update process code.
 *
 * @param Give_Updates $give_updates
 *
 * @return void
 */
function give_show_upgrade_notices( $give_updates ) {
	// v1.3.2 Upgrades
	$give_updates->register(
		array(
			'id'       => 'upgrade_give_payment_customer_id',
			'version'  => '1.3.2',
			'callback' => 'give_v132_upgrade_give_payment_customer_id',
		)
	);

	// v1.3.4 Upgrades ensure the user has gone through 1.3.4.
	$give_updates->register(
		array(
			'id'       => 'upgrade_give_offline_status',
			'depend'   => 'upgrade_give_payment_customer_id',
			'version'  => '1.3.4',
			'callback' => 'give_v134_upgrade_give_offline_status',
		)
	);

	// v1.8 form metadata upgrades.
	$give_updates->register(
		array(
			'id'       => 'v18_upgrades_form_metadata',
			'version'  => '1.8',
			'callback' => 'give_v18_upgrades_form_metadata',
		)
	);

	// v1.8.9 Upgrades
	$give_updates->register(
		array(
			'id'       => 'v189_upgrades_levels_post_meta',
			'version'  => '1.8.9',
			'callback' => 'give_v189_upgrades_levels_post_meta_callback',
		)
	);

	// v1.8.12 Upgrades
	$give_updates->register(
		array(
			'id'       => 'v1812_update_amount_values',
			'version'  => '1.8.12',
			'callback' => 'give_v1812_update_amount_values_callback',
		)
	);

	// v1.8.12 Upgrades
	$give_updates->register(
		array(
			'id'       => 'v1812_update_donor_purchase_values',
			'version'  => '1.8.12',
			'callback' => 'give_v1812_update_donor_purchase_value_callback',
		)
	);

	// v1.8.13 Upgrades for donor
	$give_updates->register(
		array(
			'id'       => 'v1813_update_donor_user_roles',
			'version'  => '1.8.13',
			'callback' => 'give_v1813_update_donor_user_roles_callback',
		)
	);
}

add_action( 'give_register_updates', 'give_show_upgrade_notices' );

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
 * Upgrades the
 *
 * Standardizes the discrepancies between two metakeys `_give_payment_customer_id` and `_give_payment_donor_id`
 *
 * @since      1.3.2
 */
function give_v132_upgrade_give_payment_customer_id() {
	global $wpdb;

	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

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

	$give_updates->percentage = 100;
	give_set_upgrade_complete( 'upgrade_give_payment_customer_id' );
}


/**
 * Upgrades the Offline Status
 *
 * Reverses the issue where offline donations in "pending" status where inappropriately marked as abandoned
 *
 * @since      1.3.4
 */
function give_v134_upgrade_give_offline_status() {
	global $wpdb;

	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	// Get abandoned offline payments.
	$select = "SELECT ID FROM $wpdb->posts p ";
	$join   = "LEFT JOIN $wpdb->postmeta m ON p.ID = m.post_id ";
	$where  = "WHERE p.post_type = 'give_payment' ";
	$where  .= "AND ( p.post_status = 'abandoned' )";
	$where  .= "AND ( m.meta_key = '_give_payment_gateway' AND m.meta_value = 'offline' )";

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

	$give_updates->percentage = 100;
	give_set_upgrade_complete( 'upgrade_give_offline_status' );
}


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
	// Create the donor databases.
	$donors_db = new Give_DB_Donors();
	$donors_db->create_table();
	$donor_meta = new Give_DB_Donor_Meta();
	$donor_meta->create_table();
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
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	// form query
	$forms = new WP_Query( array(
			'paged'          => $give_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'give_forms',
			'posts_per_page' => 20,
		)
	);

	if ( $forms->have_posts() ) {
		$give_updates->set_percentage( $forms->found_posts, ( $give_updates->step * 20 ) );

		while ( $forms->have_posts() ) {
			$forms->the_post();

			// Form content.
			// Note in version 1.8 display content setting split into display content and content placement setting.
			// You can delete _give_content_option in future
			$show_content = give_get_meta( get_the_ID(), '_give_content_option', true );
			if ( $show_content && ! give_get_meta( get_the_ID(), '_give_display_content', true ) ) {
				$field_value = ( 'none' !== $show_content ? 'enabled' : 'disabled' );
				give_update_meta( get_the_ID(), '_give_display_content', $field_value );

				$field_value = ( 'none' !== $show_content ? $show_content : 'give_pre_form' );
				give_update_meta( get_the_ID(), '_give_content_placement', $field_value );
			}

			// "Disable" Guest Donation. Checkbox
			// See: https://github.com/WordImpress/Give/issues/1470
			$guest_donation        = give_get_meta( get_the_ID(), '_give_logged_in_only', true );
			$guest_donation_newval = ( in_array( $guest_donation, array( 'yes', 'on' ) ) ? 'disabled' : 'enabled' );
			give_update_meta( get_the_ID(), '_give_logged_in_only', $guest_donation_newval );

			// Offline Donations
			// See: https://github.com/WordImpress/Give/issues/1579
			$offline_donation = give_get_meta( get_the_ID(), '_give_customize_offline_donations', true );
			if ( 'no' === $offline_donation ) {
				$offline_donation_newval = 'global';
			} elseif ( 'yes' === $offline_donation ) {
				$offline_donation_newval = 'enabled';
			} else {
				$offline_donation_newval = 'disabled';
			}
			give_update_meta( get_the_ID(), '_give_customize_offline_donations', $offline_donation_newval );

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
				$field_value = give_get_meta( get_the_ID(), $meta_key, true );

				// Convert meta value only if it is in yes/no/none.
				if ( in_array( $field_value, array( 'yes', 'on', 'no', 'none' ) ) ) {

					$field_value = ( in_array( $field_value, array( 'yes', 'on' ) ) ? 'enabled' : 'disabled' );
					give_update_meta( get_the_ID(), $meta_key, $field_value );
				}
			}
		}// End while().

		wp_reset_postdata();

	} else {
		// No more forms found, finish up.
		give_set_upgrade_complete( 'v18_upgrades_form_metadata' );
	}
}


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


/**
 * Upgrade core settings.
 *
 * @since  1.8.7
 * @return void
 */
function give_v187_upgrades() {
	global $wpdb;

	/**
	 * Upgrade 1: Remove stat and cache transients.
	 */
	$cached_options = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->options} where (option_name LIKE '%%%s%%' OR option_name LIKE '%%%s%%')",
			array(
				'_transient_give_stats_',
				'give_cache',
				'_transient_give_add_ons_feed',
				'_transient__give_ajax_works' .
				'_transient_give_total_api_keys',
				'_transient_give_i18n_give_promo_hide',
				'_transient_give_contributors',
				'_transient_give_estimated_monthly_stats',
				'_transient_give_earnings_total',
				'_transient_give_i18n_give_',
				'_transient__give_installed',
				'_transient__give_activation_redirect',
				'_transient__give_hide_license_notices_shortly_',
				'give_income_total',
			)
		),
		1
	);

	// User related transients.
	$user_apikey_options = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT user_id, meta_key
			FROM $wpdb->usermeta
			WHERE meta_value=%s",
			'give_user_public_key'
		),
		ARRAY_A
	);

	if ( ! empty( $user_apikey_options ) ) {
		foreach ( $user_apikey_options as $user ) {
			$cached_options[] = '_transient_' . md5( 'give_api_user_' . $user['meta_key'] );
			$cached_options[] = '_transient_' . md5( 'give_api_user_public_key' . $user['user_id'] );
			$cached_options[] = '_transient_' . md5( 'give_api_user_secret_key' . $user['user_id'] );
		}
	}

	if ( ! empty( $cached_options ) ) {
		foreach ( $cached_options as $option ) {
			switch ( true ) {
				case ( false !== strpos( $option, 'transient' ) ):
					$option = str_replace( '_transient_', '', $option );
					delete_transient( $option );
					break;

				default:
					delete_option( $option );
			}
		}
	}
}

/**
 * Update Capabilities for Give_Worker User Role.
 *
 * This upgrade routine will update access rights for Give_Worker User Role.
 *
 * @since      1.8.8
 */
function give_v188_upgrades() {

	global $wp_roles;

	// Get the role object.
	$give_worker = get_role( 'give_worker' );

	// A list of capabilities to add for give workers.
	$caps_to_add = array(
		'edit_posts',
		'edit_pages',
	);

	foreach ( $caps_to_add as $cap ) {
		// Add the capability.
		$give_worker->add_cap( $cap );
	}

}

/**
 * Update Post meta for minimum and maximum amount for multi level donation forms
 *
 * This upgrade routine adds post meta for give_forms CPT for multi level donation form.
 *
 * @since      1.8.9
 */
function give_v189_upgrades_levels_post_meta_callback() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	// form query
	$donation_forms = new WP_Query( array(
			'paged'          => $give_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => 'give_forms',
			'posts_per_page' => 20,
		)
	);

	if ( $donation_forms->have_posts() ) {
		$give_updates->set_percentage( $donation_forms->found_posts, ( $give_updates->step * 20 ) );

		while ( $donation_forms->have_posts() ) {
			$donation_forms->the_post();
			$form_id = get_the_ID();

			// Remove formatting from _give_set_price
			update_post_meta(
				$form_id,
				'_give_set_price',
				give_sanitize_amount( get_post_meta( $form_id, '_give_set_price', true ) )
			);

			// Remove formatting from _give_custom_amount_minimum
			update_post_meta(
				$form_id,
				'_give_custom_amount_minimum',
				give_sanitize_amount( get_post_meta( $form_id, '_give_custom_amount_minimum', true ) )
			);

			// Bailout.
			if ( 'set' === get_post_meta( $form_id, '_give_price_option', true ) ) {
				continue;
			}

			$donation_levels = get_post_meta( $form_id, '_give_donation_levels', true );

			if ( ! empty( $donation_levels ) ) {

				foreach ( $donation_levels as $index => $donation_level ) {
					if ( isset( $donation_level['_give_amount'] ) ) {
						$donation_levels[ $index ]['_give_amount'] = give_sanitize_amount( $donation_level['_give_amount'] );
					}
				}

				update_post_meta( $form_id, '_give_donation_levels', $donation_levels );

				$donation_levels_amounts = wp_list_pluck( $donation_levels, '_give_amount' );

				$min_amount = min( $donation_levels_amounts );
				$max_amount = max( $donation_levels_amounts );

				// Set Minimum and Maximum amount for Multi Level Donation Forms
				give_update_meta( $form_id, '_give_levels_minimum_amount', $min_amount ? give_sanitize_amount( $min_amount ) : 0 );
				give_update_meta( $form_id, '_give_levels_maximum_amount', $max_amount ? give_sanitize_amount( $max_amount ) : 0 );
			}

		}

		/* Restore original Post Data */
		wp_reset_postdata();
	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'v189_upgrades_levels_post_meta' );
	}

}


/**
 * Give version 1.8.9 upgrades
 *
 * @since      1.8.9
 */
function give_v189_upgrades() {
	/**
	 * 1. Remove user license related notice show blocked ( Give_Notice will handle )
	 */
	global $wpdb;

	// Delete permanent notice blocker.
	$wpdb->query(
		$wpdb->prepare(
			"
					DELETE FROM $wpdb->usermeta
					WHERE meta_key
					LIKE '%%%s%%'
					",
			'_give_hide_license_notices_permanently'
		)
	);

	// Delete short notice blocker.
	$wpdb->query(
		$wpdb->prepare(
			"
					DELETE FROM $wpdb->options
					WHERE option_name
					LIKE '%%%s%%'
					",
			'__give_hide_license_notices_shortly_'
		)
	);

}


/**
 * Give version 1.8.9 upgrades
 *
 * @since      1.8.9
 */
function give_v1812_upgrades() {
	/**
	 * Validate number format settings.
	 */
	$give_settings        = give_get_settings();
	$give_setting_updated = false;

	if ( $give_settings['thousands_separator'] === $give_settings['decimal_separator'] ) {
		$give_settings['number_decimals']   = 0;
		$give_settings['decimal_separator'] = '';
		$give_setting_updated               = true;

	} elseif ( empty( $give_settings['decimal_separator'] ) ) {
		$give_settings['number_decimals'] = 0;
		$give_setting_updated             = true;

	} elseif ( 6 < absint( $give_settings['number_decimals'] ) ) {
		$give_settings['number_decimals'] = 5;
		$give_setting_updated             = true;
	}

	if ( $give_setting_updated ) {
		update_option( 'give_settings', $give_settings );
	}
}


/**
 * Give version 1.8.12 update
 *
 * Standardized amount values to six decimal
 *
 * @see        https://github.com/WordImpress/Give/issues/1849#issuecomment-315128602
 *
 * @since      1.8.12
 */
function give_v1812_update_amount_values_callback() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();

	// form query
	$donation_forms = new WP_Query( array(
			'paged'          => $give_updates->step,
			'status'         => 'any',
			'order'          => 'ASC',
			'post_type'      => array( 'give_forms', 'give_payment' ),
			'posts_per_page' => 20,
		)
	);

	if ( $donation_forms->have_posts() ) {
		$give_updates->set_percentage( $donation_forms->found_posts, ( $give_updates->step * 20 ) );

		while ( $donation_forms->have_posts() ) {
			$donation_forms->the_post();
			global $post;

			$meta = get_post_meta( $post->ID );

			switch ( $post->post_type ) {
				case 'give_forms':
					// _give_set_price
					if ( ! empty( $meta['_give_set_price'][0] ) ) {
						update_post_meta( $post->ID, '_give_set_price', give_sanitize_amount_for_db( $meta['_give_set_price'][0] ) );
					}

					// _give_custom_amount_minimum
					if ( ! empty( $meta['_give_custom_amount_minimum'][0] ) ) {
						update_post_meta( $post->ID, '_give_custom_amount_minimum', give_sanitize_amount_for_db( $meta['_give_custom_amount_minimum'][0] ) );
					}

					// _give_levels_minimum_amount
					if ( ! empty( $meta['_give_levels_minimum_amount'][0] ) ) {
						update_post_meta( $post->ID, '_give_levels_minimum_amount', give_sanitize_amount_for_db( $meta['_give_levels_minimum_amount'][0] ) );
					}

					// _give_levels_maximum_amount
					if ( ! empty( $meta['_give_levels_maximum_amount'][0] ) ) {
						update_post_meta( $post->ID, '_give_levels_maximum_amount', give_sanitize_amount_for_db( $meta['_give_levels_maximum_amount'][0] ) );
					}

					// _give_set_goal
					if ( ! empty( $meta['_give_set_goal'][0] ) ) {
						update_post_meta( $post->ID, '_give_set_goal', give_sanitize_amount_for_db( $meta['_give_set_goal'][0] ) );
					}

					// _give_form_earnings
					if ( ! empty( $meta['_give_form_earnings'][0] ) ) {
						update_post_meta( $post->ID, '_give_form_earnings', give_sanitize_amount_for_db( $meta['_give_form_earnings'][0] ) );
					}

					// _give_custom_amount_minimum
					if ( ! empty( $meta['_give_donation_levels'][0] ) ) {
						$donation_levels = unserialize( $meta['_give_donation_levels'][0] );

						foreach ( $donation_levels as $index => $level ) {
							if ( empty( $level['_give_amount'] ) ) {
								continue;
							}

							$donation_levels[ $index ]['_give_amount'] = give_sanitize_amount_for_db( $level['_give_amount'] );
						}

						$meta['_give_donation_levels'] = $donation_levels;
						update_post_meta( $post->ID, '_give_donation_levels', $meta['_give_donation_levels'] );
					}


					break;

				case 'give_payment':
					// _give_payment_total
					if ( ! empty( $meta['_give_payment_total'][0] ) ) {
						update_post_meta( $post->ID, '_give_payment_total', give_sanitize_amount_for_db( $meta['_give_payment_total'][0] ) );
					}

					break;
			}
		}

		/* Restore original Post Data */
		wp_reset_postdata();
	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'v1812_update_amount_values' );
	}
}


/**
 * Give version 1.8.12 update
 *
 * Standardized amount values to six decimal for donor
 *
 * @see        https://github.com/WordImpress/Give/issues/1849#issuecomment-315128602
 *
 * @since      1.8.12
 */
function give_v1812_update_donor_purchase_value_callback() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();
	$offset       = 1 === $give_updates->step ? 0 : $give_updates->step * 20;

	// form query
	$donors = Give()->donors->get_donors( array(
			'number' => 20,
			'offset' => $offset,
		)
	);

	if ( ! empty( $donors ) ) {
		$give_updates->set_percentage( Give()->donors->count(), ( $give_updates->step * 20 ) );

		/* @var Object $donor */
		foreach ( $donors as $donor ) {
			Give()->donors->update( $donor->id, array( 'purchase_value' => give_sanitize_amount_for_db( $donor->purchase_value ) ) );
		}
	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'v1812_update_donor_purchase_values' );
	}
}

/**
 * Upgrade routine for updating user roles for existing donors.
 *
 * @since 1.8.13
 */
function give_v1813_update_donor_user_roles_callback() {
	/* @var Give_Updates $give_updates */
	$give_updates = Give_Updates::get_instance();
	$offset       = 1 === $give_updates->step ? 0 : $give_updates->step * 20;

	// Fetch all the existing donors.
	$donors = Give()->donors->get_donors( array(
			'number' => 20,
			'offset' => $offset,
		)
	);

	if ( ! empty( $donors ) ) {
		$give_updates->set_percentage( Give()->donors->count(), ( $give_updates->step * 20 ) );

		/* @var Object $donor */
		foreach ( $donors as $donor ) {
			$user_id = $donor->user_id;

			// Proceed, if donor is attached with user.
			if ( $user_id ) {
				$user = get_userdata( $user_id );

				// Update user role, if user has subscriber role.
				if ( is_array( $user->roles ) && in_array( 'subscriber', $user->roles ) ) {
					wp_update_user(
						array(
							'ID'   => $user_id,
							'role' => 'give_donor',
						)
					);
				}
			}
		}
	} else {
		// The Update Ran.
		give_set_upgrade_complete( 'v1813_update_donor_user_roles' );
	}
}


/**
 * Version 1.8.13 automatic updates
 *
 * @since 1.8.13
 */
function give_v1813_upgrades() {
	// Update admin setting.
	give_update_option( 'donor_default_user_role', 'give_donor' );

	// Update Give roles.
	$roles = new Give_Roles();
	$roles->add_roles();
	$roles->add_caps();
}
