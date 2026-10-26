<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Actions;

use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\DataTransferObjects\WebhookEndpointDto;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\PaymentGateways\Stripe\Webhooks\Traits\PreparesWebhookEndpointRequests;
use Stripe\Exception\ApiErrorException;
use Stripe\WebhookEndpoint;

/**
 * Lists every webhook endpoint registered on a connected Stripe account.
 *
 * @since TBD
 */
final class ListWebhookEndpoints
{
    use PreparesWebhookEndpointRequests;

    /**
     * @since TBD
     *
     * @param string $mode "live" or "test"
     *
     * @return WebhookEndpointDto[]
     *
     * @throws WebhookEndpointException
     */
    public function __invoke(AccountDetail $account, string $mode): array
    {
        $options = $this->requestOptions($account, $mode);
        $endpoints = [];

        try {
            foreach (WebhookEndpoint::all(['limit' => 100], $options)->autoPagingIterator() as $endpoint) {
                $endpoints[] = WebhookEndpointDto::fromStripeObject($endpoint);
            }
        } catch (ApiErrorException $exception) {
            throw WebhookEndpointException::fromStripe($exception);
        }

        return $endpoints;
    }
}
