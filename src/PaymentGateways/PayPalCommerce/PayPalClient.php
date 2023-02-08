<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\AccessToken;
use Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\AuthorizationInjector;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

/**
 * Class PayPalClient
 *
 * This class set Authorization in http request header if missing.
 * Authorization header build with merchant access token.
 * Fresh Merchant access token will be fetched from PayPal if expired, for http request.
 *
 * @package Give\PaymentGateways\PaypalCommerce
 *
 * @since 2.9.0
 */
class PayPalClient
{
    /**
     * Environment mode.
     *
     * @since 2.9.0
     *
     * @var string
     */
    public $mode = null;

    /**
     * PayPalClient constructor.
     */
    public function __construct()
    {
        $this->mode = give_is_test_mode() ? 'sandbox' : 'live';
    }

    /**
     * Get environment.
     *
     * @sicne 2.9.0
     *
     * @return ProductionEnvironment|SandboxEnvironment
     */
    public function getEnvironment()
    {
        /* @var MerchantDetail $merchant */
        $merchant = give(MerchantDetail::class);

        return 'sandbox' === $this->mode ?
            new SandboxEnvironment($merchant->clientId, $merchant->clientSecret) :
            new ProductionEnvironment($merchant->clientId, $merchant->clientSecret);
    }

    /**
     * Get http client.
     *
     * @since 2.9.0
     */
    public function getHttpClient(): PayPalHttpClient
    {
        $paypalEnvironment = $this->getEnvironment();
        $paypalHttpClient = new PayPalHttpClient($paypalEnvironment);
        $authorizationInjector = new AuthorizationInjector($paypalHttpClient, $paypalEnvironment, null);
        $authorizationInjector->accessToken = AccessToken::fromArray(give(MerchantDetail::class)->toArray()['token']);

        $paypalHttpClient->addInjector($authorizationInjector);

        return $paypalHttpClient;
    }

    /**
     * Get api url.
     *
     * @since 2.9.0
     *
     * @param string $endpoint
     *
     * @return string
     */
    public function getApiUrl($endpoint)
    {
        $baseUrl = $this->getEnvironment()->baseUrl();

        return "{$baseUrl}/$endpoint";
    }

    /**
     * Get PayPal homepage url.
     *
     * @since 2.9.0
     *
     * @return string
     */
    public function getHomePageUrl()
    {
        return sprintf(
            'https://%1$spaypal.com/',
            'sandbox' === $this->mode ? 'sandbox.' : ''
        );
    }
}
