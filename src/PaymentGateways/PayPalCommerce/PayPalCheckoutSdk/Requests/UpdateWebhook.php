<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests;

use PayPalHttp\HttpRequest;

/**
 * Class UpdateWebhook.
 *
 * @since 4.1.0 Add PayPal-Partner-Attribution-Id header
 * @since 2.32.0
 */
class UpdateWebhook extends HttpRequest
{
    /**
     * @since 2.32.0
     *
     * @param string $webhookId Webhook ID.
     * @param array $requestBody Request body.
     */
    public function __construct(string $webhookId, array $requestBody)
    {
        parent::__construct("/v1/notifications/webhooks/$webhookId", 'PATCH');

        $this->headers['Content-Type'] = 'application/json';
        $this->headers["PayPal-Partner-Attribution-Id"] = give('PAYPAL_COMMERCE_ATTRIBUTION_ID');
        $this->body = wp_json_encode($requestBody);
    }
}
