<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * Read-only state of the webhook endpoint that points at this site on one Stripe account.
 *
 * @since TBD
 *
 * @method static WebhookHealthStatus HEALTHY()
 * @method static WebhookHealthStatus MISSING()
 * @method static WebhookHealthStatus DISABLED()
 * @method static WebhookHealthStatus MISSING_EVENTS()
 * @method bool isHealthy()
 * @method bool isMissing()
 * @method bool isDisabled()
 * @method bool isMissingEvents()
 */
final class WebhookHealthStatus extends Enum
{
    const HEALTHY = 'healthy';
    const MISSING = 'missing';
    const DISABLED = 'disabled';
    const MISSING_EVENTS = 'missing_events';
}
