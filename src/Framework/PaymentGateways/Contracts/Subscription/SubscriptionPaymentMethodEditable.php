<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Subscriptions\Models\Subscription;

/**
 * @since 2.20.0
 */
interface SubscriptionPaymentMethodEditable
{
    /**
     * Update subscription payment method.
     *
     * Note: use "givewp_edit_{$subscription->gatewayId}_gateway_subscription_payment_method" filter to provide payment method data to function.
     *       This filter will help to add additional arguments to this function which should be optional otherwise you will get PHP fatal error.
     *
     * @since 2.20.0
     */
    public function updateSubscriptionPaymentMethod(Subscription $subscription);
}
