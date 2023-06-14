<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Exception;
use Give\Donations\Models\Donation;
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
        $details = [
            'currentStatus' => $subscriptionSynced->subscription->getOriginal('status'),
            'gatewayStatus' => $subscriptionSynced->subscription->status,
            'currentPeriod' => $subscriptionSynced->subscription->getOriginal('period'),
            'gatewayPeriod' => $subscriptionSynced->subscription->period,
            'currentCreatedDate' => $subscriptionSynced->subscription->getOriginal('createdAt'),
            'gatewayCreatedDate' => $subscriptionSynced->subscription->createdAt,
        ];
        $subscriptionSynced->subscription->save();

        $transactions = [];
        foreach ($subscriptionSynced->donations as $donation) {
            if ($donation instanceof Donation) {
                $transactions[] = $donation->getAttributes();
            } else {
                $transactions = $donation;
            }
        }

        return response()->json([
            'details' => $details,
            'transactions' => $transactions,
            'notice' => $subscriptionSynced->notice,
        ]);
    }
}
