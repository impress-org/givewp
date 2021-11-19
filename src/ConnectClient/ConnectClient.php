<?php

namespace Give\ConnectClient;

/**
 * Class ConnectClient
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.8.0
 */
class ConnectClient
{
    /**
     * Api Url
     *
     * @since 2.8.0
     *
     * @var string
     */
    public $apiUrl = 'https://connect.givewp.com';

    /**
     * Get rest api endpoint url for requests.
     *
     * @since 2.8.0
     *
     * @param string $endpoint
     *
     * @return string
     */
    public function getApiUrl($endpoint)
    {
        return "{$this->apiUrl}/{$endpoint}";
    }
}
