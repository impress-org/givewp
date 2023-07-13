<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests;

use PayPalHttp\HttpRequest;

/**
 * Class GenerateClientToken
 *
 * This class use to generate a client token for PayPal JS SDK.
 *
 * @since 2.30.0
 */
class GenerateClientToken extends HttpRequest
{
    /**
     * @since 2.30.0
     */
    public function __construct()
    {
        parent::__construct('/v1/identity/generate-token', 'POST');
    }
}
