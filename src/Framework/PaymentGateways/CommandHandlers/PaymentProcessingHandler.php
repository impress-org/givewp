<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\PaymentProcessing;

class PaymentProcessingHandler  {
    /**
     * @unreleased
     *
     * @param  PaymentProcessing  $paymentProcessing
     * @param  int  $donationId
     * @return void
     */
    public function __invoke(PaymentProcessing $paymentProcessing, $donationId)
    {
        give_update_payment_status($donationId, 'processing' );
        
        if ($paymentProcessing->gatewayTransactionId){
            give_set_payment_transaction_id($donationId, $paymentProcessing->gatewayTransactionId);
        }
    }
}