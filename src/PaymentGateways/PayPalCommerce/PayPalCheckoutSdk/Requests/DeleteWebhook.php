<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests;

use PayPalHttp\HttpRequest;

class DeleteWebhook extends HttpRequest
{
    /**
     * @since 4.1.0 Add PayPal-Partner-Attribution-Id header
     * @since 2.32.0
     *
     * @param string $webhookId Webhook ID.
     */
    public function __construct(string $webhookId)
    {
        parent::__construct("/v1/notifications/webhooks/$webhookId", 'DELETE');

        $this->headers['Content-Type'] = 'application/json';
        $this->headers["PayPal-Partner-Attribution-Id"] = give('PAYPAL_COMMERCE_ATTRIBUTION_ID');
    }
}
