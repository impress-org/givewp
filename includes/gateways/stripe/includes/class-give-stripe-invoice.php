<?php
/**
 * Give - Stripe Core Gateway
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
 * Check for class Give_Stripe_Invoices exists.
 *
 * @since 2.5.0
 */
if ( ! class_exists( 'Give_Stripe_Invoice' ) ) {

	class Give_Stripe_Invoice {

		/**
		 * Retrieve Invoice/
		 *
		 * @param string $id Invoice ID.
		 *
		 * @return \Stripe\Invoice
		 */
		public function retrieve( $id ) {
			try {

				// Set Application Information.
				give_stripe_set_app_info();

				// Retrieve Invoice by ID.
				$invoice = \Stripe\Invoice::retrieve( $id );
			} catch( Exception $e ) {

				// Something went wrong outside of Stripe.
				give_record_gateway_error(
					__( 'Stripe - Invoices Error', 'give' ),
					sprintf(
						/* translators: %s Exception Message. */
						__( 'An error while retrieving invoice. Details: %s', 'give' ),
						$e->getMessage()
					)
				);
				give_set_error( 'Stripe Error', __( 'An error occurred while retrieving invoice. Please try again.', 'give' ) );
				give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode'] ) );

				 return false;
			}

			return $invoice;
		}
	}
}
