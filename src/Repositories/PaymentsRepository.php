<?php

namespace Give\Repositories;

use Give_Payment;

class PaymentsRepository
{
    /**
     * Retrieves a donation for the given payment ID
     *
     * @since 2.8.0
     *
     * @param string $paymentId
     *
     * @return Give_Payment
     */
    public function getDonationByPayment($paymentId)
    {
        $payments = give_get_payments(
            [
                'meta_key' => '_give_payment_transaction_id',
                'meta_value' => $paymentId,
                'number' => 1,
            ]
        );

        return empty($payments) ? null : $payments[0];
    }
}
