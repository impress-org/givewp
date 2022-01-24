<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\PaymentCommand;

abstract class PaymentHandler
{
    /**
     * @var PaymentCommand
     */
    protected $paymentCommand;

    /**
     * @since 2.18.0
     * @return string
     */
    abstract protected function getPaymentStatus();

    /**
     * @param PaymentCommand $paymentCommand
     */
    public function __construct(PaymentCommand $paymentCommand)
    {
        $this->paymentCommand = $paymentCommand;
    }

    /**
     * @param PaymentCommand $paymentCommand
     * @return static
     */
    public static function make(PaymentCommand $paymentCommand)
    {
        return new static($paymentCommand);
    }

    /**
     * @since 2.18.0
     *
     * @param  int  $donationId
     * @return void
     */
    public function handle($donationId)
    {
        give_update_payment_status($donationId, $this->getPaymentStatus());

        if( $this->paymentCommand->gatewayTransactionId ) {
            give_set_payment_transaction_id($donationId, $this->paymentCommand->gatewayTransactionId);
        }

        foreach( $this->paymentCommand->paymentNotes as $paymentNote ) {
            give_insert_payment_note( $donationId, $paymentNote );
        }
    }
}
