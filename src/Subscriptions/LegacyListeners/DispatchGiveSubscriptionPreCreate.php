<?php

namespace Give\Subscriptions\LegacyListeners;

use Give\Helpers\Hooks;
use Give\Subscriptions\Models\Subscription;

class DispatchGiveSubscriptionPreCreate
{
    /**
     * @since 2.19.6
     *
     * @param  Subscription  $subscription
     * @return void
     */
    public function __invoke(Subscription $subscription)
    {
        $args = [
            'customer_id' => $subscription->donorId,
            'period' => $subscription->period->getValue(),
            'frequency' => $subscription->frequency,
            'initial_amount' => $subscription->amount,
            'recurring_amount' => $subscription->amount,
            'recurring_fee_amount' => $subscription->feeAmountRecovered,
            'bill_times' => $subscription->installments,
            'parent_payment_id' => 0,
            'form_id' => $subscription->donationFormId,
            'created' => null,
            'expiration' => $subscription->expiration(),
            'status' => $subscription->status->getValue(),
            'profile_id' => $subscription->gatewaySubscriptionId,
        ];

        Hooks::doAction('give_subscription_pre_create', $args);
    }
}
