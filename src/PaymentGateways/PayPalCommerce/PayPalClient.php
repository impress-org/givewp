<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

/**
 * Class PayPalClient
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
     *
     * @return PayPalHttpClient
     */
    public function getHttpClient()
    {
        return new PayPalHttpClient($this->getEnvironment());
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
