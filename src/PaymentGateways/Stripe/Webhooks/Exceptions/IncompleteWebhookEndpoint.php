<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;

/**
 * Thrown when an endpoint points at this site but is disabled or lacks events, and enabling or completing it failed.
 *
 * @since TBD
 */
final class IncompleteWebhookEndpoint extends Exception
{
    /**
     * @since TBD
     */
    public static function updateFailed(string $endpointId, WebhookEndpointException $previous): self
    {
        return new self(
            sprintf('Webhook endpoint %s could not be enabled or given the missing events.', esc_html($endpointId)),
            0,
            $previous
        );
    }
}
