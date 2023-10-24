<?php

namespace Give\Framework\PaymentGateways\Commands;

use Give\Donations\Models\Donation;
use Give\Subscriptions\Models\Subscription;

/**
 * @since 2.33.0
 */
class SubscriptionSynced implements GatewayCommand
{
    /**
     * @since 2.33.0
     *
     * @var Subscription
     */
    public $subscription;

    /**
     * @since 2.33.0
     *
     * @var array
     */
    public $missingDonations;

    /**
     * @since 2.33.0
     *
     * @var array
     */
    public $presentDonations;

    /**
     * @since 2.33.0
     *
     * @var string
     */
    public $notice;

    /**
     * @since 2.33.0
     *
     * @param Subscription $subscription     Do not save the subscription, just return it so the API can see what's dirty
     * @param Donation[]   $missingDonations The missing donations added to the subscription
     * @param Donation[]   $presentDonations Optional. The already present donations of the subscription
     * @param string       $notice           Optional. Use to notify users about some limitation or specificity of the gateway
     */
    public function __construct(
        Subscription $subscription,
        array $missingDonations,
        array $presentDonations = [],
        string $notice = ''
    ) {
        $this->subscription = $subscription;
        $this->missingDonations = $missingDonations;
        $this->presentDonations = $presentDonations;
        $this->notice = $notice;
    }
}
