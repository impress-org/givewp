<?php

namespace Give\PaymentGateways\PayPalStandard\Controllers;

use Give\Helpers\Call;
use Give\PaymentGateways\PayPalStandard\Actions\ProcessIpnDonationRefund;
use Give\PaymentGateways\PayPalStandard\Webhooks\WebhookValidator;
use Give_Payment;

/**
 * This class use to handle PayPal ipn.
 *
 * @unreleased
 */
class PayPalStandardWebhook
{

    /**
     * @var WebhookValidator
     */
    private $webhookValidator;

    public function __construct(WebhookValidator $webhookValidator)
    {
        $this->webhookValidator = $webhookValidator;
    }

    /**
     * Handle PayPal ipn
     *
     * @unreleased
     */
    public function handle()
    {
        $eventData = file_get_contents('php://input');
        $eventData = wp_parse_args($eventData);

        if ( ! $this->webhookValidator->verifyEventSignature($eventData)) {
            wp_die('Forbidden', 404);
        }

        $donationId = isset($eventData['custom']) ? absint($eventData['custom']) : 0;
        $txnType = $eventData['txn_type'];

        $this->recordIpn($eventData, $donationId);
        $this->recordIpnInDonation($donationId);

        if (has_action('give_paypal_' . $txnType)) {
            /**
             * Fires while processing PayPal IPN $txnType.
             *
             * Allow PayPal IPN types to be processed separately.
             *
             * @since 1.0
             *
             * @param int $donationId donation id.
             *
             * @param array $eventData Encoded data.
             */
            do_action("give_paypal_{$txnType}", $eventData, $donationId);
        } else {
            /**
             * Fires while process PayPal IPN.
             *
             * Fallback to web accept just in case the txn_type isn't present.
             *
             * @since 1.0
             *
             * @param int $donationId donation id.
             *
             * @param array $eventData Encoded data.
             */
            do_action('give_paypal_web_accept', $eventData, $donationId);
        }
        exit;
    }

    /**
     * @param array $eventData
     * @param int $donationId
     *
     * @unreleased
     */
    private function recordIpn(array $eventData, $donationId)
    {
        update_option(
            'give_last_paypal_ipn_received',
            [
                'auth_status' => 'VERIFIED',
                'transaction_id' => isset($eventData['txn_id']) ? $eventData['txn_id'] : 'N/A',
                'payment_id' => $donationId,
            ],
            false
        );
    }

    /**
     * Handle web_accept & cart txt_type PayPal Standard ipn.
     *
     * @unreleased
     *
     * @param array $eventData
     * @param int $donationId
     */
    public function handleIpnForOneTimeDonation(array $eventData, $donationId)
    {
        // Only allow through these transaction types.
        if ( ! in_array($eventData['txn_type'], ['web_accept', 'cart'])) {
            return;
        }

        // Collect donation payment details.
        $donation = new Give_Payment($donationId);
        $donationStatus = strtolower($eventData['payment_status']);

        // Process refunds & reversed.
        if (in_array($donationStatus, ['refunded', 'reversed'])) {
            Call::invoke(ProcessIpnDonationRefund::class, $eventData, $donationId);

            return;
        }

        // Only complete payments once.
        if ('publish' === get_post_status($donationId)) {
            return;
        }

        // Process completed donations.
        if ('completed' === $donationStatus) {
            give_insert_payment_note(
                $donation->ID,
                sprintf( /* translators: %s: Paypal transaction ID */
                    __('PayPal Transaction ID: %s', 'give'),
                    $eventData['txn_id']
                )
            );
            give_set_payment_transaction_id($donation->ID, $eventData['txn_id']);
            give_update_payment_status($donation->ID, 'publish');
        } elseif ('pending' === $donationStatus) {
            // Look for possible pending reasons, such as an eCheck.
            if (isset($eventData['pending_reason'])) {
                $note = give_paypal_get_pending_donation_note($eventData['pending_reason']);
                give_insert_payment_note($donation->ID, $note);
            }
        }
    }

    /**
     * @unreleased
     *
     * @param int $donationId
     */
    private function recordIpnInDonation($donationId)
    {
        $currentTimestamp = current_time('timestamp');

        give_insert_payment_note(
            $donationId,
            sprintf(
                __('IPN received on %1$s at %2$s', 'give'),
                date_i18n('m/d/Y', $currentTimestamp),
                date_i18n('H:i', $currentTimestamp)
            )
        );

        give_update_meta($donationId, 'give_last_paypal_ipn_received', $currentTimestamp);
    }
}
