<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use PayPalCheckoutSdk\Core\PayPalEnvironment;

/**
 * Class PayPalHttpClient.
 *
 * This class extends PayPalCheckoutSdk\Core\PayPalHttpClient class.
 * PayPalHttpClient from php sdk force access token refresh on each http request.
 * We register AuthorizationInjector class to inject access token in http request header.
 * AuthorizationInjector clas refresh access token only if expired.
 *
 * @since 2.32.0 Remove unnecessary properties.
 * @since 2.25.0
 */
class PayPalHttpClient extends \PayPalCheckoutSdk\Core\PayPalHttpClient
{
    /**
     * Class constructor.
     *
     * @since 2.25.0
     */
    public function __construct(PayPalEnvironment $environment)
    {
        parent::__construct($environment);

        // Remove existing AuthorizationInjector.
        foreach ($this->injectors as $index => $injector) {
            if ($injector instanceof \PayPalCheckoutSdk\Core\AuthorizationInjector) {
                unset($this->injectors[$index]);
            }
        }

        // Add custom AuthorizationInjector.
        $this->authInjector = $this->getAuthorizationInjector($environment);
        $this->addInjector($this->authInjector);
    }

    /**
     * Returns AuthorizationInjector.
     *
     * @since 2.25.0
     */
    private function getAuthorizationInjector($environment): AuthorizationInjector
    {
        $merchant = give(MerchantDetail::class);
        $authorizationInjector = new AuthorizationInjector();

        // Set access token if exists.
        if ($merchant->accessToken) {
            $authorizationInjector->accessToken = AccessToken::fromArray($merchant->toArray()['token']);
        }

        return $authorizationInjector;
    }
}
