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

	/**
	 * Class Give_Stripe
	 */
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
			add_action( 'admin_notices', array( $this, 'display_processing_fees_notice' ) );

			/**
			 * Using hardcoded constant for backward compatibility of Give 2.5.0 with Recurring 1.8.13 when Stripe Premium is not active.
			 *
			 * This code will handle extreme rare scenario.
			 *
			 * @since 2.5.0
			 *
			 * @todo Remove this constant declaration after 2-3 Give core minor releases.
			 */
			if ( ! defined( 'GIVE_STRIPE_BASENAME' ) ) {
				define( 'GIVE_STRIPE_BASENAME', 'give-stripe/give-stripe.php' );
			}

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

			// Include files which are necessary to load in admin but not in context of `is_admin`.
			$this->include_admin_files();

			// Load files which are necessary for front as well as admin end.
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/give-stripe-helpers.php';

			// Bailout, if any of the Stripe gateways are not active.
			if ( ! give_stripe_is_any_payment_method_active() ) {

				// Hardcoded recurring plugin basename to show notice even when recurring addon is deactivated.
				$recurring_plugin_basename = 'give-recurring/give-recurring.php';
				$recurring_file_path       = WP_CONTENT_DIR . '/plugins/' . $recurring_plugin_basename;

				// If recurring donations add-on exists.
				if ( file_exists( $recurring_file_path ) ) {

					// If `get_plugin_data` fn not exists then include the file.
					if ( ! function_exists( 'get_plugin_data' ) ) {
						require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
					}

					$recurring_plugin_data = get_plugin_data($recurring_file_path);

					// Avoid fatal error for smooth update for customers.
					if (
						isset( $recurring_plugin_data['Version'] ) &&
						version_compare( '1.9.3', $recurring_plugin_data['Version'], '>=' )
					) {
						add_action('admin_notices', function() {

							// Register error notice.
							Give()->notices->register_notice(
								array(
									'id'          => 'give-recurring-fatal-error',
									'type'        => 'error',
									'description' => sprintf(
										__( '<strong>Activation Error:</strong> Please update the Recurring Donations add-on to version <strong>1.9.4+</strong> in order to be compatible with GiveWP <strong>2.5.5+</strong>. If you are experiencing this issue please rollback GiveWP to 2.5.4 or below using the <a href="%s" target="_blank">WP Rollback</a> plugin and <a href="%s" target="_blank">contact support</a> for prompt assistance.', 'give'),
										'https://wordpress.org/plugins/wp-rollback/',
										'https://givewp.com/support/'
									),
									'show'        => true,
								)
							);
						});

						// Deactivate recurring addon to avoid fatal error.
						deactivate_plugins( $recurring_plugin_basename );
						if ( isset( $_GET['activate'] ) ) {
							unset( $_GET['activate'] );
						}
					}
				}

				return;
			}

			// Load Stripe SDK.
			give_stripe_load_stripe_sdk();

			// Include frontend files.
			$this->include_frontend_files();
		}

		/**
		 * This function is used to display admin notices about Stripe Application Fees.
		 *
		 * @since 2.5.9
		 *
		 * @return void
		 */
		public function display_processing_fees_notice() {

			// Hardcoded stripe plugin name.
			$stripe_plugin_name     = 'give-stripe';
			$stripe_plugin_basename = "{$stripe_plugin_name}/{$stripe_plugin_name}.php";
			$stripe_file_path       = WP_CONTENT_DIR . '/plugins/' . $stripe_plugin_basename;

			$description = false;
			$license     = Give_License::get_license_by_plugin_dirname( $stripe_plugin_name );

			// Proceed, if Stripe Premium add-on exists.
			if ( file_exists( $stripe_file_path ) ) {

				if ( defined( 'GIVE_STRIPE_VERSION' ) ) {
					// Stripe Premium is installed and active.
					if (
						empty( $license ) ||
						(
							! empty( $license['license'] ) &&
							'valid' !== $license['license']
						)
					) {

						// Show notice when Stripe Premium is active and license is not valid.
						$description = sprintf(
							__( '<strong>Action Needed: </strong>Please authenticate your license key to avoid 2%% application fees applied on every donation with Stripe payment gateway. If you are experiencing any issue, please <a href="%1$s" target="_blank">contact support</a> for prompt assistance.', 'give' ),
							esc_url( 'https://givewp.com/support/' )
						);
					}

				} else {

					// Show notice to activate Stripe Premium add-on and authenticate the license.
					$description = sprintf(
						__( '<strong>Action Needed: </strong>Please activate the Stripe Premium add-on and authenticate your license to avoid 2%% application fees applied on every donation. If you are experiencing any issue, please <a href="%1$s" target="_blank">contact support</a> for prompt assistance.', 'give' ),
						esc_url( 'https://givewp.com/support/' )
					);

					// Stripe Premium is installed and not active.
					if ( ! empty( $license['license'] ) && 'valid' === $license['license'] ) {

						// Show notice to activate Stripe Premium add-on.
						$description = sprintf(
							__( '<strong>Action Needed: </strong>Please activate the Stripe Premium add-on to avoid 2%% application fees applied on every donation. If you are experiencing any issue, please <a href="%1$s" target="_blank">contact support</a> for prompt assistance.', 'give' ),
							esc_url( 'https://givewp.com/support/' )
						);
					}
				}

				// Register Notice for Stripe Premium Application Fees.
				if ( ! empty( $description ) ) {
					Give()->notices->register_notice(
						array(
							'id'          => 'give-stripe-premium-application-fee-errors',
							'type'        => 'error',
							'description' => $description,
							'show'        => true,
						)
					);
				}
			}
		}

		/**
		 * This function is used to include admin files.
		 *
		 * @since  2.6.0
		 * @access public
		 *
		 * @return void
		 */
		public function include_admin_files() {
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/admin-helpers.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/admin-actions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/admin-filters.php';

			// Load these files when accessed from admin.
			if ( is_admin() ) {
				require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/class-give-stripe-admin-settings.php';
				require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/class-give-stripe-logs.php';
			}
		}

		/**
		 * This function will be used to load frontend files.
		 *
		 * @since  2.6.0
		 * @access public
		 *
		 * @return void
		 */
		public function include_frontend_files() {

			// General.
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/actions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/give-stripe-scripts.php';

			// Classes.
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-logger.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-invoice.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-customer.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-payment-intent.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-payment-method.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-checkout-session.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-gateway.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/class-give-stripe-webhooks.php';

			// Payment Methods.
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/payment-methods/class-give-stripe-card.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/payment-methods/class-give-stripe-checkout.php';

			// Deprecations.
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/deprecated/deprecated-functions.php';
			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/deprecated/deprecated-filters.php';
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

			// Stripe - On page credit card.
			$gateways['stripe'] = array(
				'admin_label'    => __( 'Stripe - Credit Card', 'give' ),
				'checkout_label' => __( 'Credit Card', 'give' ),
			);

			// Stripe - Off page credit card (also known as Checkout).
			$gateways['stripe_checkout'] = array(
				'admin_label'    => __( 'Stripe - Checkout', 'give' ),
				'checkout_label' => __( 'Credit Card', 'give' ),
			);

			return $gateways;
		}
	}
}

new Give_Stripe();
