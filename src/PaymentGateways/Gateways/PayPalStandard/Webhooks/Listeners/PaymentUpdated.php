<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Webhooks\Listeners;

use Give\Donations\Models\Donation;
use Give\Log\Log;
use Give\PaymentGateways\Gateways\PayPalStandard\Actions\ProcessIpnDonationRefund;
use Give_Payment;

/**
 * Handle web_accept and cart transaction types.
 * read more: https://developer.paypal.com/api/nvp-soap/ipn/IPNandPDTVariables/#link-ipntransactiontypes
 *
 * @since 2.19.0
 */
class PaymentUpdated implements EventListener
{

    /**
     * @inheritDoc
     *
     * @since 4.18.0.1 Reject missing or reused transaction IDs and refund the renewal referenced by parent_txn_id.
     */
    public function processEvent($eventData)
    {
        // Collect donation payment details.
        $donation = new Give_Payment($eventData->custom);
        $donationStatus = strtolower($eventData->payment_status);

        // Process refunds & reversed for donation.
        if (in_array($donationStatus, ['refunded', 'reversed'])) {
            $refundedDonation = $this->getRefundedDonation($eventData, $donation->ID);

            if ($refundedDonation && ! $refundedDonation->status->isRefunded()) {
                (new ProcessIpnDonationRefund())($eventData, $refundedDonation->id);
            }

            return;
        }

        // Process donation only if not already completed.
        if ('completed' === $donationStatus && 'publish' !== $donation->status) {
            if ( ! $this->isTransactionIdAvailable($eventData, $donation->ID)) {
                return;
            }

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

    /**
     * A PayPal transaction can complete only one donation.
     *
     * @since 4.18.0.1
     *
     * @param object $eventData  PayPal IPN data.
     * @param int    $donationId Donation the IPN would complete.
     *
     * @return bool
     */
    private function isTransactionIdAvailable($eventData, int $donationId): bool
    {
        $transactionId = $eventData->txn_id ?? '';

        if ('' !== $transactionId) {
            $existingDonation = give()->donations->getByGatewayTransactionId($transactionId);

            if ( ! $existingDonation || $existingDonation->id === $donationId) {
                return true;
            }
        }

        Log::error(
            'PayPal Standard IPN Error',
            [
                'Message' => sprintf(
                    'IPN txn_id (%s) is missing or already recorded on another donation, so donation #%d was not completed.',
                    $eventData->txn_id ?? '(not set)',
                    $donationId
                ),
                'Event Data' => $eventData,
            ]
        );

        return false;
    }

    /**
     * Renewal refunds reference the initial donation in "custom" and the renewal in "parent_txn_id".
     *
     * @since 4.18.0.1
     *
     * @param object $eventData  PayPal IPN data.
     * @param int    $donationId Donation ID from the IPN "custom" field.
     *
     * @return Donation|null The donation being refunded, or null when the "custom" donation doesn't exist.
     */
    private function getRefundedDonation($eventData, int $donationId): ?Donation
    {
        $donation = Donation::find($donationId);
        $parentTxnId = trim((string) ($eventData->parent_txn_id ?? ''));

        if ( ! $donation || '' === $parentTxnId || $parentTxnId === $donation->gatewayTransactionId) {
            return $donation;
        }

        $renewal = give()->donations->getByGatewayTransactionId($parentTxnId);

        if ($renewal && $renewal->type->isRenewal() && $renewal->subscriptionId === $donation->subscriptionId) {
            return $renewal;
        }

        return $donation;
    }
}
