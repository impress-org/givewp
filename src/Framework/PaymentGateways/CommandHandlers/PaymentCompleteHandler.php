<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\PaymentComplete;

class PaymentCompleteHandler  {
    /**
     * @unreleased 
     *
     * @param  PaymentComplete  $paymentComplete
     * @param int $paymentId
     * @return void
     */
    public function __invoke(PaymentComplete $paymentComplete, $paymentId)
    {
        give_update_payment_status($paymentId);
        give_set_payment_transaction_id($paymentId, $paymentComplete->gatewayTransactionId);
    }
}