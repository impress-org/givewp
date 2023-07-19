<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Repositories\Traits\HasMode;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

/**
 * Class PayPalClient
 *
 * @package Give\PaymentGateways\PaypalCommerce
 *
 * @since 2.9.0
 */
class PayPalClient
{
    use HasMode;

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
     * @since 2.25.0 Add custom AuthorizationInjector.
     * @since 2.9.0
     */
    public function getHttpClient(): PayPalHttpClient
    {
        return new PayPalCheckoutSdk\PayPalHttpClient($this->getEnvironment());
    }

    /**
     * Get api url.
     *
     * @deprecated 2.30.1
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
