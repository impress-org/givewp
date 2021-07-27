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
	public function __construct(  ) {

	}

	/**
	 * @unreleased
	 */
	private function setUpProperties(): void {
	}

	/**
	 * Render Stripe account manager setting field.
	 *
	 * @unreleased
	 *
	 * @param array $field
	 */
	public function handle( $field ): void {
		$this->setUpProperties();
		$classes = ! empty( $field['wrapper_class'] ) ? esc_attr( $field['wrapper_class'] ) : ''
		?>
		<div class="<?php echo $classes; ?>">

			<?php $this->getIntroductionSectionMarkup(); ?>
			<?php $this->getRadioButtons(); ?>

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
	private function getRadioButtons(): void {
		?>


			

		<?php
	}


}
