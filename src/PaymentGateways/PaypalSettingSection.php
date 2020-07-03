<?php
namespace  Give\PaymentGateways;

use Give\PaymentGateways;
use function give_get_current_setting_section as getCurrentSettingSection;

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
	public function register() {
		$this->paypalCheckout = new PayPalCheckout\PayPalCheckout();

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		add_action( 'give_get_groups_paypal', [ $this, 'getGroups' ] );
		add_filter( 'give_get_settings_gateways', [ $this, 'registerPaypalSettings' ] );
		add_filter( 'give_get_sections_gateways', [ $this, 'registerPaypalSettingSection' ] );
	}

	/**
	 * @var PaymentGateways\PayPalCheckout\PayPalCheckout
	 *
	 * @since 2.8.0
	 */
	private $paypalCheckout;

	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'paypal';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return esc_html__( 'PayPal', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getSettings() {
		return [];
	}

	/**
	 * Get groups.
	 * @return array
	 *
	 * @since 2.8.0
	 */
	public function getGroups() {
		return [
			$this->paypalCheckout->getId() => $this->paypalCheckout->getName(),
			'paypal-standard'              => esc_html__( 'PayPal Standard', 'give' ),
			'paypal-legacy'                => esc_html__( 'PayPal Legacy', 'give' ),
		];
	}

	/**
	 * @param $settings
	 *
	 * @return array
	 */
	public function registerPaypalSettings( $settings ) {
		$currentSection = getCurrentSettingSection();

		return $currentSection === $this->getId() ?
			$this->getSettings() :
			$settings;
	}

	/**
	 * @param $sections
	 *
	 * @return array
	 */
	public function registerPaypalSettingSection( $sections ) {
		$sections[ $this->getId() ] = $this->getName();

		return $sections;
	}
}
