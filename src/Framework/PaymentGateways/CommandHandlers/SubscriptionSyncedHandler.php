<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\PaymentGateways\Commands\SubscriptionSynced;

use function Give\Framework\Http\Response\response;

/**
 * @unreleased
 */
class SubscriptionSyncedHandler
{
    /**
     * @unreleased
     */
    public function __invoke(SubscriptionSynced $subscriptionSynced): JsonResponse
    {
        return response()->json([
            'subscription' => $subscriptionSynced->subscription->getDirty(),
            'donations' => $subscriptionSynced->donations,
            'notice' => $subscriptionSynced->notice,
        ]);
    }
}
