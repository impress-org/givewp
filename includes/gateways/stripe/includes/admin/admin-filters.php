<?php
/**
 * Give - Stripe Core | Admin Filters
 *
 * @since      2.5.0
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
 * @param string $transaction_id The Transaction ID.
 * @param int    $payment_id     The payment ID for this transaction.
 *
 * @return string                 A link to the Transaction details
 * @since 2.5.0
 *
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
 * @param array $settings Settings List.
 * @param int   $form_id  Form ID.
 *
 * @return array
 * @since 2.7.0
 *
 */
function give_stripe_add_metabox_settings( $settings, $form_id ) {
	$form_account       = give_is_setting_enabled( give_clean( give_get_meta( $form_id, 'give_stripe_per_form_accounts', true ) ) );
	$defaultAccountSlug = give_stripe_get_default_account_slug();

	// Must have a Stripe payment gateway active.
	if ( ! give_stripe_is_any_payment_method_active() ) {
		return $settings;
	}

	$settings['stripe_form_account_options'] = [
		'id'        => 'stripe_form_account_options',
		'title'     => esc_html__( 'Stripe Account', 'give' ),
		'icon-html' => '<i class="fab fa-stripe-s"></i>',
		'fields'    => [
			[
				'name'        => esc_html__( 'Account Options', 'give' ),
				'id'          => 'give_stripe_per_form_accounts',
				'type'        => 'radio_inline',
				'default'     => 'disabled',
				'options'     => [
					'disabled' => esc_html__( 'Use Global Default Stripe Account', 'give' ),
					'enabled'  => esc_html__( 'Customize Stripe Account', 'give' ),
				],
				'description' => esc_html__( 'Do you want to customize the Stripe account for this donation form? The customize option allows you to modify the Stripe account this form processes payments through. By default, new donation forms will use the Global Default Stripe account.', 'give' ),
			],
			[
				'name'          => esc_html__( 'Active Stripe Account', 'give' ),
				'id'            => '_give_stripe_default_account',
				'type'          => 'radio',
				'default'       => $defaultAccountSlug,
				'options'       => give_stripe_get_account_options(),
				'wrapper_class' => $form_account ? 'give-stripe-per-form-default-account' : 'give-stripe-per-form-default-account give-hidden',
			],
			[
				'type'  => 'label',
				'id'    => 'give-stripe-add-account-link',
				'title' => sprintf(
					'<span style="display:block; margin: 22px 0 0 150px;"><a href="%1$s" class="button">%2$s</a></span>',
					admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ),
					esc_html__( 'Connect Stripe Account', 'give' )
				),
			],
			[
				'name'  => 'donation_stripe_per_form_docs',
				'type'  => 'docs_link',
				'url'   => 'http://docs.givewp.com/stripe-free',
				'title' => __( 'Stripe Documentation', 'give' ),
			],
		],
	];

	return $settings;
}

add_filter( 'give_metabox_form_data_settings', 'give_stripe_add_metabox_settings', 10, 2 );
