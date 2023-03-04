<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use PayPalCheckoutSdk\Core\FPTIInstrumentationInjector;
use PayPalCheckoutSdk\Core\GzipInjector;
use PayPalCheckoutSdk\Core\PayPalEnvironment;

/**
 * Class PayPalHttpClient.
 *
 * This class extends PayPalCheckoutSdk\Core\PayPalHttpClient class.
 * PayPalHttpClient from php sdk force access token refresh on each http request.
 * We register AuthorizationInjector class to inject access token in http request header.
 * AuthorizationInjector clas refresh access token only if expired.
 *
 * @since 2.25.0
 */
class PayPalHttpClient extends \PayPalCheckoutSdk\Core\PayPalHttpClient
{
    /**
     * Class constructor.
     *
     * @since 2.25.0
     */
    public function __construct(PayPalEnvironment $environment, $refreshToken = null)
    {
        parent::__construct($environment);
        $this->authInjector = $this->getAuthorizationInjector($environment, $refreshToken);
        $this->addInjector($this->authInjector);
        $this->addInjector(new GzipInjector());
        $this->addInjector(new FPTIInstrumentationInjector());
    }

    /**
     * Returns AuthorizationInjector.
     *
     * @since 2.25.0
     */
    private function getAuthorizationInjector($environment, $refreshToken): AuthorizationInjector
    {
        $merchant = give(MerchantDetail::class);
        $authorizationInjector = new AuthorizationInjector($this, $environment, $refreshToken);

        // Set access token if exists.
        if ($merchant->accessToken) {
            $authorizationInjector->accessToken = $merchant->toArray()['token'];
        }

        return $authorizationInjector;
    }
}
