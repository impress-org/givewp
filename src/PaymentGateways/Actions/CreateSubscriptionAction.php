<?php

namespace Give\PaymentGateways\Actions;

use Give\PaymentGateways\DataTransferObjects\FormData;
use Give\PaymentGateways\DataTransferObjects\SubscriptionData;
use Give\Subscriptions\DataTransferObjects\SubscriptionArgs;
use Give\Subscriptions\Models\LegacySubscriber;
use Give\ValueObjects\DonorInfo;
use Give_Donor;

/**
 * Class CreateSubscriptionAction
 * @since 2.18.0
 */
class CreateSubscriptionAction
{
    /**
     * Processes the recurring donation form and sends sets up the subscription data for hand-off to the gateway.
     *
     * @since 2.18.0
     *
     * @param  int  $donationId
     * @param  FormData  $formData
     * @param  SubscriptionData  $subscriptionData
     *
     * @return int
     */
    public function __invoke($donationId, FormData $formData, SubscriptionData $subscriptionData)
    {
        $donor = $this->getOrCreateDonor($formData->donorInfo);

        $subscriptionArgs = $this->getSubscriptionData($formData, $subscriptionData);

        return $this->createSubscription($donationId, $donor->id, $subscriptionArgs);
    }

    /**
     * @param  FormData  $formData
     * @param  SubscriptionData  $subscriptionData
     *
     * @return SubscriptionArgs
     */
    private function getSubscriptionData(FormData $formData, SubscriptionData $subscriptionData)
    {
        $subscriptionArgs = SubscriptionArgs::fromRequest([
            'period' => $subscriptionData->period,
            'times' => !empty($subscriptionData->times) ? (int)$subscriptionData->times : 0,
            'frequency' => !empty($subscriptionData->frequency) ? (int)$subscriptionData->frequency : 1,
            'formTitle' => $formData->formTitle,
            'formId' => $formData->formId,
            'priceId' => $formData->priceId,
            'price' => $formData->price,
            'status' => 'pending'
        ]);

        apply_filters('give_recurring_subscription_pre_gateway_args', $subscriptionArgs->toArray());

        return $subscriptionArgs;
    }

    /**
     * Get or create donor
     *
     * @since 2.18.0
     *
     * @param  DonorInfo  $donorInfo
     *
     * @return Give_Donor
     */
    private function getOrCreateDonor(DonorInfo $donorInfo)
    {
        $subscriber = empty($donorInfo->wpUserId) ?
            new Give_Donor($donorInfo->email) :
            new Give_Donor($donorInfo->wpUserId, true);

        if (empty($subscriber->id)) {
            $name = sprintf(
                '%s %s',
                (!empty($donorInfo->firstName) ? trim($donorInfo->firstName) : ''),
                (!empty($donorInfo->lastName) ? trim($donorInfo->lastName) : '')
            );

            $subscriber_data = [
                'name' => trim($name),
                'email' => $donorInfo->email,
                'user_id' => $donorInfo->wpUserId,
            ];

            $subscriber->create($subscriber_data);
        }

        return $subscriber;
    }

    /**
     * Records subscription donations in the database and creates a give_payment record.
     *
     * @since 2.18.0
     *
     * @param  int  $donationId
     * @param  int  $donorId
     * @param  SubscriptionArgs  $subscriptionArgs
     *
     * @return int
     */
    private function createSubscription($donationId, $donorId, $subscriptionArgs)
    {
        // Set subscription_payment.
        give_update_meta($donationId, '_give_subscription_payment', true);

        // Now create the subscription record.
        $subscriber = new LegacySubscriber($donorId);

        $args = [
            'form_id' => $subscriptionArgs->formId,
            'parent_payment_id' => $donationId,
            'status' => $subscriptionArgs->status,
            'period' => $subscriptionArgs->periodInterval,
            'frequency' => $subscriptionArgs->frequencyIntervalCount,
            'initial_amount' => $subscriptionArgs->initialAmount,
            'recurring_amount' => $subscriptionArgs->recurringAmount,
            'recurring_fee_amount' => $subscriptionArgs->recurringFeeAmount,
            'bill_times' => $subscriptionArgs->billTimes,
            'expiration' => $subscriber->get_new_expiration(
                $subscriptionArgs->formId,
                $subscriptionArgs->priceId,
                $subscriptionArgs->frequencyIntervalCount,
                $subscriptionArgs->periodInterval
            ),
            'profile_id' => $subscriptionArgs->profileId,
            'transaction_id' => $subscriptionArgs->transactionId,
            'user_id' => $donorId,
        ];

        return $subscriber->add_subscription($args)->id;
    }
}
