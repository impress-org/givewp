<?php

namespace Give\PaymentGateways\Stripe\Admin;

/**
 * Class CreditCardSettingField
 *
 * @package Give\PaymentGateways\Stripe\Admin
 * @since 2.13.0
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
	 * @since 2.13.0
	 *
	 * @param array  $field
	 * @param string $value
	 */
	public function handle( $field, $value ) {
		$classes = ! empty( $field['wrapper_class'] ) ? esc_attr( $field['wrapper_class'] ) : ''
		?>
		<div class="<?php echo $classes; ?>">
			<?php $this->getIntroductionSectionMarkup(); ?>
			<?php $this->getRadioButtons( $field, $value ); ?>
		</div>
		<?php
	}


	/**
	 * @since 2.13.0
	 */
	private function getIntroductionSectionMarkup() {
		?>
		<div class="give-stripe-credit-card-format-description">
			<h2><?php esc_html_e( 'Credit Card Fields Format', 'give' ); ?></h2>
			<p class="give-stripe-subheading-description">
				<?php
				esc_html_e(
					'The credit card fieldset uses Stripe Elements for a secure method of accepting payment. Stripe offers two different types of credit card fields. A consolidated single field or a more traditional multi-fields format.',
					'give'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * @since 2.13.0
	 */
	private function getRadioButtons( $field, $value ) {
		?>
		<div class="give-stripe-credit-card-options">
			<div class="give-stripe-cc-option-field give-stripe-boxshadow-option-wrap <?php echo 'single' === $value ? 'give-stripe-boxshadow-option-wrap__selected' : ''; ?>">
				<div class="give-stripe-account-default-checkmark">
					<svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M32.375 16.1875C32.375 25.1276 25.1276 32.375 16.1875 32.375C7.24737 32.375 0 25.1276 0 16.1875C0 7.24737 7.24737 0 16.1875 0C25.1276 0 32.375 7.24737 32.375 16.1875ZM14.3151 24.7586L26.3252 12.7486C26.733 12.3407 26.733 11.6795 26.3252 11.2717L24.8483 9.79474C24.4404 9.38686 23.7792 9.38686 23.3713 9.79474L13.5766 19.5894L9.00371 15.0165C8.59589 14.6086 7.93462 14.6086 7.52673 15.0165L6.04982 16.4934C5.642 16.9012 5.642 17.5625 6.04982 17.9703L12.8381 24.7586C13.246 25.1665 13.9072 25.1665 14.3151 24.7586Z" fill="#69B868" />
					</svg>
				</div>
				<label>
					<input
						name="stripe_cc_fields_format"
						value="single"
						type="radio"
						style=""
						<?php checked( 'single', $value ); ?>
					>
					<span class="stripe-cc-fields-radio-title"><?php esc_html_e( 'Single Field', 'give' ); ?></span>
					<span class="stripe-cc-fields-radio-description"><?php esc_html_e( 'The single credit card format combines the Card number, expiration date, CVC, and zip / postal code (if enabled) fields  into one intuitive field.', 'give' ); ?></span>
					<span class="stripe-cc-fields-example stripe-cc-fields-example__single">
						<span class="stripe-cc-fields-example-text"><?php esc_html_e( 'Example', 'give' ); ?>:</span>
						<img src="<?php echo GIVE_PLUGIN_URL . '/assets/dist/images/admin/stripe-single-cc-field.png'; ?>" width="340px" />
					</span>

				</label>
			</div>

			<div class="give-stripe-cc-option-field give-stripe-boxshadow-option-wrap <?php echo 'multi' === $value ? 'give-stripe-boxshadow-option-wrap__selected' : ''; ?>">
				<div class="give-stripe-account-default-checkmark">
					<svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M32.375 16.1875C32.375 25.1276 25.1276 32.375 16.1875 32.375C7.24737 32.375 0 25.1276 0 16.1875C0 7.24737 7.24737 0 16.1875 0C25.1276 0 32.375 7.24737 32.375 16.1875ZM14.3151 24.7586L26.3252 12.7486C26.733 12.3407 26.733 11.6795 26.3252 11.2717L24.8483 9.79474C24.4404 9.38686 23.7792 9.38686 23.3713 9.79474L13.5766 19.5894L9.00371 15.0165C8.59589 14.6086 7.93462 14.6086 7.52673 15.0165L6.04982 16.4934C5.642 16.9012 5.642 17.5625 6.04982 17.9703L12.8381 24.7586C13.246 25.1665 13.9072 25.1665 14.3151 24.7586Z" fill="#69B868" />
					</svg>
				</div>
				<label>
					<input
						name="stripe_cc_fields_format"
						value="multi"
						type="radio"
						style=""
						<?php checked( 'multi', $value ); ?>
					>
					<span class="stripe-cc-fields-radio-title"><?php esc_html_e( 'Multiple Fields', 'give' ); ?></span>
					<span class="stripe-cc-fields-radio-description"><?php esc_html_e( 'This is the more traditional credit card fieldset format that many are familiar with. All fields areseparate from one another.', 'give' ); ?></span>

					<span class="stripe-cc-fields-example stripe-cc-fields-example__multi ">
						<span class="stripe-cc-fields-example-text"><?php esc_html_e( 'Example', 'give' ); ?>:</span>
						<img src="<?php echo GIVE_PLUGIN_URL . '/assets/dist/images/admin/stripe-multiple-cc-fields.png'; ?>" width="340px" />
					</span>

				</label>
			</div>
		</div>
		<?php
	}
}
