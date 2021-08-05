<?php
/**
 * Give - Stripe Core Admin Actions
 *
 * @since 2.5.0
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
use Give\Helpers\Gateways\Stripe;
use Give\PaymentGateways\Stripe\Admin\AccountManagerSettingField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disconnects user from the Give Stripe Connected App.
 */
function give_stripe_connect_deauthorize() {
	$get_vars = give_clean( $_GET );

	// Be sure only to deauthorize when param present.
	if ( ! isset( $get_vars['stripe_disconnected'] ) ) {
		return false;
	}

	// Show message if NOT disconnected.
	if (
		'false' === $get_vars['stripe_disconnected']
		&& isset( $get_vars['error_code'] )
	) {

		$class   = 'notice notice-warning give-stripe-disconnect-message';
		$message = sprintf(
			/* translators: %s Error Message */
			__( '<strong>Error:</strong> GiveWP could not disconnect from the Stripe API. Reason: %s', 'give' ),
			esc_html( $get_vars['error_message'] )
		);

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );

	}
}
add_action( 'admin_notices', 'give_stripe_connect_deauthorize' );

/**
 * This function will display field to opt for refund in Stripe.
 *
 * @param int $donation_id Donation ID.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_opt_refund( $donation_id ) {

	$processed_gateway = Give()->payment_meta->get_meta( $donation_id, '_give_payment_gateway', true );

	// Bail out, if the donation is not processed with Stripe payment gateway.
	if ( ! in_array( $processed_gateway, give_stripe_supported_payment_methods(), true ) ) {
		return;
	}
	?>
	<div id="give-stripe-opt-refund-wrap" class="give-stripe-opt-refund give-admin-box-inside give-hidden">
		<p>
			<input type="checkbox" id="give-stripe-opt-refund" name="give_stripe_opt_refund" value="1"/>
			<label for="give-stripe-opt-refund">
				<?php esc_html_e( 'Refund Charge in Stripe?', 'give' ); ?>
			</label>
		</p>
	</div>

	<?php
}

add_action( 'give_view_donation_details_totals_after', 'give_stripe_opt_refund', 11, 1 );

/**
 * Process refund in Stripe.
 *
 * @since  2.5.0
 * @access public
 *
 * @param int    $donation_id Donation ID.
 * @param string $new_status  New Donation Status.
 * @param string $old_status  Old Donation Status.
 *
 * @return void
 */
function give_stripe_process_refund( $donation_id, $new_status, $old_status ) {

	$stripe_opt_refund_value = ! empty( $_POST['give_stripe_opt_refund'] ) ? give_clean( $_POST['give_stripe_opt_refund'] ) : '';
	$can_process_refund      = ! empty( $stripe_opt_refund_value ) ? $stripe_opt_refund_value : false;

	// Only move forward if refund requested.
	if ( ! $can_process_refund ) {
		return;
	}

	// Verify statuses.
	$should_process_refund = 'publish' !== $old_status ? false : true;
	$should_process_refund = apply_filters( 'give_stripe_should_process_refund', $should_process_refund, $donation_id, $new_status, $old_status );

	if ( false === $should_process_refund ) {
		return;
	}

	if ( 'refunded' !== $new_status ) {
		return;
	}

	$charge_id = give_get_payment_transaction_id( $donation_id );

	// If no charge ID, look in the payment notes.
	if ( empty( $charge_id ) || $charge_id == $donation_id ) {
		$charge_id = give_stripe_get_payment_txn_id_fallback( $donation_id );
	}

	// Bail if no charge ID was found.
	if ( empty( $charge_id ) ) {
		return;
	}

	// Get Form ID.
	$form_id = give_get_payment_form_id( $donation_id );

	// Set App Info.
	give_stripe_set_app_info( $form_id );

	try {

		$args = [
			'charge' => $charge_id,
		];

		// If the donation is processed with payment intent then refund using payment intent.
		if ( give_stripe_is_source_type( $charge_id, 'pi' ) ) {
			$args = [
				'payment_intent' => $charge_id,
			];
		}

		$refund = \Stripe\Refund::create( $args );

		if ( isset( $refund->id ) ) {
			give_insert_payment_note(
				$donation_id,
				sprintf(
					/* translators: 1. Refund ID */
					esc_html__( 'Charge refunded in Stripe: %s', 'give' ),
					$refund->id
				)
			);
		}
	} catch ( \Stripe\Error\Base $e ) {
		// Refund issue occurred.
		$log_message  = __( 'The Stripe payment gateway returned an error while refunding a donation.', 'give' ) . '<br><br>';
		$log_message .= sprintf( esc_html__( 'Message: %s', 'give' ), $e->getMessage() ) . '<br><br>';
		$log_message .= sprintf( esc_html__( 'Code: %s', 'give' ), $e->getCode() );

		// Log it with DB.
		give_record_gateway_error( __( 'Stripe Error', 'give' ), $log_message );

	} catch ( Exception $e ) {

		// some sort of other error.
		$body = $e->getJsonBody();
		$err  = $body['error'];

		if ( isset( $err['message'] ) ) {
			$error = $err['message'];
		} else {
			$error = esc_html__( 'Something went wrong while refunding the charge in Stripe.', 'give' );
		}

		wp_die(
			$error,
			esc_html__( 'Error', 'give' ),
			[
				'response' => 400,
			]
		);

	} // End try().

	do_action( 'give_stripe_donation_refunded', $donation_id );

}

add_action( 'give_update_payment_status', 'give_stripe_process_refund', 200, 3 );

/**
 * Displays the "Give Connect" banner.
 *
 * @since 2.5.0
 *
 * @see: https://stripe.com/docs/connect/reference
 *
 * @return bool
 */
function give_stripe_show_connect_banner() {

	$status                       = true;
	$stripe_payment_methods       = give_stripe_supported_payment_methods();
	$is_any_stripe_gateway_active = array_map( 'give_is_gateway_active', $stripe_payment_methods );

	// Don't show banner, if all the stripe gateways are disabled.
	if ( ! in_array( true, $is_any_stripe_gateway_active, true ) ) {
		$status = false;
	}

	// Don't show if already connected.
	if ( Give\Helpers\Gateways\Stripe::isAccountConfigured() ) {
		$status = false;
	}

	$hide_on_sections = [ 'stripe-settings', 'gateways-settings', 'stripe-ach-settings' ];
	$current_section  = give_get_current_setting_section();

	// Don't show if on the payment settings section.
	if (
		'gateways' === give_get_current_setting_tab() &&
		(
			empty( $current_section ) ||
			in_array( $current_section, $hide_on_sections, true )
		)
	) {
		$status = false;
	}

	// Don't show for non-admins.
	if ( ! current_user_can( 'update_plugins' ) ) {
		$status = false;
	}

	// Additional Check: For multiple accounts management.
	$all_accounts = give_stripe_get_all_accounts();
	if ( $all_accounts ) {
		$status = false;
	}

	/**
	 * This filter hook is used to decide whether the connect button banner need to be displayed or not.
	 *
	 * @since 2.5.0
	 */
	$status = apply_filters( 'give_stripe_connect_banner_status', $status );

	// Bailout, if status is false.
	if ( false === $status ) {
		return $status;
	}

	$connect_link = give( AccountManagerSettingField::class )->getStripeConnectButtonMarkup();

	// Default message.
	$main_text = __( 'The Stripe gateway is enabled but you\'re not connected. Connect to Stripe to start accepting credit card donations directly on your website.', 'give' );

	/**
	 * This filter hook is used to change the text of the connect banner.
	 *
	 * @param string $main_text Text to be displayed on the connect banner.
	 *
	 * @since 2.5.0
	 */
	$main_text = apply_filters( 'give_stripe_change_connect_banner_text', $main_text );

	$message = sprintf(
		/* translators: 1. Main Text, 2. Connect Link */
		__( '<p><strong>Stripe Connect:</strong> %1$s </p>%2$s', 'give' ),
		$main_text,
		$connect_link
	);

	// Register Notice.
	Give()->notices->register_notice(
		[
			'id'               => 'give-stripe-connect-banner',
			'description'      => $message,
			'type'             => 'warning',
			'dismissible_type' => 'user',
			'dismiss_interval' => 'shortly',
		]
	);
}

add_action( 'admin_notices', 'give_stripe_show_connect_banner' );

/**
 * Register Currency related admin notices.
 *
 * @since 2.6.1
 *
 * @return void
 */
function give_stripe_show_currency_notice() {

	// Bailout, if not admin.
	if ( ! is_admin() ) {
		return;
	}

	// Show Currency notice when Stripe SEPA Payment Gateway is selected.
	if (
		current_user_can( 'manage_give_settings' ) &&
		give_is_gateway_active( 'stripe_sepa' ) &&
		'EUR' !== give_get_currency() &&
		! class_exists( 'Give_Currency_Switcher' ) // Disable Notice, if Currency Switcher add-on is enabled.
	) {
		Give()->notices->register_notice(
			[
				'id'          => 'give-stripe-currency-notice',
				'type'        => 'error',
				'dismissible' => false,
				'description' => sprintf(
					__( 'The currency must be set as "Euro (&euro;)" within Give\'s <a href="%s">Currency Settings</a> in order to collect donations through the Stripe - SEPA Direct Debit Payment Gateway.', 'give' ),
					admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=general&section=currency-settings' )
				),
				'show'        => true,
			]
		);
	}

	// Show Currency notice when Stripe BECS Payment Gateway is selected.
	if (
		current_user_can( 'manage_give_settings' ) &&
		give_is_gateway_active( 'stripe_becs' ) &&
		'AUD' !== give_get_currency() &&
		! class_exists( 'Give_Currency_Switcher' ) // Disable Notice, if Currency Switcher add-on is enabled.
	) {
		Give()->notices->register_notice(
			[
				'id'          => 'give-stripe-currency-notice',
				'type'        => 'error',
				'dismissible' => false,
				'description' => sprintf(
					__( 'The currency must be set as "AUD (&dollar;)" within Give\'s <a href="%s">Currency Settings</a> in order to collect donations through the Stripe - BECS Direct Debit Payment Gateway.', 'give' ),
					admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=general&section=currency-settings' )
				),
				'show'        => true,
			]
		);
	}

}

add_action( 'admin_notices', 'give_stripe_show_currency_notice' );

/**
 * Disconnect Stripe Account.
 *
 * @since 2.7.0
 *
 * @return void
 */
function give_stripe_disconnect_connect_stripe_account() {
	$get_data = give_clean( $_GET );

	if ( current_user_can( 'manage_options' ) && isset( $get_data['stripe_disconnected'] ) ) {
		$account_name = ! empty( $get_data['account_name'] ) ? $get_data['account_name'] : false;

		// Disconnect Stripe Account.
		give_stripe_disconnect_account( $account_name );
	}
}

add_action( 'admin_init', 'give_stripe_disconnect_connect_stripe_account' );

/**
 * Show Stripe Account Used under donation details.
 *
 * @param  int  $donationId  Donation ID.
 *
 * @return void
 * @since 2.7.0
 *
 */
function giveStripeDisplayProcessedStripeAccount( $donationId ) {
	$paymentMethod = give_get_payment_gateway( $donationId );

	// Exit if donation is not processed with Stripe payment method.
	if ( ! Stripe::isDonationPaymentMethod( $paymentMethod ) ) {
		return;
	}

	$stripeAccounts = give_stripe_get_all_accounts();
	$accountId      = give_get_meta( $donationId, '_give_stripe_account_slug', true );
	$accountDetail  = isset( $stripeAccounts[ $accountId ] ) ? $stripeAccounts[ $accountId ] : [];
	$account        = 'connect' === $accountDetail['type'] ?
		"{$accountDetail['account_name']} ({$accountId})" :
		give_stripe_convert_slug_to_title( $accountId );
	?>
	<div class="give-donation-stripe-account-used give-admin-box-inside">
		<p>
			<strong><?php esc_html_e( 'Stripe Account:', 'give' ); ?></strong><br/>
			<?php echo $account; ?>
		</p>
	</div>
	<?php
}

add_action( 'give_view_donation_details_payment_meta_after', 'giveStripeDisplayProcessedStripeAccount' );
