<?php

namespace Give\PaymentGateways\PayPalStandard\Actions;

use Give\ValueObjects\Money;
use Give_Payment;

/**
 * @unreleased
 */
class ProcessIpnDonationRefund
{
    /**
     * @unreleased
     *
     * @param array $ipnEventData
     * @param Give_Payment $donation
     *
     * @return void
     */
    public function __invoke(array $ipnEventData, Give_Payment $donation)
    {
        if ($this->isPartialRefund($ipnEventData, $donation)) {
            $donation->add_note(
                sprintf( /* translators: %s: Paypal parent transaction ID */
                    __('Partial PayPal refund processed: %s', 'give'),
                    $ipnEventData['parent_txn_id']
                )
            );
        } else {
            $donation->add_note(
                sprintf( /* translators: 1: Paypal parent transaction ID 2. Paypal reason code */
                    __('PayPal Payment #%1$s Refunded for reason: %2$s', 'give'),
                    $ipnEventData['parent_txn_id'],
                    $ipnEventData['reason_code']
                )
            );

            $donation->add_note(
                sprintf( /* translators: %s: Paypal transaction ID */
                    __('PayPal Refund Transaction ID: %s', 'give'),
                    $ipnEventData['txn_id']
                )
            );

            $donation->update_status('refunded');
        }
    }

    /**
     * @unreleased
     *
     * @param array $ipnEventData
     * @param Give_Payment $donation
     *
     * @return bool
     */
    private function isPartialRefund($ipnEventData, Give_Payment $donation)
    {
        $donationAmount = Money::of($donation->total, $donation->currency);
        $refundedAmountOnPayPal = Money::of($ipnEventData['payment_gross'], $donation->currency);

        return $refundedAmountOnPayPal->getMinorAmount() < $donationAmount->getMinorAmount();
    }
}
