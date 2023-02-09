<?php

namespace Give\Framework\PaymentGateways\Commands;

use Give\Donations\Models\Donation;
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
     * @param Subscription $subscription Do not save the subscription, just return it so the API can see what's dirty
     * @param Donation[]   $donations    The missing donations added to the subscription
     * @param string       $notice       Optional. Use to notify users about some limitation or specificity of the gateway
     *
     */
    public function __construct(Subscription $subscription, array $donations, string $notice = '')
    {
        $this->subscription = $subscription;
        $this->donations = $donations;
        $this->notice = $notice;
    }

}
