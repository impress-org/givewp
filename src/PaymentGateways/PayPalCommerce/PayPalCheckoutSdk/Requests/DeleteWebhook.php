<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests;

use PayPalHttp\HttpRequest;

class DeleteWebhook extends HttpRequest
{
    /**
     * @since 2.32.0
     *
     * @param string $webhookId Webhook ID.
     */
    public function __construct(string $webhookId)
    {
        parent::__construct("/v1/notifications/webhooks/$webhookId", 'DELETE');

        $this->headers['Content-Type'] = 'application/json';
    }
}
