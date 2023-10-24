<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\PaymentGateways\Commands\SubscriptionSynced;

use function Give\Framework\Http\Response\response;

/**
 * @since 2.33.0
 */
class SubscriptionSyncedHandler
{
    /**
     * @since 2.33.0
     *
     * @throws Exception
     */
    public function __invoke(SubscriptionSynced $subscriptionSynced): JsonResponse
    {
        $dateTimeFormat = get_option('date_format') . ' ' . get_option('time_format');

        $details = [
            'currentStatus' => $subscriptionSynced->subscription->getOriginal('status'),
            'gatewayStatus' => $subscriptionSynced->subscription->status,
            'currentPeriod' => $subscriptionSynced->subscription->getOriginal('period'),
            'gatewayPeriod' => $subscriptionSynced->subscription->period,
            'currentCreatedAt' => date($dateTimeFormat,
                $subscriptionSynced->subscription->getOriginal('createdAt')->getTimestamp()),
            'gatewayCreatedAt' => date($dateTimeFormat, $subscriptionSynced->subscription->createdAt->getTimestamp()),
        ];
        $subscriptionSynced->subscription->save();

        $missingTransactions = [];
        foreach ($subscriptionSynced->missingDonations as $missingDonation) {
            $missingTransactions[] = $this->getTransactionData($missingDonation);
        }

        $presentTransactions = [];
        foreach ($subscriptionSynced->presentDonations as $presentDonation) {
            $presentTransactions[] = $this->getTransactionData($presentDonation);
        }

        return response()->json([
            'details' => $details,
            'missingTransactions' => $missingTransactions,
            'presentTransactions' => $presentTransactions,
            'notice' => $subscriptionSynced->notice,
        ]);
    }

    /**
     * @since 2.33.0
     */
    private function getTransactionData(Donation $donation): array
    {
        $dateTimeFormat = get_option('date_format') . ' ' . get_option('time_format');

        return [
            'id' => $donation->id,
            'gatewayTransactionId' => $donation->gatewayTransactionId,
            'amount' => $donation->amount->getCurrency()->getCode() . ' ' . $donation->amount->formatToDecimal(),
            'createdAt' => date($dateTimeFormat, $donation->createdAt->getTimestamp()),
            'status' => $donation->status->getValue(),
            'type' => $donation->type->getValue(),
        ];
    }
}
