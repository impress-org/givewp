<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;

/**
 * @since 3.6.0
 */
class SubscriptionRenewalDonationCreated
{
    /**
     * @since 3.16.0 Add log messages and a defensive approach to prevent duplicated renewals
     * @since 3.6.0
     */
    public function __invoke(
        string $gatewaySubscriptionId,
        string $gatewayTransactionId,
        string $message = ''
    ) {
        $subscription = give()->subscriptions->getByGatewaySubscriptionId($gatewaySubscriptionId);

        if ( ! $subscription) {
            PaymentGatewayLog::error(
                sprintf('The renewal was not created for the gateway transaction ID %s because no subscription with the gateway subscription %s was found.',
                    $gatewayTransactionId, $gatewaySubscriptionId),
                [
                    'Gateway Subscription ID' => $gatewaySubscriptionId,
                    'Gateway Transaction ID' => $gatewayTransactionId,
                    'Message' => $message,
                ]
            );

            return;
        }

        if ($subscription->initialDonation()->gatewayTransactionId === $gatewayTransactionId) {
            PaymentGatewayLog::error(
                sprintf('The renewal was not created for the gateway transaction ID %s because the initial donation of the subscription %s is already using the informed gateway transaction ID %s.',
                    $gatewayTransactionId, $subscription->id, $gatewaySubscriptionId),
                [
                    'Gateway Subscription ID' => $gatewaySubscriptionId,
                    'Gateway Transaction ID' => $gatewayTransactionId,
                    'Message' => $message,
                    'Subscription' => $subscription->toArray(),
                ]
            );

            return;
        }

        $donation = give()->donations->getByGatewayTransactionId($gatewayTransactionId);

        if ($donation) {
            PaymentGatewayLog::error(
                sprintf('The renewal was not created for the gateway transaction ID %s because the donation %s is already using the informed gateway transaction ID %s.',
                    $gatewayTransactionId, $donation->id, $gatewaySubscriptionId),
                [
                    'Gateway Subscription ID' => $gatewaySubscriptionId,
                    'Gateway Transaction ID' => $gatewayTransactionId,
                    'Message' => $message,
                    'Donation' => $donation->toArray(),
                ]
            );

            return;
        }

        try {
            $donation = Donation::create([
                'subscriptionId' => $subscription->id,
                'amount' => $subscription->amount,
                'status' => DonationStatus::COMPLETE(),
                'type' => DonationType::RENEWAL(),
                'donorId' => $subscription->donor->id,
                'firstName' => $subscription->donor->firstName,
                'lastName' => $subscription->donor->lastName,
                'email' => $subscription->donor->email,
                'gatewayId' => $subscription->gatewayId,
                'formId' => $subscription->donationFormId,
                'levelId' => $subscription->initialDonation()->levelId,
                'anonymous' => $subscription->initialDonation()->anonymous,
                'company' => $subscription->initialDonation()->company,
                'gatewayTransactionId' => $gatewayTransactionId,
            ]);

            if (empty($message)) {
                $message = __('Subscription Renewal Donation Created.', 'give');
            }

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => $message . ' ' . sprintf(__('%s transaction ID: %s', 'give'),
                        $donation->gateway()->getName(),
                        $donation->gatewayTransactionId
                    ),
            ]);

            PaymentGatewayLog::info(
                $message . ' ' . sprintf('Donation ID: %s', $donation->id),
                [
                    'Payment Gateway' => $donation->gateway()->getId(),
                    'Gateway Transaction Id' => $donation->gatewayTransactionId,
                    'Gateway Subscription Id' => $donation->subscription->gatewaySubscriptionId,
                    'Donation' => $donation->id,
                    'Subscription' => $donation->subscriptionId,
                ]
            );
        } catch (Exception $e) {
            PaymentGatewayLog::error(
                sprintf('Subscription Renewal Donation Failed! Error: %s',
                    $e->getCode() . ' - ' . $e->getMessage()),
                [
                    'Subscription Id' => $subscription->id,
                    'Gateway Subscription Id' => $gatewaySubscriptionId,
                    'Gateway Transaction Id' => $gatewayTransactionId,
                    'message' => $message,
                ]
            );
        }
    }
}
