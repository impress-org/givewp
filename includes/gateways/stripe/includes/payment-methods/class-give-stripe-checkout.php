<?php
/**
 * Give - Stripe Checkout
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for Give_Stripe_Checkout existence.
 *
 * @since 2.5.5
 */
if ( ! class_exists( 'Give_Stripe_Checkout' ) ) {

	/**
	 * Class Give_Stripe_Checkout.
	 *
	 * @since 2.5.5
	 */
	class Give_Stripe_Checkout extends Give_Stripe_Gateway {

		/**
		 * Checkout Session of Stripe.
		 *
		 * @since  2.5.5
		 * @access public
		 *
		 * @var $stripe_checkout_session
		 */
		public $stripe_checkout_session;

		/**
		 * Give_Stripe_Checkout constructor.
		 *
		 * @since  2.5.5
		 * @access public
		 */
		public function __construct() {

			$this->id = 'stripe_checkout';

			parent::__construct();

			// Create object for Stripe Checkout Session for usage.
			$this->stripe_checkout_session = new Give_Stripe_Checkout_Session();

			// Remove CC fieldset.
			add_action( 'give_stripe_checkout_cc_form', '__return_false' );

			// Load the `redirect_to_checkout` function only when `redirect` is set as checkout type.
			if ( 'redirect' === give_stripe_get_checkout_type() ) {
				add_action( 'wp_footer', array( $this, 'redirect_to_checkout' ), 99999 );
			}

		}

		/**
		 * This function will be used for donation processing.
		 *
		 * @param array $donation_data List of donation data.
		 *
		 * @since  2.5.5
		 * @access public
		 *
		 * @return void
		 */
		public function process_payment( $donation_data ) {

			// Bailout, if the current gateway and the posted gateway mismatched.
			if ( $this->id !== $donation_data['post_data']['give-gateway'] ) {
				return;
			}

			// Make sure we don't have any left over errors present.
			give_clear_errors();

			// Any errors?
			$errors = give_get_errors();

			// No errors, proceed.
			if ( ! $errors ) {

				$form_id          = ! empty( $donation_data['post_data']['give-form-id'] ) ? intval( $donation_data['post_data']['give-form-id'] ) : 0;
				$price_id         = ! empty( $donation_data['post_data']['give-price-id'] ) ? $donation_data['post_data']['give-price-id'] : 0;
				$donor_email      = ! empty( $donation_data['post_data']['give_email'] ) ? $donation_data['post_data']['give_email'] : 0;
				$payment_method   = ! empty( $donation_data['post_data']['give_stripe_payment_method'] ) ? $donation_data['post_data']['give_stripe_payment_method'] : 0;
				$donation_summary = give_payment_gateway_donation_summary( $donation_data, false );

				// Get an existing Stripe customer or create a new Stripe Customer and attach the source to customer.
				$give_stripe_customer = new Give_Stripe_Customer( $donor_email, $payment_method );
				$stripe_customer_id   = $give_stripe_customer->get_id();
				$payment_method       = ! empty( $give_stripe_customer->attached_payment_method ) ?
					$give_stripe_customer->attached_payment_method->id :
					$payment_method;

				// We have a Stripe customer, charge them.
				if ( $stripe_customer_id ) {

					// Setup the payment details.
					$payment_data = array(
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
					);

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
						return;
					}

					// Assign required data to array of donation data for future reference.
					$donation_data['donation_id'] = $donation_id;
					$donation_data['description'] = $donation_summary;
					$donation_data['customer_id'] = $stripe_customer_id;
					$donation_data['source_id']   = $payment_method;

					// Save Stripe Customer ID to Donation note, Donor and Donation for future reference.
					give_insert_payment_note( $donation_id, 'Stripe Customer ID: ' . $stripe_customer_id );
					$this->save_stripe_customer_id( $stripe_customer_id, $donation_id );
					give_update_meta( $donation_id, '_give_stripe_customer_id', $stripe_customer_id );

					if ( 'modal' === give_stripe_get_checkout_type() ) {
						$this->process_legacy_checkout( $donation_id, $donation_data );
					} elseif ( 'redirect' === give_stripe_get_checkout_type() ) {
						$this->process_checkout( $donation_id, $donation_data );
					} else {
						give_record_gateway_error(
							__( 'Invalid Checkout Error', 'give' ),
							sprintf(
								/* translators: %s Donation Data */
								__( 'Invalid Checkout type passed to process the donation. Details: %s', 'give' ),
								wp_json_encode( $donation_data )
							)
						);
						give_set_error( 'stripe_error', __( 'The Stripe Gateway returned an error while processing the donation.', 'give' ) );
						give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode'] ) );
						return;
					}

					// Don't execute code further.
					give_die();
				}
			}

		}

		/**
		 * This function is used to process donations via legacy Stripe Checkout which will be deprecated soon.
		 *
		 * @param int   $donation_id   Donation ID.
		 * @param array $donation_data List of submitted data for donation processing.
		 *
		 * @since  2.5.5
		 * @access public
		 *
		 * @return void
		 */
		public function process_legacy_checkout( $donation_id, $donation_data ) {

			$stripe_customer_id = ! empty( $donation_data['customer_id'] ) ? $donation_data['customer_id'] : '';

			// Process charge w/ support for preapproval.
			$charge = $this->process_charge( $donation_data, $stripe_customer_id );

			// Verify the Stripe payment.
			$this->verify_payment( $donation_id, $stripe_customer_id, $charge );

		}

		/**
		 * Process One Time Charge.
		 *
		 * @param array  $donation_data      List of donation data.
		 * @param string $stripe_customer_id Customer ID.
		 *
		 * @return bool|\Stripe\Charge
		 */
		public function process_charge( $donation_data, $stripe_customer_id ) {

			$form_id     = ! empty( $donation_data['post_data']['give-form-id'] ) ? intval( $donation_data['post_data']['give-form-id'] ) : 0;
			$donation_id = ! empty( $donation_data['donation_id'] ) ? intval( $donation_data['donation_id'] ) : 0;
			$description = ! empty( $donation_data['description'] ) ? $donation_data['description'] : false;

			// Format the donation amount as required by Stripe.
			$amount = $this->format_amount( $donation_data['price'] );

			// Prepare charge arguments.
			$charge_args = array(
				'amount'               => $amount,
				'customer'             => $stripe_customer_id,
				'currency'             => give_get_currency( $form_id ),
				'description'          => html_entity_decode( $description, ENT_COMPAT, 'UTF-8' ),
				'statement_descriptor' => give_stripe_get_statement_descriptor( $donation_data ),
				'metadata'             => $this->prepare_metadata( $donation_id ),
			);

			// Process the charge.
			$charge = $this->create_charge( $donation_id, $charge_args );

			// Return charge if set.
			if ( isset( $charge ) ) {
				return $charge;
			} else {
				return false;
			}
		}

		/**
		 * This function is used to process donations via Stripe Checkout 2.0.
		 *
		 * @param int   $donation_id Donation ID.
		 * @param array $data        List of submitted data for donation processing.
		 *
		 * @since  2.5.5
		 * @access public
		 *
		 * @return void
		 */
		public function process_checkout( $donation_id, $data ) {

			// Define essential variables.
			$form_id          = ! empty( $data['post_data']['give-form-id'] ) ? intval( $data['post_data']['give-form-id'] ) : 0;
			$form_name        = ! empty( $data['post_data']['give-form-title'] ) ? $data['post_data']['give-form-title'] : false;
			$donation_summary = ! empty( $data['description'] ) ? $data['description'] : '';
			$donation_id      = ! empty( $data['donation_id'] ) ? intval( $data['donation_id'] ) : 0;
			$redirect_to_url  = ! empty( $data['post_data']['give-current-url'] ) ? $data['post_data']['give-current-url'] : site_url();

			// Format the donation amount as required by Stripe.
			$amount = give_stripe_format_amount( $data['price'] );

			// Fetch whether the billing address collection is enabled in admin settings or not.
			$is_billing_enabled = give_is_setting_enabled( give_get_option( 'stripe_collect_billing' ) );

			$session_args = array(
				'customer'                   => $data['customer_id'],
				'client_reference_id'        => $data['purchase_key'],
				'payment_method_types'       => array( 'card' ),
				'billing_address_collection' => $is_billing_enabled ? 'required' : 'auto',
				'mode'                       => 'payment',
				'line_items'                 => array(
					array(
						'name'        => $form_name,
						'description' => $data['description'],
						'amount'      => $amount,
						'currency'    => give_get_currency( $form_id ),
						'quantity'    => 1,
					),
				),
				'payment_intent_data'        => array(
					'capture_method'       => 'automatic',
					'description'          => $donation_summary,
					'metadata'             => $this->prepare_metadata( $donation_id ),
					'statement_descriptor' => give_stripe_get_statement_descriptor(),
				),
				'submit_type'                => 'donate',
				'success_url'                => give_get_success_page_uri(),
				'cancel_url'                 => give_get_failed_transaction_uri(),
				'locale'                     => give_stripe_get_preferred_locale(),
			);

			// If featured image exists, then add it to checkout session.
			if ( ! empty( get_the_post_thumbnail( $form_id ) ) ) {
				$session_args['line_items'][0]['images'] = array( get_the_post_thumbnail_url( $form_id ) );
			}

			// Create Checkout Session.
			$session    = $this->stripe_checkout_session->create( $session_args );
			$session_id = ! empty( $session->id ) ? $session->id : false;

			// Set Checkout Session ID as Transaction ID.
			if ( ! empty( $session_id ) ) {
				give_insert_payment_note( $donation_id, 'Stripe Checkout Session ID: ' . $session_id );
				give_set_payment_transaction_id( $donation_id, $session_id );
			}

			// Save donation summary to donation.
			give_update_meta( $donation_id, '_give_stripe_donation_summary', $donation_summary );

			// Redirect to show loading area to trigger redirectToCheckout client side.
			wp_safe_redirect(
				add_query_arg(
					array(
						'action'  => 'checkout_processing',
						'session' => $session_id,
					),
					$redirect_to_url
				)
			);

			// Don't execute code further.
			give_die();
		}

		/**
		 * Redirect to Checkout.
		 *
		 * @since  2.5.5
		 * @access public
		 *
		 * @return void
		 */
		public function redirect_to_checkout() {

			$get_data          = give_clean( $_GET );
			$publishable_key   = give_stripe_get_publishable_key();
			$session_id        = ! empty( $get_data['session'] ) ? $get_data['session'] : false;
			$action            = ! empty( $get_data['action'] ) ? $get_data['action'] : false;
			$stripe_account_id = give_get_option( 'give_stripe_user_id' );

			// Bailout, if action is not checkout processing.
			if ( 'checkout_processing' !== $action ) {
				return;
			}

			// Bailout, if session id doesn't exists.
			if ( ! $session_id ) {
				return;
			}
			?>
			<div id="give-stripe-checkout-processing"></div>
			<script>
				// Show Processing Donation Overlay.
				const processingHtml = document.querySelector( '#give-stripe-checkout-processing');

				processingHtml.setAttribute( 'class', 'stripe-checkout-process' );
				processingHtml.style.background = '#FFFFFF';
				processingHtml.style.opacity = '0.9';
				processingHtml.style.position = 'fixed';
				processingHtml.style.top = '0';
				processingHtml.style.left = '0';
				processingHtml.style.bottom = '0';
				processingHtml.style.right = '0';
				processingHtml.style.zIndex = '2147483646';
				processingHtml.innerHTML = '<div class="give-stripe-checkout-processing-container" style="position: absolute;top: 50%;left: 50%;width: 300px; margin-left: -150px; text-align:center;"><div style="display:inline-block;"><span class="give-loading-animation" style="color: #333;height:26px;width:26px;font-size:26px; margin:0; "></span><span style="color:#000; font-size: 26px; margin:0 0 0 10px;">' + give_stripe_vars.checkout_processing_text + '</span></div></div>';

				window.addEventListener('load', function() {
					const stripe = Stripe( '<?php echo $publishable_key; ?>', {
						'stripeAccount': '<?php echo $stripe_account_id; ?>'
					} );

					// Redirect donor to Checkout page.
					stripe.redirectToCheckout({
						// Make the id field from the Checkout Session creation API response
						// available to this file, so you can provide it as parameter here
						// instead of the {{CHECKOUT_SESSION_ID}} placeholder.
						sessionId: '<?php echo $session_id; ?>'
					}).then( ( result ) => {
						console.log(result);
						// If `redirectToCheckout` fails due to a browser or network
						// error, display the localized error message to your customer
						// using `result.error.message`.
					});
				})
			</script>
			<?php
		}
	}
}

new Give_Stripe_Checkout();
