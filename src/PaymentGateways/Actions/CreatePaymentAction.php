<?php

namespace Give\PaymentGateways\Actions;

use Give\Log\Log;
use Give\PaymentGateways\DataTransferObjects\GiveInsertPaymentData;

/**
 * Class CreatePaymentAction
 * @since 2.18.0
 */
class CreatePaymentAction
{
    /**
     * @since 2.18.0
     *
     * @param  GiveInsertPaymentData  $giveInsertPaymentData
     *
     * @return bool|int
     */
    public function __invoke(GiveInsertPaymentData $giveInsertPaymentData)
    {
        // Record the pending payment
        $payment = give_insert_payment($giveInsertPaymentData->toArray());

        // If errors are present, send the user back to the donation page, so they can be corrected
        if (!$payment) {
            Log::error(esc_html__('Payment Error', 'give'), $giveInsertPaymentData->toArray());
            give_send_back_to_checkout('?payment-mode=' . $giveInsertPaymentData->paymentGateway);
        }

        return $payment;
    }
}
