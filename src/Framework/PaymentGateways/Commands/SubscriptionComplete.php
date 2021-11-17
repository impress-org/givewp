<?php
namespace Give\Framework\PaymentGateways\Commands;
/***
 * @unreleased
 */
class SubscriptionComplete implements GatewayCommand {
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
     * @unreleased
     *
     * @param  string  $gatewayTransactionId
     * @param  string  $gatewaySubscriptionId
     */
    public function __construct($gatewayTransactionId, $gatewaySubscriptionId)
    {
        $this->gatewayTransactionId = $gatewayTransactionId;
        $this->gatewaySubscriptionId = $gatewaySubscriptionId;
    }
}