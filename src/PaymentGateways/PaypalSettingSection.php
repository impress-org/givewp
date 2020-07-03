<?php
namespace  Give\PaymentGateways;

use Give\PaymentGateways;

/**
 * Class PaypalSettingSection
 * @package Give\PaymentGateways
 *
 * @sicne 2.8.0
 */
class PaypalSettingSection implements SettingSection {

	/**
	 * @inheritDoc
	 */
	public function getSectionId() {
		return 'paypal';
	}

	/**
	 * @inheritDoc
	 */
	public function getSectionTitle() {
		return esc_html__( 'Paypal', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getSettings() {
		return [];
	}
}
