<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Actions;

use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\DataTransferObjects\WebhookEndpointDto;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\PaymentGateways\Stripe\Webhooks\Traits\PreparesWebhookEndpointRequests;
use InvalidArgumentException;
use Stripe\Exception\ApiErrorException;
use Stripe\WebhookEndpoint;

/**
 * Updates a webhook endpoint on a connected Stripe account.
 *
 * @since TBD
 */
final class UpdateWebhookEndpoint
{
    use PreparesWebhookEndpointRequests;

    private const UPDATABLE_FIELDS = ['url', 'enabled_events', 'description', 'disabled', 'metadata'];

    /**
     * $changes holds only the fields to change, keyed by Stripe parameter name: "url", "enabled_events",
     * "description", "disabled" (bool), "metadata". Stripe keeps metadata keys that are not sent, and removes
     * a key when its value is an empty string. "api_version" cannot be changed after creation.
     *
     * @since TBD
     *
     * @param string $mode "live" or "test"
     * @param array{
     *     url?: string,
     *     enabled_events?: string[],
     *     description?: string,
     *     disabled?: bool,
     *     metadata?: array<string, string>
     * } $changes
     *
     * @throws WebhookEndpointException
     * @throws InvalidArgumentException When $changes is empty or holds a field Stripe does not allow updating.
     */
    public function __invoke(
        AccountDetail $account,
        string $mode,
        string $endpointId,
        array $changes
    ): WebhookEndpointDto {
        if (!$changes) {
            throw new InvalidArgumentException('No webhook endpoint changes were given.');
        }

        $unsupported = array_diff(array_keys($changes), self::UPDATABLE_FIELDS);

        if ($unsupported) {
            throw new InvalidArgumentException(
                sprintf('Webhook endpoint fields cannot be updated: %s.', esc_html(implode(', ', $unsupported)))
            );
        }

        $this->assertEndpointId($endpointId);
        $options = $this->requestOptions($account, $mode);

        try {
            $endpoint = WebhookEndpoint::update($endpointId, $changes, $options);
        } catch (ApiErrorException $exception) {
            throw WebhookEndpointException::fromStripe($exception);
        }

        return WebhookEndpointDto::fromStripeObject($endpoint);
    }
}
