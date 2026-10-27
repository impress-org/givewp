<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Actions;

use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\PaymentGateways\Stripe\Webhooks\ValueObjects\WebhookHealthStatus;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;

/**
 * Reports whether a Stripe account sends every required event to this site, without changing anything.
 *
 * @since TBD
 */
final class CheckWebhookEndpointHealth
{
    /**
     * @var ListWebhookEndpoints
     */
    private $listEndpoints;

    /**
     * @var WebhookTarget
     */
    private $target;

    /**
     * @since TBD
     */
    public function __construct(ListWebhookEndpoints $listEndpoints, WebhookTarget $target)
    {
        $this->listEndpoints = $listEndpoints;
        $this->target = $target;
    }

    /**
     * @since TBD
     *
     * @param string $mode "live" or "test"
     *
     * @throws WebhookEndpointException
     */
    public function __invoke(AccountDetail $account, string $mode): WebhookHealthStatus
    {
        $endpoint = $this->target->findIn(($this->listEndpoints)($account, $mode));

        if (!$endpoint) {
            return WebhookHealthStatus::MISSING();
        }

        if ('enabled' !== $endpoint->status) {
            return WebhookHealthStatus::DISABLED();
        }

        if ($this->target->missingEvents($endpoint)) {
            return WebhookHealthStatus::MISSING_EVENTS();
        }

        return WebhookHealthStatus::HEALTHY();
    }
}
