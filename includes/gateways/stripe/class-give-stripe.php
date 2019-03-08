<?php
/**
 * Give - Stripe Core
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

if ( ! class_exists( 'Give_Stripe' ) ) {

	class Give_Stripe {

		/**
		 * Give_Stripe() constructor.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function __construct() {

			add_filter( 'give_payment_gateways', array( $this, 'register_gateway' ) );

			$this->includes();
		}

		/**
		 * This function is used to include the related Stripe core files.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function includes() {

			// Include admin files.
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/admin-actions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/admin-filters.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/class-give-stripe-admin-settings.php';

			// Include frontend files.
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/give-stripe-helpers.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/actions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-customer.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-payment-intent.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-gateway.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/payment-methods/class-give-stripe-card.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/give-stripe-scripts.php';

		}

		/**
		 * Register the payment methods supported by Stripe.
		 *
		 * @access public
		 * @since  2.5.0
		 *
		 * @param array $gateways List of registered gateways.
		 *
		 * @return array
		 */
		public function register_gateway( $gateways ) {

			$gateways['stripe'] = array(
				'admin_label'    => __( 'Stripe - Credit Card', 'give' ),
				'checkout_label' => __( 'Credit Card', 'give' ),
			);

			return $gateways;
		}

	}
}

new Give_Stripe();
