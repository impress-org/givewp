<?php

namespace Give\PaymentGateways\PayPalStandard\Gateways\Controllers;

use Give\Helpers\Call;
use Give\PaymentGateways\PayPalStandard\Gateways\Actions\ProcessIpnDonationRefund;
use Give\PaymentGateways\PayPalStandard\Gateways\PayPalStandard;
use Give\PaymentGateways\PayPalStandard\Gateways\Webhooks\WebhookRegister;
use Give\PaymentGateways\PayPalStandard\Gateways\Webhooks\WebhookValidator;
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

        // ipn verification can be disabled in GiveWP (<=2.15.0).
        // This check will prevent anonymous requests from editing donation, if ipn verification disabled.
        if ( ! $this->verifyDonationId($donationId)) {
            wp_die('Forbidden', 404);
        }

        $this->recordIpn($eventData, $donationId);
        $this->recordIpnInDonation($donationId);

        /* @var WebhookRegister $webhookRegisterer */
        $webhookRegisterer = give(WebhookRegister::class);
        if ($webhookRegisterer->hasEventRegistered($txnType)) {
            $webhookRegisterer->getEventHandler($txnType)->processEvent((object)$eventData);
        }

        $this->supportLegacyActions($txnType, $eventData, $donationId);

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

    /**
     * @param $donationId
     *
     * @return bool
     */
    private function verifyDonationId($donationId)
    {
        return $donationId && PayPalStandard::id() === give_get_payment_gateway($donationId);
    }

    /**
     * @unreleased
     *
     * @param string $txnType
     * @param array $eventData
     * @param int $donationId
     *
     * @return void
     */
    private function supportLegacyActions($txnType, array $eventData, $donationId)
    {
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
    }
}
