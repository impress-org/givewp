<?php

namespace Give\Framework\PaymentGateways\Commands;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/***
 * @since 2.18.0
 */
class SubscriptionComplete implements GatewayCommand
{
    /**
     * The Gateway Transaction / Charge Record ID
     *
     * @var string
     */
    public $gatewayTransactionId;
    /**
     * The Gateway Subscription Record ID
     *
     * @var string
     */
    public $gatewaySubscriptionId;
    /**
     * The subscription status.
     *
     * @var SubscriptionStatus
     */
    public $subscriptionStatus;
    /**
     * The donation status.
     *
     * @var DonationStatus
     */
    public $donationStatus;

    /**
     * @unreleased Add support for donation and subscription status.
     * @since 2.18.0
     */
    public function __construct(
        string $gatewayTransactionId,
        string $gatewaySubscriptionId,
        SubscriptionStatus $subscriptionStatus = null,
        DonationStatus $donationStatus = null
    ) {
        $this->gatewayTransactionId = $gatewayTransactionId;
        $this->gatewaySubscriptionId = $gatewaySubscriptionId;
        $this->subscriptionStatus = $subscriptionStatus;
        $this->donationStatus = $donationStatus;
    }
}
