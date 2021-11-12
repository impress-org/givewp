<?php

namespace Give\Framework\PaymentGateways\Commands;

/***
 * @unreleased
 */
class PaymentComplete implements GatewayCommand {
    /**
     * @var string
     */
    public $transactionId;

    /**
     * @unreleased
     *
     * @param string $transactionId
     */
    public function __construct($transactionId) {
        $this->transactionId = $transactionId;
    }
}