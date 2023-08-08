<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests;

use PayPalHttp\HttpRequest;

/**
 * Class GetAccessTokenWithClientCredentials
 *
 * @unreleased
 */
class GetAccessToken extends HttpRequest
{
    /**
     * @unreleased
     *
     * @param array $requestBody Request body.
     * @param array $headers Headers to be added to the request.
     */
    public function __construct(array $requestBody, array $headers)
    {
        parent::__construct('/v1/oauth2/token', 'POST');

        $this->headers = wp_parse_args($headers, $this->headers);

        $this->headers["Content-Type"] = "application/x-www-form-urlencoded";
        $this->body = $requestBody;
    }
}
