<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Webhooks\Listeners;

use Give\Helpers\Call;
use Give\PaymentGateways\Gateways\PayPalStandard\Actions\ProcessIpnDonationRefund;
use Give_Payment;

/**
 * Handle web_accept and cart transaction types.
 * read more: https://developer.paypal.com/api/nvp-soap/ipn/IPNandPDTVariables/#link-ipntransactiontypes
 *
 * @unreleased
 */
class PaymentUpdated implements EventListener
{

    /**
     * @inheritDoc
     */
    public function processEvent($eventData)
    {
        // Collect donation payment details.
        $donation = new Give_Payment($eventData->custom);
        $donationStatus = strtolower($eventData->payment_status);

        // Process refunds & reversed.
        if (in_array($donationStatus, ['refunded', 'reversed'])) {
            if ('refunded' !== $donation->status) {
                Call::invoke(ProcessIpnDonationRefund::class, $eventData, $donation->ID);
            }

            return;
        }

        // Process completed donations.
        if ('completed' === $donationStatus && 'publish' !== $donation->status) {
            $donation->add_note(
                sprintf( /* translators: %s: Paypal transaction ID */
                    __('PayPal Transaction ID: %s', 'give'),
                    $eventData->txn_id
                )
            );
            $donation->transaction_id = $eventData->txn_id;
            $donation->status = 'publish';

            $donation->save();
            return;
        }

        // Add note about pending payment.
        if ('pending' === $donationStatus && isset($eventData->pending_reason)) {
            $donation->add_note(give_paypal_get_pending_donation_note($eventData->pending_reason));
            return;
        }
    }
}
