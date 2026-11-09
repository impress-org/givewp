<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks;

use Give\Log\Log;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Throwable;

/**
 * Writes the webhook setup, health check and disconnect entries of the Give log (GiveWP > Tools > Logs).
 *
 * Context carries account slug, type, mode and Stripe identifiers, never a key or a signing secret.
 *
 * @since TBD
 */
final class WebhookLogger
{
    public const SOURCE = 'Stripe Webhooks';

    /**
     * @since TBD
     *
     * @param array<string, scalar|string[]|null> $context
     */
    public static function error(string $message, array $context = []): void
    {
        Log::error($message, self::withDefaults($context));
    }

    /**
     * @since TBD
     *
     * @param array<string, scalar|string[]|null> $context
     */
    public static function warning(string $message, array $context = []): void
    {
        Log::warning($message, self::withDefaults($context));
    }

    /**
     * @since TBD
     *
     * @param array<string, scalar|string[]|null> $context
     */
    public static function info(string $message, array $context = []): void
    {
        Log::info($message, self::withDefaults($context));
    }

    /**
     * @since TBD
     *
     * @param array<string, scalar|string[]|null> $context
     */
    public static function debug(string $message, array $context = []): void
    {
        Log::debug($message, self::withDefaults($context));
    }

    /**
     * What support needs to find a failed request in the Stripe Dashboard.
     *
     * @since TBD
     *
     * @return array<string, scalar|string[]|null>
     */
    public static function failureContext(Throwable $exception): array
    {
        $context = ['exception' => get_class($exception), 'error' => $exception->getMessage()];

        $stripe = $exception;

        while ($stripe && !$stripe instanceof WebhookEndpointException) {
            $stripe = $stripe->getPrevious();
        }

        if ($stripe) {
            $context['stripe_http_status'] = $stripe->getHttpStatus();
            $context['stripe_error_type'] = $stripe->getErrorType();
            $context['stripe_request_id'] = $stripe->getRequestId();
        }

        return $context;
    }

    /**
     * @since TBD
     *
     * @param array<string, scalar|string[]|null> $context
     *
     * @return array<string, scalar|string[]|null>
     */
    private static function withDefaults(array $context): array
    {
        return array_merge(['category' => 'Payment', 'source' => self::SOURCE], $context);
    }
}
