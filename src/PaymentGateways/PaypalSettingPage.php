<?php
namespace  Give\PaymentGateways;

use Give\PaymentGateways\PayPalCommerce\PayPalCommerce;
use Give\PaymentGateways\PayPalCommerce\AdminSettingFields;
use Give\PaymentGateways\PayPalStandard\PayPalStandard;
use Give\PaymentGateways\PayPalCommerce\onBoardingRedirectHandler;
use function give_get_current_setting_section as getCurrentSettingSection;

/**
 * Class PaypalSettingSection
 * @package Give\PaymentGateways
 *
 * @sicne 2.8.0
 */
class PaypalSettingPage implements SettingPage {
	/**
	 * @var PayPalCommerce
	 */
	private $payPalCommerce;

	/**
	 * @var PayPalStandard
	 */
	private $paypalStandard;

	/**
	 * Register properties
	 *
	 * @param  PayPalCommerce  $payPalCommerce
	 * @param  PayPalStandard  $paypalStandard
	 *
	 * @since 2.8.0
	 */
	public function __construct( PayPalCommerce $payPalCommerce, PayPalStandard $paypalStandard ) {
		$this->payPalCommerce = $payPalCommerce;
		$this->paypalStandard = $paypalStandard;
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		add_action( 'give_get_groups_paypal', [ $this, 'getGroups' ] );
		add_filter( 'give_get_settings_gateways', [ $this, 'registerPaypalSettings' ] );
		add_filter( 'give_get_sections_gateways', [ $this, 'registerPaypalSettingSection' ] );

		// Load custom setting fields.
		$adminSettingFields = new AdminSettingFields();
		$adminSettingFields->boot();

		// Handle paypal redirect on setting page after on boarding seller.
		( new onBoardingRedirectHandler )->boot();

	}

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
		$settings[ $this->payPalCommerce->getId() ] = $this->payPalCommerce->getOptions();
		$settings[ $this->paypalStandard->getId() ] = $this->paypalStandard->getOptions();

		return $settings;
	}

	/**
	 * Get groups.
	 *
	 * @since 2.8.0
	 *
	 * @return array
	 */
	public function getGroups() {
		return [
			$this->payPalCommerce->getId() => $this->payPalCommerce->getName(),
			$this->paypalStandard->getId() => $this->paypalStandard->getName(),
		];
	}

	/**
	 * Register settings.
	 *
	 * @param array $settings
	 *
	 * @since 2.8.0
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
	 * Register setting section.
	 *
	 * @param array $sections
	 *
	 * @since 2.8.0
	 *
	 * @return array
	 */
	public function registerPaypalSettingSection( $sections ) {
		$sections[ $this->getId() ] = $this->getName();

		return $sections;
	}
}
