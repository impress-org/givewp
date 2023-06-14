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
     * @unreleased
     *
     * @var Subscription
     */
    public $subscription;

    /**
     * @unreleased
     *
     * @var array
     */
    public $missingDonations;

    /**
     * @unreleased
     *
     * @var array
     */
    public $presentDonations;

    /**
     * @unreleased
     *
     * @var string
     */
    public $notice;

    /**
     * @param Subscription $subscription     Do not save the subscription, just return it so the API can see what's dirty
     * @param Donation[]   $missingDonations The missing donations added to the subscription
     * @param Donation[]   $presentDonations The already present donations of the subscription
     * @param string       $notice           Optional. Use to notify users about some limitation or specificity of the gateway
     *
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
