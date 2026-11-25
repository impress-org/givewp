<?php

/**
 * @since 4.20.0
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;

/**
 * Thrown instead of creating an endpoint on an account that already has the maximum number of endpoints.
 *
 * @since 4.20.0
 */
final class WebhookEndpointLimitReached extends Exception
{
    /**
     * @since 4.20.0
     */
    public static function create(): self
    {
        return new self(
            sprintf(
                'The Stripe account already has %d webhook endpoints, the maximum Stripe allows.',
                WebhookTarget::MAX_ENDPOINTS_PER_ACCOUNT
            )
        );
    }
}
