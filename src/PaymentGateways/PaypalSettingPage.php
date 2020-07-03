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

		// Custom field type for paypal checkout options
		add_action( 'give_admin_field_paypal_checkout_account_manger', [ $this, 'paypalCheckoutAccountManagerField' ] );
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
	 * Paypal Checkout account manager custom field
	 *
	 * @since 2.8.0
	 */
	public function paypalCheckoutAccountManagerField() {
		?>
		<div>
			<h3><?php esc_html_e( 'Accept Donations with PayPal Checkout', 'give' ); ?></h3>
			<p><?php esc_html_e( 'Allow your donors to give using Debit or Credit Cards directly on your website with no additional fees. Upgrade to PayPal Pro and provide your donors with even more payment options using PayPal Smart Buttons.', 'give' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'Credit and Debit Card Donations', 'give' ); ?></li>
				<li><?php esc_html_e( 'Accept payments from around the world', 'give' ); ?></li>
				<li><?php esc_html_e( 'Improve donation conversion rates', 'give' ); ?></li>
				<li><?php esc_html_e( 'Easy no-API key connection', 'give' ); ?></li>
				<li><?php esc_html_e( 'PayPal, Apple and Google Pay', 'give' ); ?></li>
			</ul>
			<div>
				<div><?php esc_html_e( 'PayPal Connection', 'give' ); ?></div>
				<div>
					<button><?php esc_html_e( 'Connect with PayPal', 'give' ); ?></button>
					<span><?php esc_html_e( 'PayPal is currently NOT connected.', 'give' ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}
}
