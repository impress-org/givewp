<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;

class AdvancedCardFields {
	/**
	 * @since 2.9.0
	 *
	 * @var MerchantDetail
	 */
	private $merchantDetails;

	/**
	 * AdvancedCardFields constructor.
	 *
	 * @since 2.9.0
	 *
	 * @param MerchantDetail $merchantDetails
	 */
	public function __construct( MerchantDetail $merchantDetails ) {
		$this->merchantDetails = $merchantDetails;
	}

	/**
	 * PayPal commerce uses smart buttons to accept payment.
	 *
	 * @since 2.9.0
	 *
	 * @param int  $formId Donation Form ID.
	 * @param int  $args Donation Form Arguments.
	 * @param bool $echo Status to display or not.
	 *
	 * @access public
	 * @return string $form
	 *
	 */
	public function addCreditCardForm( $formId, $args, $echo = true ) {
		ob_start();
		$idPrefix = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : '';

		do_action( 'give_before_cc_fields', $formId ); ?>

		<fieldset id="give_cc_fields">
			<legend>
				<?php esc_attr_e( 'Credit Card Info', 'give' ); ?>
			</legend>

			<?php echo $this->getSslNotice(); ?>

			<div id="give-paypal-commerce-smart-buttons-wrap" class="form-row">
				<div id="smart-buttons-<?php echo esc_html( $idPrefix ); ?>"></div>
			</div>

			<?php
			if ( $this->merchantDetails->supportsCustomPayments ) {
				echo $this->getSeparator();
				echo $this->cardNumberField( $idPrefix );
				echo $this->cardCvcField( $idPrefix );
				echo $this->cardNameField();
				echo $this->cardExpirationField( $idPrefix );
			}
			?>

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
	 * @since 2.9.0
	 */
	private function removeBillingField() {
		remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );
	}


	/**
	 * Get ssl notice.
	 *
	 * @since 2.9.0
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

	/**
	 * Return card number field html.
	 *
	 * @since 2.9.0
	 *
	 * @param string $idPrefix
	 *
	 * @return string
	 */
	private function cardNumberField( $idPrefix ) {
		$label   = esc_html__( 'Card Number', 'give' );
		$tooltip = esc_attr__( 'The (typically) 16 digits on the front of your credit card.', 'give' );

		return <<<EOT
			<div id="give-card-number-wrap" class="form-row form-row-two-thirds form-row-responsive give-paypal-commerce-cc-field-wrap">
				<label for="give-card-number-field-$idPrefix" class="give-label">
					$label
					<span class="give-required-indicator">*</span>
					<span class="give-tooltip give-icon give-icon-question" data-tooltip="$tooltip"></span>
					<span class="card-type"></span>
				</label>
				<div id="give-card-number-field-$idPrefix" class="input empty give-paypal-commerce-cc-field give-paypal-commerce-card-number-field"></div>
			</div>
EOT;
	}

	/**
	 * Return card cvc field html.
	 *
	 * @since 2.9.0
	 *
	 * @param string $idPrefix
	 *
	 * @return string
	 */
	private function cardCvcField( $idPrefix ) {
		$label   = esc_html__( 'CVC', 'give' );
		$tooltip = esc_attr__( 'The 3 digit (back) or 4 digit (front) value on your card.', 'give' );

		return <<<EOT
			<div id="give-card-cvc-wrap" class="form-row form-row-one-third form-row-responsive give-paypal-commerce-cc-field-wrap">
				<label for="give-card-cvc-field-$idPrefix" class="give-label">
					$label
					<span class="give-required-indicator">*</span>
					<span class="give-tooltip give-icon give-icon-question" data-tooltip="$tooltip"></span>
				</label>
				<div id="give-card-cvc-field-$idPrefix" class="input empty give-paypal-commerce-cc-field give-paypal-commerce-card-cvc-field"></div>
			</div>
EOT;
	}

	/**
	 * Return card name field html.
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	private function cardNameField() {
		$label       = esc_html__( 'Cardholder Name', 'give' );
		$tooltip     = esc_attr__( 'The name of the credit card account holder.', 'give' );
		$placeholder = esc_attr__( 'Cardholder Name', 'give' );

		return <<<EOT
			<div id="give-card-name-wrap" class="form-row form-row-two-thirds form-row-responsive give-paypal-commerce-cc-field-wrap">
				<label for="card_name" class="give-label">
					$label
					<span class="give-required-indicator">*</span>
					<span class="give-tooltip give-icon give-icon-question" data-tooltip="$tooltip"></span>
				</label>
				<input
					type="text"
					autocomplete="off"
					id="card_name"
					name="card_name"
					class="card-name give-input required"
					placeholder="$placeholder"
				/>
			</div>
EOT;
	}

	/**
	 * Return card expiration  field html.
	 *
	 * @since 2.9.0
	 *
	 * @param string $idPrefix
	 *
	 * @return string
	 */
	private function cardExpirationField( $idPrefix ) {
		$label   = esc_html__( 'Expiration', 'give' );
		$tooltip = esc_attr__( 'The date your credit card expires, typically on the front of the card.', 'give' );

		return <<<EOT
			<div id="give-card-expiration-wrap" class="card-expiration form-row form-row-one-third form-row-responsive give-paypal-commerce-cc-field-wrap">
				<label for="give-card-expiration-field-$idPrefix" class="give-label">
					$label
					<span class="give-required-indicator">*</span>
					<span class="give-tooltip give-icon give-icon-question" data-tooltip="$tooltip"></span>
				</label>
				<div id="give-card-expiration-field-$idPrefix" class="input empty give-paypal-commerce-cc-field give-paypal-commerce-card-expiration-field"></div>
			</div>
EOT;
	}

	/**
	 *  Return separator html.
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	private function getSeparator() {
		$label = esc_html__( 'Or pay with card', 'give' );

		return <<<EOF
			<div class="separator-with-text">
				<div class="dashed-line"></div>
				<div class="label">$label</div>
				<div class="dashed-line"></div>
			</div>
EOF;
	}
}
