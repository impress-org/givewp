<?php

namespace Give\Framework\PaymentGateways\Commands;

use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
class SubscriptionSynced implements GatewayCommand
{
    /**
     * @var Subscription
     */
    public $subscription;

    /**
     * @var array
     */
    public $donations;

    /**
     * @var string
     */
    public $notice;

    /**
     * @unreleased
     */
    public function __construct(Subscription $subscription, array $donations, string $notice = '')
    {
        $this->subscription = $subscription;
        $this->donations = $donations;
        $this->notice = $notice;
    }

}
