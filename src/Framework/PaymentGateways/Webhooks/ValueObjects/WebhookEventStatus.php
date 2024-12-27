<?php

namespace Give\Framework\PaymentGateways\Webhooks\ValueObjects;

use ActionScheduler_Store;
use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static WebhookEventStatus COMPLETE()
 * @method static WebhookEventStatus PENDING()
 * @method static WebhookEventStatus RUNNING()
 * @method static WebhookEventStatus FAILED()
 * @method static WebhookEventStatus CANCELED()
 * @method bool isComplete()
 * @method bool isPending()
 * @method bool isRunning()
 * @method bool isFailed()
 * @method bool isCanceled()
 */
class WebhookEventStatus extends Enum
{
    const COMPLETE = ActionScheduler_Store::STATUS_COMPLETE;
    const PENDING = ActionScheduler_Store::STATUS_PENDING;
    const RUNNING = ActionScheduler_Store::STATUS_RUNNING;
    const FAILED = ActionScheduler_Store::STATUS_FAILED;
    const CANCELED = ActionScheduler_Store::STATUS_CANCELED;
}
