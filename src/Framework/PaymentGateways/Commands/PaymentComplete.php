<?php

namespace Give\Framework\PaymentGateways\Commands;

/***
 * @unreleased
 */
class PaymentComplete implements GatewayCommand {
    /**
     * The Gateway Transaction / Charge Record ID
     *
     * @var string
     */
    public $gatewayTransactionId;

    /**
     * @unreleased
     *
     * @param  string  $gatewayTransactionId
     */
    public function __construct($gatewayTransactionId)
    {
        $this->gatewayTransactionId = $gatewayTransactionId;
    }
}