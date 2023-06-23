<?php
namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests;

use PayPalHttp\HttpRequest;

/**
 * Class VerifyWebhookSignature
 */
class VerifyWebhookSignature extends HttpRequest
{
    public function __construct(array $requestBody)
    {
        parent::__construct('/v1/notifications/verify-webhook-signature', 'POST');

        $this->headers["Content-Type"] = "application/json";
        $this->body = wp_json_encode($requestBody);
    }
}
