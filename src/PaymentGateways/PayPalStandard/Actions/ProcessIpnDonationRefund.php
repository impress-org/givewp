<?php

namespace Give\PaymentGateways\PayPalStandard\Actions;

use Give\ValueObjects\Money;
use Give_Payment;

/**
 * @unreleased
 */
class ProcessIpnDonationRefund
{
    public function __invoke(array $ipnEventData, $donationId)
    {
        // Only refund payments once.
        if ('refunded' === get_post_status($donationId)) {
            return;
        }

        $donation = new Give_Payment($donationId);
        $donationAmount = Money::of($donation->total, $donation->currency);
        $refundedAmountOnPayPal = Money::of($ipnEventData['payment_gross'], $donation->currency);

        if ($refundedAmountOnPayPal->getMinorAmount() < $donationAmount->getMinorAmount()) {
            give_insert_payment_note(
                $donationId,
                sprintf( /* translators: %s: Paypal parent transaction ID */
                    __('Partial PayPal refund processed: %s', 'give'),
                    $ipnEventData['parent_txn_id']
                )
            );

            return; // This is a partial refund
        }

        give_insert_payment_note(
            $donationId,
            sprintf( /* translators: 1: Paypal parent transaction ID 2. Paypal reason code */
                __('PayPal Payment #%1$s Refunded for reason: %2$s', 'give'),
                $ipnEventData['parent_txn_id'],
                $ipnEventData['reason_code']
            )
        );
        give_insert_payment_note(
            $donationId,
            sprintf( /* translators: %s: Paypal transaction ID */
                __('PayPal Refund Transaction ID: %s', 'give'),
                $ipnEventData['txn_id']
            )
        );
        give_update_payment_status($donationId, 'refunded');
    }
}
