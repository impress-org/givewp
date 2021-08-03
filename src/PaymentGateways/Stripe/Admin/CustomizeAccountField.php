<?php

namespace Give\PaymentGateways\Stripe\Admin;

/**
 * Class CustomizeAccountField
 *
 * @package Give\PaymentGateways\Stripe\Admin
 * @unreleased
 */
class CustomizeAccountField {

	/**
	 * CustomizeAccountField constructor.
	 */
	public function __construct() {
	}

	/**
	 * Render
	 *
	 * @unreleased
	 *
	 * @param array  $field
	 * @param string $value
	 */
	public function handle( $field ) {


		$classes = ! empty( $field['wrapper_class'] ) ? esc_attr( $field['wrapper_class'] ) : ''
		?>
		<div class="<?php echo $classes; ?>">
			<?php $this->getIntroductionSectionMarkup(); ?>
			<?php $this->getRadioButtons( $field ); ?>
		</div>
		<?php
	}


	/**
	 * @unreleased
	 */
	private function getIntroductionSectionMarkup() {
		?>
		<div class="give-stripe-per-form-description">
			<h2><?php esc_html_e( 'How would you like to process donations made to this form?', 'give' ); ?></h2>
			<p class="give-stripe-subheading-description">
				<?php
				esc_html_e(
					'Do you want to customize the Stripe account for this donation form? The customize option allows you to modify the Stripe account this form processes payments through. By default, new donation forms will use the Global Default Stripe account.',
					'give'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * @unreleased
	 */
	private function getRadioButtons( $field ) {
		?>
		<div class="give-stripe-per-form-options">
			<div class="give-stripe-per-form-option-field give-stripe-boxshadow-option-wrap <?php echo 'single' === $value ? 'give-stripe-boxshadow-option-wrap__selected' : ''; ?>">
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
					<span class="stripe-per-form-radio-title"><?php esc_html_e( 'Global Default Account', 'give' ); ?></span>
					<span class="stripe-per-form-description"><?php esc_html_e( 'All donations are processed through the default account set in the Global Settings.', 'give' ); ?></span>

					<span class="stripe-per-form-global-setting">
						<span class="stripe-per-form-global-setting__title"><?php esc_html_e( 'Global account name:', 'give' ); ?></span>
						<span class="stripe-per-form-global-setting__name">
								<?php
								// Output Globally set account
								$globalAccount = give_stripe_get_default_account();
								echo $globalAccount['account_name'] ?? esc_html__( 'None set', 'give' ); ?>
						</span>

					</span>

				</label>
			</div>

			<div class="give-stripe-per-form-option-field give-stripe-boxshadow-option-wrap <?php echo 'multi' === $value ? 'give-stripe-boxshadow-option-wrap__selected' : ''; ?>">
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
					<span class="stripe-per-form-radio-title"><?php esc_html_e( 'Customize Stripe Account', 'give' ); ?></span>
					<span class="stripe-per-form-description"><?php esc_html_e( 'Donations are processed through the account selected below. Global Settings are overridden for this form.', 'give' ); ?></span>


				</label>
			</div>
		</div>
		<?php
	}
}
