<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Donations\Models\Donation;
use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
interface SubscriptionPaymentRefundable
{
    /**
     * refund subscription payment.
     *
     * @unreleased
     *
     * @param Donation $donationModel
     * @param Subscription $subscriptionModel
     *
     * @return void
     */
    public function refundSubscriptionPayment(Donation $donationModel, Subscription $subscriptionModel );
}
