<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests;

use PayPalHttp\HttpRequest;

/**
 * Class GenerateClientToken
 *
 * This class use to generate a client token for PayPal JS SDK.
 *
 * @unreleased
 */
class GenerateClientToken extends HttpRequest
{
    /**
     * @unreleased
     */
    public function __construct()
    {
        parent::__construct('/v1/identity/generate-token', "POST");
    }
}
