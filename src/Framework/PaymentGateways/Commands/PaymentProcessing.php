<?php

namespace Give\Framework\PaymentGateways\Commands;

/***
 * @unreleased
 */
class PaymentProcessing implements GatewayCommand {
    /**
     * The Gateway Transaction / Charge Record ID
     *
     * @var string|null
     */
    public $gatewayTransactionId;

    /**
     * @unreleased
     *
     * @param  string|null  $gatewayTransactionId
     */
    public function __construct($gatewayTransactionId = null)
    {
        $this->gatewayTransactionId = $gatewayTransactionId;
    }
}