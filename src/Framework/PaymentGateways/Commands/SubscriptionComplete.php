<?php
namespace Give\Framework\PaymentGateways\Commands;
/***
 * @unreleased
 */
class SubscriptionComplete implements GatewayCommand {
    /**
     * @var string
     */
    public $gatewayTransactionId;
    /**
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