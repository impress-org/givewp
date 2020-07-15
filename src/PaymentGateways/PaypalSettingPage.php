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
class PaypalSettingPage implements SettingPage {
	/**
	 * @var PayPalCheckout\PayPalCheckout
	 */
	private $paypalCheckout;

	/**
	 * @var PayPalStandard\PayPalStandard
	 */
	private $paypalStandard;

	/**
	 * Register properties
	 * @return PaypalSettingPage
	 *
	 * @since 2.8.0
	 */
	public function register() {
		$this->paypalCheckout = new PayPalCheckout\PayPalCheckout();
		$this->paypalStandard = new PaymentGateways\PayPalStandard\PayPalStandard();

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		add_action( 'give_get_groups_paypal', [ $this, 'getGroups' ] );
		add_filter( 'give_get_settings_gateways', [ $this, 'registerPaypalSettings' ] );
		add_filter( 'give_get_sections_gateways', [ $this, 'registerPaypalSettingSection' ] );
		add_action( 'give-settings_start', [ $this, 'savePayPalMerchantDetails' ] );

		// Load custom setting fields.
		$adminSettingFields = new PaymentGateways\PayPalCheckout\AdminSettingFields();
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
		$settings[ $this->paypalCheckout->getId() ] = $this->paypalCheckout->getOptions();
		$settings[ $this->paypalStandard->getId() ] = $this->paypalStandard->getOptions();
		$settings['paypal-legacy']                  = [];

		return $settings;
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
			$this->paypalStandard->getId() => $this->paypalStandard->getName(),
			'paypal-legacy'                => esc_html__( 'PayPal Legacy', 'give' ),
		];
	}

	/**
	 * Register settings.
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
	 * Register setting section.
	 * @param $sections
	 *
	 * @return array
	 * @since 2.8.0
	 */
	public function registerPaypalSettingSection( $sections ) {
		$sections[ $this->getId() ] = $this->getName();

		return $sections;
	}

	/**
	 * Save PayPal merchant details
	 *
	 * @return void
	 * @since 2.8.0
	 */
	public function savePayPalMerchantDetails() {
		// Save PayPal merchant details only if admin redirected to PayPal connect setting page after completing onboarding process.
		if (
			! isset( $_GET['merchantIdInPayPal'] ) ||
			! \Give_Admin_Settings::is_setting_page( 'gateways', 'paypal' )
		) {
			return;
		}

		$paypalGetData = wp_parse_args( $_SERVER['QUERY_STRING'] );

		$allowedPayPalData = [
			'merchantIdInPayPal',
			'permissionsGranted',
			'accountStatus',
			'consentStatus',
			'productIntentId',
			'isEmailConfirmed',
			'returnMessage',
		];

		$allAccounts = maybe_unserialize( give_update_option( 'paypalCheckoutAccounts', [] ) );
		$paypalData  = array_intersect_key( $paypalGetData, array_flip( $allowedPayPalData ) );

		$allAccounts[ $paypalData['merchantIdInPayPal'] ] = $paypalData;

		give_update_option( 'paypalCheckoutAccounts', maybe_serialize( $allAccounts ) );
	}
}
