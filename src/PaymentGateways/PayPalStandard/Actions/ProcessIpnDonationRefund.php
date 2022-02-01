<?php

namespace Give\PaymentGateways\PayPalStandard\Actions;

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

        $payment_amount = give_donation_amount($donationId);
        $refund_amount = $ipnEventData['payment_gross'] * -1;

        if (number_format((float)$refund_amount, 2) < number_format((float)$payment_amount, 2)) {
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
