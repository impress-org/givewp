<?php

namespace Give\Vendor\PayPal\PayPalCheckoutSdk\Core;

use Give\Vendor\PayPal\PayPalHttp\HttpClient;
class PayPalHttpClient extends \Give\Vendor\PayPal\PayPalHttp\HttpClient {

	private $refreshToken;
	public $authInjector;
	public function __construct( \Give\Vendor\PayPal\PayPalCheckoutSdk\Core\PayPalEnvironment $environment, $refreshToken = null ) {
		parent::__construct( $environment );
		$this->refreshToken = $refreshToken;
		$this->authInjector = new \Give\Vendor\PayPal\PayPalCheckoutSdk\Core\AuthorizationInjector( $this, $environment, $refreshToken );
		$this->addInjector( $this->authInjector );
		$this->addInjector( new \Give\Vendor\PayPal\PayPalCheckoutSdk\Core\GzipInjector() );
		$this->addInjector( new \Give\Vendor\PayPal\PayPalCheckoutSdk\Core\FPTIInstrumentationInjector() );
	}
	public function userAgent() {
		return \Give\Vendor\PayPal\PayPalCheckoutSdk\Core\UserAgent::getValue();
	}
}
