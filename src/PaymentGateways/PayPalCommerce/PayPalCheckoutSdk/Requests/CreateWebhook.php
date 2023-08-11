<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests;

use PayPalHttp\HttpRequest;

/**
 * Class CreateWebhook
 *
 * This class use  as request to create a webhook.
 *
 * @since 2.32.0
 */
class CreateWebhook extends HttpRequest
{
    public function __construct(array $requestBody)
    {
        parent::__construct('/v1/notifications/webhooks', 'POST');

        $this->headers["Content-Type"] = "application/json";
        $this->body = wp_json_encode($requestBody);
    }
}
