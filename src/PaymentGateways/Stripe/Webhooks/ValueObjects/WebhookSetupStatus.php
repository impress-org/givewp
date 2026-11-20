<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\IncompleteWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointLimitReached;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\PermissionException;
use Stripe\Exception\RateLimitException;
use Throwable;

/**
 * Outcome of setting up the webhook endpoint of one Stripe account.
 *
 * @since TBD
 *
 * @method static WebhookSetupStatus FOUND()
 * @method static WebhookSetupStatus CREATED()
 * @method static WebhookSetupStatus UPDATED()
 * @method static WebhookSetupStatus INCOMPLETE()
 * @method static WebhookSetupStatus LIMIT_REACHED()
 * @method static WebhookSetupStatus AUTHORIZATION()
 * @method static WebhookSetupStatus RATE_LIMITED()
 * @method static WebhookSetupStatus CONNECTION()
 * @method static WebhookSetupStatus INVALID_REQUEST()
 * @method static WebhookSetupStatus UNKNOWN()
 * @method bool isFound()
 * @method bool isCreated()
 * @method bool isUpdated()
 * @method bool isIncomplete()
 * @method bool isLimitReached()
 * @method bool isAuthorization()
 * @method bool isRateLimited()
 * @method bool isConnection()
 * @method bool isInvalidRequest()
 * @method bool isUnknown()
 */
final class WebhookSetupStatus extends Enum
{
    const FOUND = 'found';
    const CREATED = 'created';
    const UPDATED = 'updated';
    const INCOMPLETE = 'incomplete';
    const LIMIT_REACHED = 'limit_reached';
    const AUTHORIZATION = 'authorization';
    const RATE_LIMITED = 'rate_limited';
    const CONNECTION = 'connection';
    const INVALID_REQUEST = 'invalid_request';
    const UNKNOWN = 'unknown';

    /**
     * @since TBD
     */
    public static function fromException(Throwable $exception): self
    {
        if ($exception instanceof WebhookEndpointLimitReached) {
            return self::LIMIT_REACHED();
        }

        if ($exception instanceof IncompleteWebhookEndpoint) {
            return self::INCOMPLETE();
        }

        $stripeException = $exception instanceof WebhookEndpointException ? $exception->getPrevious() : null;

        if ($stripeException instanceof AuthenticationException || $stripeException instanceof PermissionException) {
            return self::AUTHORIZATION();
        }

        if ($stripeException instanceof RateLimitException) {
            return self::RATE_LIMITED();
        }

        if ($stripeException instanceof ApiConnectionException) {
            return self::CONNECTION();
        }

        if ($stripeException instanceof InvalidRequestException) {
            return self::INVALID_REQUEST();
        }

        return self::UNKNOWN();
    }

    /**
     * Whether the account has an enabled endpoint with every required event.
     *
     * @since TBD
     */
    public function isSuccessful(): bool
    {
        return in_array($this->getValue(), [self::FOUND, self::CREATED, self::UPDATED], true);
    }
}
