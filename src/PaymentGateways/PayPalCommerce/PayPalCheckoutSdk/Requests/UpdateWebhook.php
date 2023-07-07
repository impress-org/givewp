<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests;

use PayPalHttp\HttpRequest;

/**
 * Class UpdateWebhook.
 *
 * @unreleased
 */
class UpdateWebhook extends HttpRequest
{
    /**
     * @unreleased
     *
     * @param string $webhookId Webhook ID.
     * @param array $requestBody Request body.
     */
    public function __construct(string $webhookId, array $requestBody)
    {
        parent::__construct("/v1/notifications/webhooks/$webhookId", 'PATCH');

        $this->headers['Content-Type'] = 'application/json';
        $this->body = wp_json_encode($requestBody);
    }
}
