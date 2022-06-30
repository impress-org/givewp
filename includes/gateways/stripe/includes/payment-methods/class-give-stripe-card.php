<?php
/**
 * Give - Stripe Card Payments
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

use Give\PaymentGateways\Gateways\Stripe\Traits\CreditCardForm;

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
	class Give_Stripe_Card {
        use CreditCardForm;


		/**
         * @since 2.21.0 recover method for legacy give-recurring usage.
         * @since  1.0
         *
		 * Stripe uses it's own credit card form because the card details are tokenized.
		 *
		 * We don't want the name attributes to be present on the fields in order to
		 * prevent them from getting posted to the server.
		 *
		 * @param int   $form_id Donation Form ID.
        *  @param array $args    Donation Form Arguments.
		 *
		 * @access public
		 *
		 * @return string $form
		 */
		public function addCreditCardForm( $form_id, $args, $echo = true ) {
            $form = $this->getCreditCardFormHTML($form_id, $args);

            if ( false !== $echo ) {
				echo $form;
			}

			return $form;
		}
	}
}
return new Give_Stripe_Card();
