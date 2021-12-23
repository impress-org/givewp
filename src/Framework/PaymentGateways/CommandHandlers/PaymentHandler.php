<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\PaymentCommand;

abstract class PaymentHandler
{
    /**
     * @unreleased
     * @return string
     */
    abstract protected function getPaymentStatus();

    /**
     * @unreleased
     *
     * @param  PaymentCommand  $paymentCommand
     * @param  int  $donationId
     * @return void
     */
    public function __invoke(PaymentCommand $paymentCommand, $donationId)
    {
        give_update_payment_status($donationId, $this->getPaymentStatus());

        if( $paymentCommand->gatewayTransactionId ) {
            give_set_payment_transaction_id($donationId, $paymentCommand->gatewayTransactionId);
        }

        foreach( $paymentCommand->paymentNotes as $paymentNote ) {
            give_insert_payment_note( $donationId, $paymentNote );
        }
    }
}
