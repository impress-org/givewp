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
 * Creates a webhook endpoint on a connected Stripe account.
 *
 * @since TBD
 */
final class CreateWebhookEndpoint
{
    use PreparesWebhookEndpointRequests;

    /**
     * Stripe requires an explicit API version when an endpoint is created as a connected account,
     * and does not allow changing it afterwards.
     *
     * @since TBD
     *
     * @param string               $mode          "live" or "test"
     * @param string[]             $enabledEvents Event types, or ["*"] for all.
     * @param array<string,string> $metadata
     *
     * @return WebhookEndpointDto The only response that carries the signing secret.
     *
     * @throws WebhookEndpointException
     */
    public function __invoke(
        AccountDetail $account,
        string $mode,
        string $url,
        array $enabledEvents,
        string $apiVersion,
        ?string $description = null,
        array $metadata = []
    ): WebhookEndpointDto {
        $options = $this->requestOptions($account, $mode);

        $params = [
            'url' => $url,
            'enabled_events' => $enabledEvents,
            'api_version' => $apiVersion,
        ];

        if (null !== $description) {
            $params['description'] = $description;
        }

        if ($metadata) {
            $params['metadata'] = $metadata;
        }

        try {
            $endpoint = WebhookEndpoint::create($params, $options);
        } catch (ApiErrorException $exception) {
            throw WebhookEndpointException::fromStripe($exception);
        }

        return WebhookEndpointDto::fromStripeObject($endpoint);
    }
}
