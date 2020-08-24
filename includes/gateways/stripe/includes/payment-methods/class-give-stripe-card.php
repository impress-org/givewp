<?php
/**
 * Give - Stripe Card Payments
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

use Give\Helpers\Gateways\Stripe;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for Give_Stripe_Card existence.
 *
 * @since 2.5.0
 */
if ( ! class_exists( 'Give_Stripe_Card' ) ) {

	/**
	 * Class Give_Stripe_Card.
	 *
	 * @since 2.5.0
	 */
	class Give_Stripe_Card extends Give_Stripe_Gateway {

		/**
		 * Give_Stripe_Card constructor.
		 *
		 * @since  2.5.0
		 * @access public
		 */
		public function __construct() {

			$this->id = 'stripe';

			// Setup Error Messages.
			$this->errorMessages['accountConfiguredNoSsl']    = esc_html__( 'Credit Card fields are disabled because your site is not running securely over HTTPS.', 'give' );
			$this->errorMessages['accountNotConfiguredNoSsl'] = esc_html__( 'Credit Card fields are disabled because Stripe is not connected and your site is not running securely over HTTPS.', 'give' );
			$this->errorMessages['accountNotConfigured']      = esc_html__( 'Credit Card fields are disabled. Please connect and configure your Stripe account to accept donations.', 'give' );

			add_action( 'give_stripe_cc_form', [ $this, 'addCreditCardForm' ], 10, 3 );

			parent::__construct();
		}


		/**
		 * Stripe uses it's own credit card form because the card details are tokenized.
		 *
		 * We don't want the name attributes to be present on the fields in order to
		 * prevent them from getting posted to the server.
		 *
		 * @param int  $form_id Donation Form ID.
		 * @param int  $args    Donation Form Arguments.
		 * @param bool $echo    Status to display or not.
		 *
		 * @access public
		 * @since  1.0
		 *
		 * @return string $form
		 */
		public function addCreditCardForm( $form_id, $args, $echo = true ) {

			ob_start();
			$idPrefix = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : '';

			do_action( 'give_before_cc_fields', $form_id ); ?>

			<fieldset id="give_cc_fields" class="give-do-validate">
				<legend>
					<?php esc_attr_e( 'Credit Card Info', 'give' ); ?>
				</legend>

				<?php
				if ( is_ssl() ) {
					?>
					<div id="give_secure_site_wrapper">
						<span class="give-icon padlock"></span>
						<span>
					<?php esc_attr_e( 'This is a secure SSL encrypted payment.', 'give' ); ?>
				</span>
					</div>
					<?php
				}

				if ( $this->canShowFields() ) {
					// Show Credit Card Fields.
					echo Stripe::showCreditCardFields( $idPrefix );

					/**
					 * This action hook is used to display content after the Credit Card expiration field.
					 *
					 * Note: Kept this hook as it is.
					 *
					 * @since 2.5.0
					 *
					 * @param int   $form_id Donation Form ID.
					 * @param array $args    List of additional arguments.
					 */
					do_action( 'give_after_cc_expiration', $form_id, $args );

					/**
					 * This action hook is used to display content after the Credit Card expiration field.
					 *
					 * @since 2.5.0
					 *
					 * @param int   $form_id Donation Form ID.
					 * @param array $args    List of additional arguments.
					 */
					do_action( 'give_stripe_after_cc_expiration', $form_id, $args );
				}
				?>
			</fieldset>
			<?php
			// Remove Address Fields if user has option enabled.
			$billing_fields_enabled = give_get_option( 'stripe_collect_billing' );
			if ( ! $billing_fields_enabled ) {
				remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );
			}

			do_action( 'give_after_cc_fields', $form_id, $args );

			$form = ob_get_clean();

			if ( false !== $echo ) {
				echo $form;
			}

			return $form;
		}

		/**
		 * This function will be used for donation processing.
		 *
		 * @param array $donation_data List of donation data.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function process_payment( $donation_data ) {

			// Bailout, if the current gateway and the posted gateway mismatched.
			if ( 'stripe' !== $donation_data['post_data']['give-gateway'] ) {
				return;
			}

			// Make sure we don't have any left over errors present.
			give_clear_errors();

			$payment_method_id = ! empty( $donation_data['post_data']['give_stripe_payment_method'] )
				? $donation_data['post_data']['give_stripe_payment_method']
				: false;

			// Send donor back to checkout page, if no payment method id exists.
			if ( empty( $payment_method_id ) ) {
				give_record_gateway_error(
					__( 'Stripe Payment Method Error', 'give' ),
					__( 'The payment method failed to generate during a donation. This is usually caused by a JavaScript error on the page preventing Stripe’s JavaScript from running correctly. Reach out to GiveWP support for assistance.', 'give' )
				);
				give_set_error( 'no-payment-method-id', __( 'Unable to generate Payment Method ID. Please contact a site administrator for assistance.', 'give' ) );
				give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode'] ) );
			}

			// Any errors?
			$errors = give_get_errors();

			// No errors, proceed.
			if ( ! $errors ) {

				$form_id          = ! empty( $donation_data['post_data']['give-form-id'] ) ? intval( $donation_data['post_data']['give-form-id'] ) : 0;
				$price_id         = ! empty( $donation_data['post_data']['give-price-id'] ) ? $donation_data['post_data']['give-price-id'] : 0;
				$donor_email      = ! empty( $donation_data['post_data']['give_email'] ) ? $donation_data['post_data']['give_email'] : 0;
				$donation_summary = give_payment_gateway_donation_summary( $donation_data, false );

				// Get an existing Stripe customer or create a new Stripe Customer and attach the source to customer.
				$give_stripe_customer = new Give_Stripe_Customer( $donor_email, $payment_method_id );
				$stripe_customer      = $give_stripe_customer->customer_data;
				$stripe_customer_id   = $give_stripe_customer->get_id();

				// We have a Stripe customer, charge them.
				if ( $stripe_customer_id ) {

					// Proceed to get stripe source/payment method details.
					$payment_method    = $give_stripe_customer->attached_payment_method;
					$payment_method_id = $payment_method->id;

					// Setup the payment details.
					$payment_data = [
						'price'           => $donation_data['price'],
						'give_form_title' => $donation_data['post_data']['give-form-title'],
						'give_form_id'    => $form_id,
						'give_price_id'   => $price_id,
						'date'            => $donation_data['date'],
						'user_email'      => $donation_data['user_email'],
						'purchase_key'    => $donation_data['purchase_key'],
						'currency'        => give_get_currency( $form_id ),
						'user_info'       => $donation_data['user_info'],
						'status'          => 'pending',
						'gateway'         => $this->id,
					];

					// Record the pending payment in Give.
					$donation_id = give_insert_payment( $payment_data );

					// Return error, if donation id doesn't exists.
					if ( ! $donation_id ) {
						give_record_gateway_error(
							__( 'Donation creating error', 'give' ),
							sprintf(
								/* translators: %s Donation Data */
								__( 'Unable to create a pending donation. Details: %s', 'give' ),
								wp_json_encode( $donation_data )
							)
						);
						give_set_error( 'stripe_error', __( 'The Stripe Gateway returned an error while creating a pending donation.', 'give' ) );
						give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode'] ) );
						return false;
					}

					// Assign required data to array of donation data for future reference.
					$donation_data['donation_id'] = $donation_id;
					$donation_data['description'] = $donation_summary;
					$donation_data['source_id']   = $payment_method_id;

					// Save Stripe Customer ID to Donation note, Donor and Donation for future reference.
					give_insert_payment_note( $donation_id, 'Stripe Customer ID: ' . $stripe_customer_id );
					$this->save_stripe_customer_id( $stripe_customer_id, $donation_id );
					give_update_meta( $donation_id, '_give_stripe_customer_id', $stripe_customer_id );

					// Save Source ID to donation note and DB.
					give_insert_payment_note( $donation_id, 'Stripe Source/Payment Method ID: ' . $payment_method_id );
					give_update_meta( $donation_id, '_give_stripe_source_id', $payment_method_id );

					// Save donation summary to donation.
					give_update_meta( $donation_id, '_give_stripe_donation_summary', $donation_summary );

					/**
					 * This filter hook is used to update the payment intent arguments.
					 *
					 * @since 2.5.0
					 */
					$intent_args = apply_filters(
						'give_stripe_create_intent_args',
						[
							'amount'               => $this->format_amount( $donation_data['price'] ),
							'currency'             => give_get_currency( $form_id ),
							'payment_method_types' => [ 'card' ],
							'statement_descriptor' => give_stripe_get_statement_descriptor(),
							'description'          => give_payment_gateway_donation_summary( $donation_data ),
							'metadata'             => $this->prepare_metadata( $donation_id, $donation_data ),
							'customer'             => $stripe_customer_id,
							'payment_method'       => $payment_method_id,
							'confirm'              => true,
							'return_url'           => give_get_success_page_uri(),
						]
					);

					// Send Stripe Receipt emails when enabled.
					if ( give_is_setting_enabled( give_get_option( 'stripe_receipt_emails' ) ) ) {
						$intent_args['receipt_email'] = $donation_data['user_email'];
					}

					$intent = $this->payment_intent->create( $intent_args );

					// Save Payment Intent Client Secret to donation note and DB.
					give_insert_payment_note( $donation_id, 'Stripe Payment Intent Client Secret: ' . $intent->client_secret );
					give_update_meta( $donation_id, '_give_stripe_payment_intent_client_secret', $intent->client_secret );

					// Set Payment Intent ID as transaction ID for the donation.
					give_set_payment_transaction_id( $donation_id, $intent->id );
					give_insert_payment_note( $donation_id, 'Stripe Charge/Payment Intent ID: ' . $intent->id );

					// Process additional steps for SCA or 3D secure.
					give_stripe_process_additional_authentication( $donation_id, $intent );

					if ( ! empty( $intent->status ) && 'succeeded' === $intent->status ) {
						// Process to success page, only if intent is successful.
						give_send_to_success_page();
					} else {
						// Show error message instead of confirmation page.
						give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode'] ) );
					}
				} else {

					// No customer, failed.
					give_record_gateway_error(
						esc_html__( 'Stripe Customer Creation Failed', 'give' ),
						sprintf(
							/* translators: %s Donation Data */
							esc_html__( 'Unable to get Stripe Customer ID while processing donation. Details: %s', 'give' ),
							wp_json_encode( $donation_data )
						)
					);
					give_set_error( 'stripe_error', esc_html__( 'The Stripe Gateway returned an error while processing the donation.', 'give' ) );
					give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode'] ) );

				} // End if().
			} else {
				give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode'] ) );
			} // End if().
		}
	}
}
return new Give_Stripe_Card();
