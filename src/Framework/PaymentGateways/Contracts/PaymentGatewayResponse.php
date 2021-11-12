<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\Http\Response\Traits\Responseable;

/**
 * @unreleased
 */
abstract class PaymentGatewayResponse implements PaymentGatewayResponseInterface
{
    use Responseable;

    /**
     * @param  int  $paymentId
     * @param  string  $transactionId
     */
    public function updatePaymentMeta($paymentId, $transactionId)
    {
        give_update_payment_status($paymentId);
        give_set_payment_transaction_id($paymentId, $transactionId);
    }
}