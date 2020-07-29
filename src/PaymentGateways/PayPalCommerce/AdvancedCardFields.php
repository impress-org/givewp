<?php

namespace Give\PaymentGateways\PayPalCommerce;

class AdvancedCardFields {
	/**
	 * Bootstrap class
	 *
	 * @since 2.8.0
	 */
	public function boot() {
		$gatewayId = give( PayPalCommerce::class )->getId();
		add_action( "give_{$gatewayId}_cc_form", [ $this, 'addCreditCardForm' ], 10, 3 );

		return $this;
	}

	/**
	 * PayPal commerce uses smart buttons to accept payment.
	 *
	 * @since 2.8.0
	 *
	 * @param  int  $formId  Donation Form ID.
	 * @param  int  $args  Donation Form Arguments.
	 * @param  bool  $echo  Status to display or not.
	 *
	 * @access public
	 * @return string $form
	 *
	 */
	public function addCreditCardForm( $formId, $args, $echo = true ) {
		ob_start();
		$id_prefix = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : '';

		do_action( 'give_before_cc_fields', $formId ); ?>

		<fieldset id="give_cc_fields" class="give-do-validate">
			<legend>
				<?php esc_attr_e( 'Credit Card Info', 'give' ); ?>
			</legend>

			<?php echo $this->getSslNotice(); ?>

			<div id="give-paypal-smart-buttons-wrap" class="form-row">
				<div id="give-paypal-smart-buttons-field-<?php echo esc_html( $id_prefix ); ?>"></div>
			</div>

		</fieldset>
		<?php
		$this->removeBillingField();

		do_action( 'give_after_cc_fields', $formId, $args );

		$form = ob_get_clean();

		if ( false !== $echo ) {
			echo $form;
		}

		return $form;
	}

	/**
	 * Remove Address Fields if user has option enabled.
	 *
	 * @since 2.8.0
	 */
	private function removeBillingField() {
		$billing_fields_enabled = give_get_option( 'stripe_collect_billing' );
		if ( ! $billing_fields_enabled ) {
			remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );
		}
	}


	/**
	 * Get ssl notice.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	private function getSslNotice() {
		if ( is_ssl() ) {
			return '';
		}

		return sprintf(
			'<div id="give_secure_site_wrapper"><span class="give-icon padlock"></span><span>%1$s</span></div>',
			esc_html__( 'This is a secure SSL encrypted payment.', 'give' )
		);
	}
}
