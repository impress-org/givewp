<?php
namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests;

use PayPalHttp\HttpRequest;

/**
 * Class VerifyWebhookSignature
 *
 * @since 4.1.0 Add PayPal-Partner-Attribution-Id header
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
        $this->headers["PayPal-Partner-Attribution-Id"] = give('PAYPAL_COMMERCE_ATTRIBUTION_ID');
        $this->body = wp_json_encode($requestBody);
    }
}
