<?php
namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests;

use PayPalHttp\HttpRequest;

/**
 * Class VerifyWebhookSignature
 *
 * @since 2.30.0
 */
class VerifyWebhookSignature extends HttpRequest
{
    /**
     * @since 2.30.0
     * @param array $requestBody Request body.
     */
    public function __construct(array $requestBody)
    {
        parent::__construct('/v1/notifications/verify-webhook-signature', 'POST');

        $this->headers["Content-Type"] = "application/json";
        $this->body = wp_json_encode($requestBody);
    }
}
