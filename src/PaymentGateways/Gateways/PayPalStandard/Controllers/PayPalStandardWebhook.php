<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Controllers;

use Give\Donations\Models\Donation;
use Give\Framework\Support\ValueObjects\Money;
use Give\Log\Log;
use Give\PaymentGateways\Gateways\PayPalStandard\PayPalStandard;
use Give\PaymentGateways\Gateways\PayPalStandard\Webhooks\WebhookRegister;
use Give\PaymentGateways\Gateways\PayPalStandard\Webhooks\WebhookValidator;

/**
 * This class use to handle PayPal ipn.
 *
 * @since 2.19.0
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
     * @since 2.19.0
     * @since 2.19.3 Respond with 200 http status to ipn.
     * @since 4.16.6.1 Add IPN event-data validation before processing.
     */
    public function handle()
    {
        $eventData = file_get_contents('php://input');
        $eventData = wp_parse_args($eventData);

        if ( ! $this->webhookValidator->verifyEventSignature($eventData)) {
            exit();
        }

        $donationId = isset($eventData['custom']) ? absint($eventData['custom']) : 0;
        $txnType = $eventData['txn_type'];

        // ipn verification can be disabled in GiveWP (<=2.15.0).
        // This check will prevent anonymous requests from editing donation, if ipn verification disabled.
        if ( ! $this->verifyDonationId($donationId)) {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => 'Donation id (from IPN) does not exist.',
                    'Event Data' => $eventData,
                ]
            );
            exit();
        }

        if ( ! $this->verifyEventData($eventData, $donationId, $txnType)) {
            exit();
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
     * @since 2.19.0
     *
     * @param int   $donationId
     *
     * @param array $eventData
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
     * @since 2.19.0
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
     * @since 2.19.0
     *
     * @param string $txnType
     * @param array  $eventData
     * @param int    $donationId
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
             * @param array $eventData  Encoded data.
             * @param int   $donationId donation id.
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
             * @param array $eventData  Encoded data.
             * @param int   $donationId donation id.
             */
            do_action('give_paypal_web_accept', $eventData, $donationId);
        }
    }

    /**
     * @since 4.16.6.1
     */
    private function verifyEventData(array $eventData, int $donationId, $txnType): bool
    {
        $paymentStatus = strtolower($eventData['payment_status'] ?? '');

        if ( ! $this->verifyReceiverEmail($eventData)) {
            return false;
        }

        if (in_array($paymentStatus, ['completed', 'pending'], true)) {
            if ( ! $this->verifyPaymentAmount($eventData, $donationId)) {
                return false;
            }
        }

        if (in_array($paymentStatus, ['refunded', 'reversed'], true)) {
            if ( ! $this->verifyParentTransactionId($eventData, $donationId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @since 4.16.6.1
     */
    private function verifyReceiverEmail(array $eventData)
    {
        $sitePaypalEmail = trim((string) give_get_option('paypal_email', ''));
        if ($sitePaypalEmail === '') {
            return true;
        }

        $receiverEmail = strtolower(trim((string) ($eventData['receiver_email'] ?? '')));
        $business = strtolower(trim((string) ($eventData['business'] ?? '')));
        $siteEmail = strtolower($sitePaypalEmail);

        if ($receiverEmail === '' && $business === '') {
            return true;
        }

        if ($receiverEmail !== $siteEmail && $business !== $siteEmail) {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => sprintf(
                        'IPN receiver_email (%s) / business (%s) does not match the site PayPal email (%s).',
                        $eventData['receiver_email'] ?? '(not set)',
                        $eventData['business'] ?? '(not set)',
                        $sitePaypalEmail
                    ),
                    'Event Data' => $eventData,
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * @since 4.16.6.1
     */
    private function verifyPaymentAmount(array $eventData, $donationId)
    {
        try {
            $donation = Donation::find($donationId);

            if ( ! $donation) {
                Log::error(
                    'PayPal Standard IPN Error',
                    [
                        'Message' => sprintf(
                            'Donation #%d not found.',
                            $donationId
                        ),
                        'Event Data' => $eventData,
                    ]
                );

                return false;
            }

            $currency = strtoupper(trim((string) ($eventData['mc_currency'] ?? '')));
            $donationCurrency = strtoupper(trim($donation->amount->getCurrency()->getCode()));

            if ($currency !== $donationCurrency) {
                Log::error(
                    'PayPal Standard IPN Error',
                    [
                        'Message' => sprintf(
                            'IPN currency (%s) does not match donation #%d currency (%s).',
                            $currency,
                            $donationId,
                            $donationCurrency
                        ),
                        'Event Data' => $eventData,
                    ]
                );

                return false;
            }

            $ipnAmount = Money::fromDecimal((float)($eventData['mc_gross'] ?? 0), $currency);

            if ( ! $ipnAmount->equals($donation->intendedAmount())) {
                Log::error(
                    'PayPal Standard IPN Error',
                    [
                        'Message' => sprintf(
                            'IPN amount (%s %s) does not match donation #%d amount (%s %s).',
                            $eventData['mc_gross'] ?? '0',
                            $currency,
                            $donationId,
                            $donation->intendedAmount()->formatToDecimal(),
                            $donationCurrency
                        ),
                        'Event Data' => $eventData,
                    ]
                );

                return false;
            }
        } catch (\Exception $e) {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => 'Failed to compare IPN amount to donation amount.',
                    'Exception' => $e->getMessage(),
                    'Event Data' => $eventData,
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * @since 4.16.6.1
     */
    private function verifyParentTransactionId(array $eventData, $donationId)
    {
        $parentTxnId = trim((string) ($eventData['parent_txn_id'] ?? ''));
        if ($parentTxnId === '') {
            return true;
        }

        $donation = Donation::find($donationId);
        $storedTxnId = $donation ? trim((string) $donation->gatewayTransactionId) : '';

        if ($storedTxnId === '') {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => sprintf(
                        'IPN payment_status is %s but donation #%d has no stored transaction ID — cannot process a refund for a donation that was never completed.',
                        strtolower($eventData['payment_status'] ?? ''),
                        $donationId
                    ),
                    'Event Data' => $eventData,
                ]
            );

            return false;
        }

        if ($parentTxnId !== $storedTxnId) {
            Log::error(
                'PayPal Standard IPN Error',
                [
                    'Message' => sprintf(
                        'IPN parent_txn_id (%s) does not match donation #%d stored transaction ID (%s).',
                        $parentTxnId,
                        $donationId,
                        $storedTxnId
                    ),
                    'Event Data' => $eventData,
                ]
            );

            return false;
        }

        return true;
    }
}
