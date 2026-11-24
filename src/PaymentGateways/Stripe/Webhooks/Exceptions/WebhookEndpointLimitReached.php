<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;

/**
 * Thrown instead of creating an endpoint on an account that already has the maximum number of endpoints.
 *
 * @since TBD
 */
final class WebhookEndpointLimitReached extends Exception
{
    /**
     * @since TBD
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
