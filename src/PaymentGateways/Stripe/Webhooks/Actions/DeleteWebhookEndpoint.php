<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Actions;

use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\PaymentGateways\Stripe\Webhooks\Traits\PreparesWebhookEndpointRequests;
use Stripe\Exception\ApiErrorException;
use Stripe\WebhookEndpoint;

/**
 * Deletes a webhook endpoint from a connected Stripe account.
 *
 * @since TBD
 */
final class DeleteWebhookEndpoint
{
    use PreparesWebhookEndpointRequests;

    /**
     * Stripe answers 404 when the endpoint no longer exists. The exception's getHttpStatus() lets a caller
     * treat that as already deleted.
     *
     * @since TBD
     *
     * @param string $mode "live" or "test"
     *
     * @throws WebhookEndpointException
     */
    public function __invoke(AccountDetail $account, string $mode, string $endpointId): void
    {
        $this->assertEndpointId($endpointId);
        $options = $this->requestOptions($account, $mode);

        try {
            WebhookEndpoint::constructFrom(['id' => $endpointId], $options)->delete();
        } catch (ApiErrorException $exception) {
            throw WebhookEndpointException::fromStripe($exception);
        }
    }
}
