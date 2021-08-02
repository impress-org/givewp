<?php

namespace Give\PaymentGateways\Stripe;

use Give\PaymentGateways\Stripe\Admin\AccountManagerSettingField;

/**
 * Class DonationFormSettingPage
 * @package Give\PaymentGateways\Stripe
 *
 * @unreleased
 */
class DonationFormSettingPage {
	/**
	 * @unreleased
	 */
	public function boot() {
		add_filter( 'give_metabox_form_data_settings', [ $this, 'registerTab' ], 10, 2 );
	}

	/**
	 * @unreleased
	 *
	 * @param array $settings Settings List.
	 * @param $formId
	 *
	 * @return array
	 */
	function registerTab( $settings, $formId ) {
		if ( ! $this->canRegisterTab() ) {
			return $settings;
		}

		$settings['stripe_form_account_options'] = [
			'id'        => 'stripe_form_account_options',
			'title'     => esc_html__( 'Stripe Account', 'give' ),
			'icon-html' => '<i class="fab fa-stripe-s"></i>',
			'fields'    => $this->getMainSettingFields( $formId ),
		];

		return $settings;
	}

	/**
	 * @unreleased
	 *
	 * @param int $formId
	 *
	 * @return array[]
	 */
	private function getMainSettingFields( $formId ) {
		$formAccount = give_is_setting_enabled(
			give_get_meta(
				$formId,
				'give_stripe_per_form_accounts',
				true
			)
		);

		return [
			[
				'name'        => esc_html__( 'Account Options', 'give' ),
				'id'          => 'give_stripe_per_form_accounts',
				'type'        => 'radio_inline',
				'default'     => 'disabled',
				'options'     => [
					'disabled' => esc_html__( 'Use Global Default Stripe Account', 'give' ),
					'enabled'  => esc_html__( 'Customize Stripe Account', 'give' ),
				],
				'description' => esc_html__(
					'Do you want to customize the Stripe account for this donation form? The customize option allows you to modify the Stripe account this form processes payments through. By default, new donation forms will use the Global Default Stripe account.',
					'give'
				),
			],
			[
				'id'            => 'give_manage_accounts',
				'type'          => 'give_manage_accounts',
				'callback'      => [ give( AccountManagerSettingField::class ), 'handle' ],
				'wrapper_class' => $formAccount ? 'give-stripe-manage-account-options' : 'give-stripe-manage-account-options give-hidden',
			],
			[
				'name'  => 'donation_stripe_per_form_docs',
				'type'  => 'docs_link',
				'url'   => 'http://docs.givewp.com/stripe-free',
				'title' => esc_html__( 'Stripe Documentation', 'give' ),
			],
		];
	}

	/**
	 * @unreleased
	 * @return bool
	 */
	private function canRegisterTab() {
		return give_stripe_is_any_payment_method_active();
	}
}
