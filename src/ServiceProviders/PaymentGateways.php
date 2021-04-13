<?php

namespace Give\ServiceProviders;

use Give\Controller\PayPalWebhooks;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\PaymentGateways\PaymentGateway;
use Give\PaymentGateways\PayPalCommerce\AdvancedCardFields;
use Give\PaymentGateways\PayPalCommerce\AjaxRequestHandler;
use Give\PaymentGateways\PayPalCommerce\DonationProcessor;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\RefreshToken;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalAuth;
use Give\PaymentGateways\PayPalCommerce\ScriptLoader;
use Give\PaymentGateways\PayPalCommerce\onBoardingRedirectHandler;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;
use Give\PaymentGateways\PayPalCommerce\PayPalCommerce;
use Give\PaymentGateways\PayPalCommerce\Repositories\Webhooks;
use Give\PaymentGateways\PayPalCommerce\Webhooks\WebhookRegister;
use Give\PaymentGateways\PayPalStandard\Migrations\SetPayPalStandardGatewayId;
use Give\PaymentGateways\PayPalStandard\PayPalStandard;
use Give\PaymentGateways\PaypalSettingPage;
use Give\PaymentGateways\Stripe\DonationFormElements;
use Give\PaymentGateways\Stripe\ApplicationFee;
use Give\PaymentGateways\Stripe\Repositories\AccountDetail as AccountDetailRepository;

/**
 * Class PaymentGateways
 *
 * The Service Provider for loading the Payment Gateways
 *
 * @since 2.8.0
 */
class PaymentGateways implements ServiceProvider {
	/**
	 * Array of PaymentGateway classes to be bootstrapped
	 *
	 * @var string[]
	 */
	public $gateways = [
		PayPalStandard::class,
		PayPalCommerce::class,
	];

	/**
	 * Array of SettingPage classes to be bootstrapped
	 *
	 * @var string[]
	 */
	private $gatewaySettingsPages = [
		PaypalSettingPage::class,
	];

	/**
	 * @inheritDoc
	 */
	public function register() {
		give()->bind(
			'PAYPAL_COMMERCE_ATTRIBUTION_ID',
			static function() {
				return 'GiveWP_SP_PCP';
			}
		); // storage

		give()->singleton( PayPalWebhooks::class );
		give()->singleton( Webhooks::class );
		give()->singleton( DonationFormElements::class );
		give()->singleton(
			ApplicationFee::class,
			function() {
				return new ApplicationFee(
					give( AccountDetailRepository::class )->getAccountDetail(
						give_stripe_get_connected_account_options()['stripe_account']
					)
				);
			}
		);

		$this->registerPayPalCommerceClasses();
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		add_filter( 'give_register_gateway', [ $this, 'bootGateways' ] );
		add_action( 'admin_init', [ $this, 'handleSellerOnBoardingRedirect' ] );
		add_action( 'give-settings_start', [ $this, 'registerPayPalSettingPage' ] );
		Hooks::addFilter( 'give_form_html_tags', DonationFormElements::class, 'addFormHtmlTags', 99 );

		$this->registerMigrations();
	}

	/**
	 * Handle seller on boarding redirect.
	 *
	 * @since 2.8.0
	 */
	public function handleSellerOnBoardingRedirect() {
		give( onBoardingRedirectHandler::class )->boot();
	}

	/**
	 * Register all payment gateways setting pages with GiveWP.
	 *
	 * @since 2.8.0
	 */
	public function registerPayPalSettingPage() {
		foreach ( $this->gatewaySettingsPages as $page ) {
			give()->make( $page )->boot();
		}
	}

	/**
	 * Registers all of the payment gateways with GiveWP
	 *
	 * @since 2.8.0
	 *
	 * @param array $gateways
	 *
	 * @return array
	 */
	public function bootGateways( array $gateways ) {
		foreach ( $this->gateways as $gateway ) {
			/** @var PaymentGateway $gateway */
			$gateway = give( $gateway );

			$gateways[ $gateway->getId() ] = [
				'admin_label'    => $gateway->getName(),
				'checkout_label' => $gateway->getPaymentMethodLabel(),
			];

			$gateway->boot();
		}

		return $gateways;
	}

	/**
	 * Registers the classes for the PayPal Commerce gateway
	 *
	 * @since 2.8.0
	 */
	private function registerPayPalCommerceClasses() {
		give()->singleton( AdvancedCardFields::class );
		give()->singleton( DonationProcessor::class );
		give()->singleton( PayPalClient::class );
		give()->singleton( RefreshToken::class );
		give()->singleton( AjaxRequestHandler::class );
		give()->singleton( ScriptLoader::class );
		give()->singleton( WebhookRegister::class );
		give()->singleton( Webhooks::class );
		give()->singleton( MerchantDetails::class );
		give()->singleton( PayPalAuth::class );

		give()->singleton(
			MerchantDetail::class,
			static function () {
				/** @var MerchantDetails $repository */
				$repository = give( MerchantDetails::class );

				return $repository->getDetails();
			}
		);

		give()->resolving(
			MerchantDetails::class,
			static function ( MerchantDetails $details ) {
				$details->setMode( give_is_test_mode() ? 'sandbox' : 'live' );
			}
		);

		give()->resolving(
			Webhooks::class,
			static function ( Webhooks $repository ) {
				$repository->setMode( give_is_test_mode() ? 'sandbox' : 'live' );
			}
		);
	}

	/**
	 * Register migrations
	 *
	 * @since 2.9.1
	 */
	private function registerMigrations() {
		/* @var MigrationsRegister $migrationRegisterer */
		$migrationRegisterer = give( MigrationsRegister::class );

		$migrationRegisterer->addMigration( SetPayPalStandardGatewayId::class );
	}
}
