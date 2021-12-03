<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\PaymentComplete;

class PaymentCompleteHandler  {
    /**
     * @unreleased
     *
     * @param  PaymentComplete  $paymentComplete
     * @param  int  $donationId
     * @return void
     */
    public function __invoke(PaymentComplete $paymentComplete, $donationId)
    {
        give_update_payment_status($donationId);
        give_set_payment_transaction_id($donationId, $paymentComplete->gatewayTransactionId);
    }
}