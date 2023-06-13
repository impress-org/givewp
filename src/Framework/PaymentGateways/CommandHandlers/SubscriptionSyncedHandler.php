<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Exception;
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
     *
     * @throws Exception
     */
    public function __invoke(SubscriptionSynced $subscriptionSynced): JsonResponse
    {
        $response = response()->json([
            'details' => [
                'currentStatus' => $subscriptionSynced->subscription->getOriginal('status'),
                'gatewayStatus' => $subscriptionSynced->subscription->status,
                'currentPeriod' => $subscriptionSynced->subscription->getOriginal('period'),
                'gatewayPeriod' => $subscriptionSynced->subscription->period,
                'currentCreatedDate' => $subscriptionSynced->subscription->getOriginal('createdAt'),
                'gatewayCreatedDate' => $subscriptionSynced->subscription->createdAt,
            ],
            'transactions' => $subscriptionSynced->donations,
            'notice' => $subscriptionSynced->notice,
        ]);

        $subscriptionSynced->subscription->save();

        return $response;
    }
}
