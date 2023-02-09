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
        $subscriptionAttributesChanged = $subscriptionSynced->subscription->getDirty();
        $subscriptionOldStatus = $subscriptionSynced->subscription->getOriginal('status');
        $subscriptionNewStatus = $subscriptionSynced->subscription->status->getValue();

        $subscriptionSynced->subscription->save();

        return response()->json([
            'subscription' => [
                'attributes' => $subscriptionSynced->subscription->getAttributes(),
                'attributes-changes' => $subscriptionAttributesChanged,
                'old-status' => $subscriptionOldStatus,
                'new-status' => $subscriptionNewStatus,
            ],
            'donations' => $subscriptionSynced->donations,
            'notice' => $subscriptionSynced->notice,
        ]);
    }
}
