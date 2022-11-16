<?php

namespace Give\Framework\PaymentGateways\Commands;

/**
 * @unreleased
 */
class SubscriptionProcessing implements GatewayCommand
{
    /**
     * The Gateway Transaction / Charge Record ID
     *
     * @var string|null
     */
    public $gatewayTransactionId;
    /**
     * The Gateway Subscription Record ID
     *
     * @var string
     */
    public $gatewaySubscriptionId;

    /**
     * @unreleased
     */
    public function __construct(
        string $gatewaySubscriptionId,
        string $gatewayTransactionId = null
    ) {
        $this->gatewayTransactionId = $gatewayTransactionId;
        $this->gatewaySubscriptionId = $gatewaySubscriptionId;
    }
}
