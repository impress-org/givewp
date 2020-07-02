<?php

namespace Give\Vendor\Paypal\PayPalCheckoutSdk\Core;

use Give\Vendor\Paypal\PayPalHttp\HttpClient;
class PayPalHttpClient extends \Give\Vendor\Paypal\PayPalHttp\HttpClient {

	private $refreshToken;
	public $authInjector;
	public function __construct( \Give\Vendor\Paypal\PayPalCheckoutSdk\Core\PayPalEnvironment $environment, $refreshToken = null ) {
		parent::__construct( $environment );
		$this->refreshToken = $refreshToken;
		$this->authInjector = new \Give\Vendor\Paypal\PayPalCheckoutSdk\Core\AuthorizationInjector( $this, $environment, $refreshToken );
		$this->addInjector( $this->authInjector );
		$this->addInjector( new \Give\Vendor\Paypal\PayPalCheckoutSdk\Core\GzipInjector() );
		$this->addInjector( new \Give\Vendor\Paypal\PayPalCheckoutSdk\Core\FPTIInstrumentationInjector() );
	}
	public function userAgent() {
		return \Give\Vendor\Paypal\PayPalCheckoutSdk\Core\UserAgent::getValue();
	}
}
