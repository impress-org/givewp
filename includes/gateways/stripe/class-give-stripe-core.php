<?php
/**
 * Give - Stripe Core
 *
 * @since 2.5.0
 *
 * @package Give
 * @subpackage Stripe Core
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Stripe_Core' ) ) {

	class Give_Stripe_Core {

		/**
		 * Give_Stripe_Core() constructor.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function __construct() {

			$this->includes();

			add_filter( 'give_payment_gateways', array( $this, 'register_gateway' ) );
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

			// Include frontend files.
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-customer.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-gateway.php';

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
