<?php
/**
 * Give - Stripe Sepa
 *
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Stripe_Sepa' ) ) {

	class Give_Stripe_Sepa {

		/**
		 * Give_Stripe_Sepa() constructor.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function __construct() {
			add_filter( 'give_payment_gateways', array( $this, 'register_gateway' ) );
			add_action( 'give_stripe_sepa_cc_form', array($this, 'sepa_cc_form'), 10, 3 );

			require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/payment-methods/class-give-stripe-sepa.php';
		}

		/**
		 * Register the payment method Stripe Sepa.
		 *
		 * @access public
		 *
		 * @param array $gateways List of registered gateways.
		 *
		 * @return array
		 */
		public function register_gateway( $gateways ) {

			$gateways['stripe_sepa'] = array(
				'admin_label'    => __( 'Stripe - SEPA Direct Debit', 'give' ),
				'checkout_label' => __( 'SEPA Direct Debit', 'give' ),
			);

			return $gateways;
		}

		/**
		 * Stripe uses it's own sepa form because the details are tokenized.
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
		public function sepa_cc_form($form_id, $args, $echo = true) {
			if ( give_stripe_is_checkout_enabled() ) {
				return false;
			}

			$id_prefix              = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : '';


			ob_start();
			?>
			<fieldset id="give_sepa_fields" class="give-do-validate">
				<legend>
					<?php esc_attr_e( 'SEPA Info', 'give' ); ?>
				</legend>

				<div id="give-stripe-sepa-wrap" class="form-row form-row-responsive give-stripe-sepa-field-wrap">
					<div>
						<label for="give-iban-field-<?php echo esc_html( $id_prefix ); ?>" class="give-label">
							<?php esc_attr_e( 'IBAN', 'give' ); ?>
							<span class="give-required-indicator">*</span>
							<span class="give-tooltip give-icon give-icon-question"
								data-tooltip="<?php esc_attr_e( 'The 16 to 34 digits on the front or back of your debit card.', 'give' ); ?>"></span>
							<span class="card-type"></span>
						</label>
						<div id="give-iban-field-<?php echo esc_html( $id_prefix ); ?>" class="input empty give-stripe-sepa-field"></div>
					</div>
				</div>
				<div id="error-message" role="alert"></div>

				<div class="give-stripe-sepa-mandate-text">
					<?php echo give_get_option( 'stripe_sepa_mandate', '' ); ?>
				</div>
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
	}
}

new Give_Stripe_Sepa();

