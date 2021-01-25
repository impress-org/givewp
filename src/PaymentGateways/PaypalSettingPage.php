<?php
namespace  Give\PaymentGateways;

use Give\PaymentGateways\PayPalCommerce\PayPalCommerce;
use Give\PaymentGateways\PayPalCommerce\AdminSettingFields;
use Give\PaymentGateways\PayPalStandard\PayPalStandard;
use function give_get_current_setting_section as getCurrentSettingSection;

/**
 * Class PaypalSettingSection
 * @package Give\PaymentGateways
 *
 * @sicne 2.9.0
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
	 * @since 2.9.0
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
		add_filter( 'give_get_sections_gateways', [ $this, 'registerPaypalSettingSection' ], 5 );

		// Load custom setting fields.
		/* @var AdminSettingFields $adminSettingFields */
		$adminSettingFields = give( AdminSettingFields::class );
		$adminSettingFields->boot();
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
	 * @since 2.9.0
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
	 * @since 2.9.0
	 *
	 * @return array
	 */
	public function registerPaypalSettings( $settings ) {
		$currentSection = getCurrentSettingSection();

		if ( $currentSection === $this->getId() ) {
			$settings = $this->getSettings();
		}

		return $settings;
	}

	/**
	 * Register setting section.
	 *
	 * @param array $sections
	 *
	 * @since 2.9.0
	 *
	 * @return array
	 */
	public function registerPaypalSettingSection( $sections ) {
		$sections[ $this->getId() ] = $this->getName();

		return $sections;
	}
}
