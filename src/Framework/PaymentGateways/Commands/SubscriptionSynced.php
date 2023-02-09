<?php

namespace Give\Framework\PaymentGateways\Commands;

use Give\Subscriptions\Models\Subscription;

class SubscriptionSynced implements GatewayCommand
{
    public function __construct(Subscription $subscription, array $donations, string $notice = '')
    {
        //$this->gatewayTransactionId = $gatewayTransactionId;
        //$this->gatewaySubscriptionId = $gatewaySubscriptionId;
    }

}
