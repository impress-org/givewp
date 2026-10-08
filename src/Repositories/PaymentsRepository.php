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
                'meta_key' => '_give_payment_transaction_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Finds a donation by its transaction ID, which is stored only as meta.
                'meta_value' => $paymentId, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Finds a donation by its transaction ID, which is stored only as meta.
                'number' => 1,
            ]
        );

        return empty($payments) ? null : $payments[0];
    }
}
