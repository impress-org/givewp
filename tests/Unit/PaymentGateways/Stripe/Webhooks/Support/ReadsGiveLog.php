<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support;

use Give\Log\LogModel;
use Give\Log\LogRepository;
use Give\PaymentGateways\Stripe\Webhooks\WebhookLogger;

/**
 * @since TBD
 */
trait ReadsGiveLog
{
    /**
     * The Stripe webhook entries of one level, optionally narrowed to an account slug.
     *
     * @since TBD
     *
     * @param string $type A Give\Log\ValueObjects\LogType value
     *
     * @return LogModel[]
     */
    protected function webhookLogs(string $type, ?string $accountSlug = null): array
    {
        return array_values(
            array_filter(
                give(LogRepository::class)->getLogsByType($type),
                static function (LogModel $log) use ($accountSlug) {
                    $context = $log->getContext();

                    return WebhookLogger::SOURCE === $log->getSource()
                        && (null === $accountSlug || ($context['account'] ?? null) === $accountSlug);
                }
            )
        );
    }
}
