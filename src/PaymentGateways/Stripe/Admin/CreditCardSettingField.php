<?php

namespace Give\PaymentGateways\Stripe\Admin;

/**
 * Class CreditCardSettingField
 *
 * @package Give\PaymentGateways\Stripe\Admin
 * @unreleased
 */
class CreditCardSettingField {


	/**
	 * CreditCardSettingField constructor.
	 */
	public function __construct() {

	}

	/**
	 * Render Stripe account manager setting field.
	 *
	 * @unreleased
	 *
	 * @param array $field
	 * @param string $value
	 */
	public function handle( $field, $value ): void {

		$classes = ! empty( $field['wrapper_class'] ) ? esc_attr( $field['wrapper_class'] ) : ''
		?>
		<div class="<?php echo $classes; ?>">

			<?php $this->getIntroductionSectionMarkup(); ?>
			<?php $this->getRadioButtons( $field, $value ); ?>

		</div>
		<?php
	}


	/**
	 * @unreleased
	 */
	private function getIntroductionSectionMarkup(): void {
		?>
		<div id="give-stripe-credit-card-format-description">
			<h2><?php esc_html_e( 'Credit Card Fields Format', 'give' ); ?></h2>
			<p class="give-stripe-subheading-description">
				<?php esc_html_e( 'The credit card fieldset uses Stripe Elements for a secure method of accepting payment. Stripe offers two different types of credit card fields. A consolidated single field or a more traditional multi-fields format.',
					'give' ); ?>
			</p>
			<?php


			?>
		</div>
		<?php
	}

	/**
	 * @unreleased
	 */
	private function getRadioButtons( $field, $value ): void {

		$class = '';

		?>

		<div class="give-stripe-credit-card-options">

				<label
					class="give-stripe-boxshadow-option-wrap<?php echo $class; ?>"
				>
					<input name="stripe_cc_fields_format" value="single" type="radio" style="">
					<span class="stripe-cc-fields-radio-title">Single Field</span>
					<span class="stripe-cc-fields-radio-description">The single credit card format combines the Card number, expiration date, CVC, and zip / postal code (if enabled) fields  into one intuitive field.</span>
				</label>

				<label>
					<input name="stripe_cc_fields_format" value="multi" type="radio" style=""> Multi Field
				</label>

		</div>

		<p class="give-field-description">This option allows you to show single or multiple credit card fields on your donation forms.</p>


		<?php
	}


}
