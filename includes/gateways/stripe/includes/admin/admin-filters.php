<?php
/**
 * Give - Stripe Core | Admin Filters
 *
 * @since 2.5.0
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Given a transaction ID, generate a link to the Stripe transaction ID details
 *
 * @since 2.5.0
 *
 * @param string $transaction_id The Transaction ID.
 * @param int    $payment_id The payment ID for this transaction.
 *
 * @return string                 A link to the Transaction details
 */
function give_stripe_link_transaction_id( $transaction_id, $payment_id ) {

	$url = give_stripe_get_transaction_link( $payment_id, $transaction_id );

	return apply_filters( 'give_stripe_link_donation_details_transaction_id', $url );

}

add_filter( 'give_payment_details_transaction_id-stripe', 'give_stripe_link_transaction_id', 10, 2 );
add_filter( 'give_payment_details_transaction_id-stripe_checkout', 'give_stripe_link_transaction_id', 10, 2 );
add_filter( 'give_payment_details_transaction_id-stripe_sepa', 'give_stripe_link_transaction_id', 10, 2 );
add_filter( 'give_payment_details_transaction_id-stripe_becs', 'give_stripe_link_transaction_id', 10, 2 );

/**
 * This function is used to add per-form Stripe account management.
 *
 * @since 2.7.0
 *
 * @param array $settings Settings List.
 * @param int   $form_id  Form ID.
 *
 * @return array
 */
function give_stripe_add_metabox_settings( $settings, $form_id ) {
	$form_account         = give_is_setting_enabled( give_get_meta( $form_id, 'give_stripe_per_form_accounts', true ) );
	$account_options      = give_stripe_get_account_options();
	$account_options_keys = array_keys( $account_options );
	$first_account_key    = $account_options_keys ? $account_options_keys[0] : [];

	$settings['stripe_form_account_options'] = array(
		'id'        => 'stripe_form_account_options',
		'title'     => esc_html__( 'Manage Accounts', 'give' ),
		'icon-html' => sprintf(
			'<img class="give-stripe-icon" src="%1$s" />',
			GIVE_PLUGIN_URL . 'assets/dist/images/admin/stripe-icon.png'
		),
		'fields'    => array(
			array(
				'name'    => esc_html__( 'Account Options', 'give' ),
				'id'      => 'give_stripe_per_form_accounts',
				'type'    => 'radio_inline',
				'default' => 'disabled',
				'options' => array(
					'disabled' => esc_html__( 'Global', 'give' ),
					'enabled'  => esc_html__( 'Customize', 'give' ),
				),
			),
			array(
				'name'          => esc_html__( 'Stripe Accounts', 'give' ),
				'id'            => '_give_stripe_default_account',
				'type'          => 'radio',
				'default'       => $first_account_key,
				'options'       => give_stripe_get_account_options(),
				'wrapper_class' => $form_account ? 'give-stripe-per-form-default-account' : 'give-stripe-per-form-default-account give-hidden',
			),
			array(
				'type'  => 'label',
				'id'    => 'give-stripe-add-account-link',
				'title' => sprintf(
					'<a href="%1$s">%2$s</a> %3$s',
					admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ),
					esc_html__( 'Click here', 'give' ),
					esc_html__( 'to add new Stripe account.', 'give' )
				),
			),
		),
	);

	return $settings;
}

add_filter( 'give_metabox_form_data_settings', 'give_stripe_add_metabox_settings', 10, 2 );
