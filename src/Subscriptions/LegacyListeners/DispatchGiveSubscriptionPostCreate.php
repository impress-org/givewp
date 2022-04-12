<?php

namespace Give\Subscriptions\LegacyListeners;

use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Hooks;
use Give\Subscriptions\Models\Subscription;

class DispatchGiveSubscriptionPostCreate
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
            'recurring_fee_amount' => $subscription->feeAmount,
            'bill_times' => $subscription->installments,
            'parent_payment_id' => give()->subscriptions->getInitialDonationId($subscription->id),
            'form_id' => $subscription->donationFormId,
            'created' => Temporal::getFormattedDateTime($subscription->createdAt),
            'expiration' => $subscription->expiration(),
            'status' => $subscription->status->getValue(),
            'profile_id' => $subscription->gatewaySubscriptionId,
        ];

        Hooks::doAction('give_subscription_post_create', $subscription->id, $args);
    }
}
